<?php
// app/models/LavanderiaModel.php

require_once __DIR__ . '/../../config/Database.php';

class LavanderiaModel
{
    private PDO $db;
    private const SLOT_MINUTOS      = 30;
    private const DEFAULT_HORAS     = 4;     // fallback se não houver config no banco
    private const DEFAULT_ABERTURA  = '07:00';
    private const DEFAULT_FECHAMENTO= '22:00';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ── Lê configuração do banco com fallback ────────────────
    private function getConfig(string $chave, string $padrao): string
    {
        try {
            $stmt = $this->db->prepare('SELECT valor FROM configuracoes WHERE chave = :k LIMIT 1');
            $stmt->execute([':k' => $chave]);
            $row = $stmt->fetch();
            return ($row && $row['valor'] !== '') ? $row['valor'] : $padrao;
        } catch (\Exception $e) {
            return $padrao;
        }
    }

    public function getLimiteSemanal(): float
    {
        return (float) $this->getConfig('horas_lavanderia_semana', (string) self::DEFAULT_HORAS);
    }

    public function getHorarioAbertura(): string
    {
        return $this->getConfig('horario_abertura', self::DEFAULT_ABERTURA);
    }

    public function getHorarioFechamento(): string
    {
        return $this->getConfig('horario_fechamento', self::DEFAULT_FECHAMENTO);
    }

    // ── Semana ───────────────────────────────────────────────
    private function getSegundaFeira(string $data): string
    {
        $dt = new DateTime($data);
        $diaSemana = (int) $dt->format('N');
        $dt->modify('-' . ($diaSemana - 1) . ' days');
        return $dt->format('Y-m-d');
    }

    public function getHorasUsadasNaSemana(int $moradorId, string $data): float
    {
        $semana = $this->getSegundaFeira($data);
        $stmt = $this->db->prepare(
            'SELECT horas_usadas FROM creditos_lavanderia
             WHERE morador_id = :mid AND semana_inicio = :semana'
        );
        $stmt->execute([':mid' => $moradorId, ':semana' => $semana]);
        $row = $stmt->fetch();
        return $row ? (float) $row['horas_usadas'] : 0.0;
    }

    public function getHorasDisponiveisSemana(int $moradorId, string $data): float
    {
        $usadas = $this->getHorasUsadasNaSemana($moradorId, $data);
        $limite = $this->getLimiteSemanal();
        return max(0, $limite - $usadas);
    }

    // ── Slots ────────────────────────────────────────────────
    public function getHorariosOcupados(string $data): array
    {
        $stmt = $this->db->prepare(
            'SELECT a.horario_inicio, a.horario_fim, m.nome, m.apartamento
             FROM agendamentos_lavanderia a
             JOIN moradores m ON m.id = a.morador_id
             WHERE a.data_agendamento = :data AND a.status = "confirmado"
             ORDER BY a.horario_inicio'
        );
        $stmt->execute([':data' => $data]);
        return $stmt->fetchAll();
    }

    public function getSlotsDisponiveis(string $data, string $abertura = '', string $fechamento = ''): array
    {
        // Se não passados, busca do banco
        if ($abertura   === '') $abertura   = $this->getHorarioAbertura();
        if ($fechamento === '') $fechamento = $this->getHorarioFechamento();

        $ocupados  = $this->getHorariosOcupados($data);
        $slots     = [];
        $inicio    = new DateTime($data . ' ' . $abertura);
        $fim       = new DateTime($data . ' ' . $fechamento);
        $intervalo = new DateInterval('PT30M');
        $agora     = new DateTime();

        while ($inicio < $fim) {
            $slotFim    = clone $inicio;
            $slotFim->add($intervalo);
            $horaInicio = $inicio->format('H:i');
            $horaFim    = $slotFim->format('H:i');
            $disponivel = true;
            $ocupadoPor = null;

            if ($data === date('Y-m-d') && $inicio <= $agora) {
                $disponivel = false;
                $ocupadoPor = 'passado';
            } else {
                foreach ($ocupados as $oc) {
                    $ocInicio = substr($oc['horario_inicio'], 0, 5);
                    $ocFim    = substr($oc['horario_fim'], 0, 5);
                    if ($horaInicio < $ocFim && $horaFim > $ocInicio) {
                        $disponivel = false;
                        $ocupadoPor = 'Apto ' . $oc['apartamento'];
                        break;
                    }
                }
            }

            $slots[] = [
                'inicio'     => $horaInicio,
                'fim'        => $horaFim,
                'disponivel' => $disponivel,
                'ocupado_por'=> $ocupadoPor,
            ];

            $inicio->add($intervalo);
        }

        return $slots;
    }

    // ── Agendar ──────────────────────────────────────────────
    public function agendar(int $moradorId, string $data, string $horaInicio, int $duracaoMinutos): array
    {
        if ($duracaoMinutos < 30 || $duracaoMinutos % 30 !== 0) {
            return ['sucesso' => false, 'mensagem' => 'Duração inválida.'];
        }

        $horasAgendadas = $duracaoMinutos / 60;
        $disponiveis    = $this->getHorasDisponiveisSemana($moradorId, $data);

        if ($horasAgendadas > $disponiveis) {
            $limite = $this->getLimiteSemanal();
            return [
                'sucesso'  => false,
                'mensagem' => "Você tem apenas {$disponiveis}h disponíveis nesta semana (limite: {$limite}h).",
            ];
        }

        $dtInicio = new DateTime($data . ' ' . $horaInicio);
        $dtFim    = clone $dtInicio;
        $dtFim->add(new DateInterval('PT' . $duracaoMinutos . 'M'));
        $horaFim  = $dtFim->format('H:i');

        $slots = $this->getSlotsDisponiveis($data);
        foreach ($slots as $slot) {
            if ($slot['inicio'] >= $horaInicio && $slot['inicio'] < $horaFim) {
                if (!$slot['disponivel']) {
                    return ['sucesso' => false, 'mensagem' => 'Horário já está ocupado.'];
                }
            }
        }

        $this->db->beginTransaction();
        try {
            $this->db->prepare(
                'INSERT INTO agendamentos_lavanderia
                 (morador_id, data_agendamento, horario_inicio, horario_fim, duracao_minutos)
                 VALUES (:mid, :data, :ini, :fim, :dur)'
            )->execute([
                ':mid'  => $moradorId,
                ':data' => $data,
                ':ini'  => $horaInicio . ':00',
                ':fim'  => $horaFim . ':00',
                ':dur'  => $duracaoMinutos,
            ]);

            $semana = $this->getSegundaFeira($data);
            $this->db->prepare(
                'INSERT INTO creditos_lavanderia (morador_id, semana_inicio, horas_usadas)
                 VALUES (:mid, :semana, :h)
                 ON DUPLICATE KEY UPDATE horas_usadas = horas_usadas + :h2'
            )->execute([
                ':mid'    => $moradorId,
                ':semana' => $semana,
                ':h'      => $horasAgendadas,
                ':h2'     => $horasAgendadas,
            ]);

            $this->db->commit();
            return ['sucesso' => true, 'mensagem' => 'Agendamento realizado com sucesso!'];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['sucesso' => false, 'mensagem' => 'Erro ao realizar agendamento.'];
        }
    }

    // ── Cancelar ─────────────────────────────────────────────
    public function cancelar(int $agendamentoId, int $moradorId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM agendamentos_lavanderia
             WHERE id = :id AND morador_id = :mid AND status = "confirmado"'
        );
        $stmt->execute([':id' => $agendamentoId, ':mid' => $moradorId]);
        $ag = $stmt->fetch();

        if (!$ag) {
            return ['sucesso' => false, 'mensagem' => 'Agendamento não encontrado.'];
        }

        $dataHora = new DateTime($ag['data_agendamento'] . ' ' . $ag['horario_inicio']);
        if ($dataHora <= new DateTime()) {
            return ['sucesso' => false, 'mensagem' => 'Não é possível cancelar agendamentos passados.'];
        }

        $this->db->beginTransaction();
        try {
            $this->db->prepare(
                'UPDATE agendamentos_lavanderia SET status = "cancelado" WHERE id = :id'
            )->execute([':id' => $agendamentoId]);

            $semana       = $this->getSegundaFeira($ag['data_agendamento']);
            $horasDevolver = $ag['duracao_minutos'] / 60;
            $this->db->prepare(
                'UPDATE creditos_lavanderia
                 SET horas_usadas = GREATEST(0, horas_usadas - :h)
                 WHERE morador_id = :mid AND semana_inicio = :semana'
            )->execute([':h' => $horasDevolver, ':mid' => $moradorId, ':semana' => $semana]);

            $this->db->commit();
            return ['sucesso' => true, 'mensagem' => 'Agendamento cancelado. Créditos devolvidos.'];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['sucesso' => false, 'mensagem' => 'Erro ao cancelar agendamento.'];
        }
    }

    public function getMeusAgendamentos(int $moradorId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM agendamentos_lavanderia
             WHERE morador_id = :mid AND data_agendamento >= CURDATE()
               AND status = "confirmado"
             ORDER BY data_agendamento, horario_inicio'
        );
        $stmt->execute([':mid' => $moradorId]);
        return $stmt->fetchAll();
    }

    public function getHistorico(int $moradorId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM agendamentos_lavanderia
             WHERE morador_id = :mid
             ORDER BY data_agendamento DESC, horario_inicio DESC
             LIMIT 30'
        );
        $stmt->execute([':mid' => $moradorId]);
        return $stmt->fetchAll();
    }
}
