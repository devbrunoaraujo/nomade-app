<?php
// app/models/ChurrasqueiraModel.php

require_once __DIR__ . '/../../config/Database.php';

class ChurrasqueiraModel
{
    private PDO $db;
    private const DEFAULT_USOS = 2; // fallback se não houver config no banco

    public static array $turnos = [
        'manha' => ['label' => 'Manhã',  'horario' => '08:00 – 12:00'],
        'tarde'  => ['label' => 'Tarde',  'horario' => '12:00 – 17:00'],
        'noite'  => ['label' => 'Noite',  'horario' => '17:00 – 22:00'],
    ];

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

    public function getLimiteMensal(): int
    {
        return (int) $this->getConfig('usos_churrasqueira_mes', (string) self::DEFAULT_USOS);
    }

    // ── Créditos ─────────────────────────────────────────────
    public function getUsosNomes(int $moradorId, string $data): int
    {
        $mesAno = (new DateTime($data))->format('Y-m');
        $stmt = $this->db->prepare(
            'SELECT usos_realizados FROM creditos_churrasqueira
             WHERE morador_id = :mid AND mes_ano = :mes'
        );
        $stmt->execute([':mid' => $moradorId, ':mes' => $mesAno]);
        $row = $stmt->fetch();
        return $row ? (int) $row['usos_realizados'] : 0;
    }

    public function getUsosDisponiveisMes(int $moradorId, string $data): int
    {
        $limite = $this->getLimiteMensal();
        $usados = $this->getUsosNomes($moradorId, $data);
        return max(0, $limite - $usados);
    }

    // ── Turnos ────────────────────────────────────────────────
    public function getTurnosOcupados(string $data): array
    {
        $stmt = $this->db->prepare(
            'SELECT a.turno, m.nome, m.apartamento
             FROM agendamentos_churrasqueira a
             JOIN moradores m ON m.id = a.morador_id
             WHERE a.data_agendamento = :data AND a.status = "confirmado"'
        );
        $stmt->execute([':data' => $data]);
        $rows    = $stmt->fetchAll();
        $ocupados = [];
        foreach ($rows as $r) {
            $ocupados[$r['turno']] = $r;
        }
        return $ocupados;
    }

    // ── Agendar ──────────────────────────────────────────────
    public function agendar(int $moradorId, string $data, string $turno): array
    {
        if (!array_key_exists($turno, self::$turnos)) {
            return ['sucesso' => false, 'mensagem' => 'Turno inválido.'];
        }

        if ($data < date('Y-m-d')) {
            return ['sucesso' => false, 'mensagem' => 'Não é possível agendar datas passadas.'];
        }

        $limite      = $this->getLimiteMensal();
        $disponiveis = $this->getUsosDisponiveisMes($moradorId, $data);

        if ($disponiveis <= 0) {
            return [
                'sucesso'  => false,
                'mensagem' => "Você já utilizou todos os seus agendamentos deste mês (limite: {$limite}).",
            ];
        }

        $ocupados = $this->getTurnosOcupados($data);
        if (isset($ocupados[$turno])) {
            $oc = $ocupados[$turno];
            return [
                'sucesso'  => false,
                'mensagem' => "Este turno já está reservado pelo Apto {$oc['apartamento']}.",
            ];
        }

        $stmt = $this->db->prepare(
            'SELECT id FROM agendamentos_churrasqueira
             WHERE morador_id = :mid AND data_agendamento = :data AND status = "confirmado"'
        );
        $stmt->execute([':mid' => $moradorId, ':data' => $data]);
        if ($stmt->fetch()) {
            return ['sucesso' => false, 'mensagem' => 'Você já possui um agendamento neste dia.'];
        }

        $this->db->beginTransaction();
        try {
            $this->db->prepare(
                'INSERT INTO agendamentos_churrasqueira (morador_id, data_agendamento, turno)
                 VALUES (:mid, :data, :turno)'
            )->execute([':mid' => $moradorId, ':data' => $data, ':turno' => $turno]);

            $mesAno = (new DateTime($data))->format('Y-m');
            $this->db->prepare(
                'INSERT INTO creditos_churrasqueira (morador_id, mes_ano, usos_realizados)
                 VALUES (:mid, :mes, 1)
                 ON DUPLICATE KEY UPDATE usos_realizados = usos_realizados + 1'
            )->execute([':mid' => $moradorId, ':mes' => $mesAno]);

            $this->db->commit();
            return ['sucesso' => true, 'mensagem' => 'Churrasqueira agendada com sucesso!'];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['sucesso' => false, 'mensagem' => 'Erro ao realizar agendamento.'];
        }
    }

    // ── Cancelar ─────────────────────────────────────────────
    public function cancelar(int $agendamentoId, int $moradorId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM agendamentos_churrasqueira
             WHERE id = :id AND morador_id = :mid AND status = "confirmado"'
        );
        $stmt->execute([':id' => $agendamentoId, ':mid' => $moradorId]);
        $ag = $stmt->fetch();

        if (!$ag) {
            return ['sucesso' => false, 'mensagem' => 'Agendamento não encontrado.'];
        }

        if ($ag['data_agendamento'] < date('Y-m-d')) {
            return ['sucesso' => false, 'mensagem' => 'Não é possível cancelar agendamentos passados.'];
        }

        $this->db->beginTransaction();
        try {
            $this->db->prepare(
                'UPDATE agendamentos_churrasqueira SET status = "cancelado" WHERE id = :id'
            )->execute([':id' => $agendamentoId]);

            $mesAno = (new DateTime($ag['data_agendamento']))->format('Y-m');
            $this->db->prepare(
                'UPDATE creditos_churrasqueira
                 SET usos_realizados = GREATEST(0, usos_realizados - 1)
                 WHERE morador_id = :mid AND mes_ano = :mes'
            )->execute([':mid' => $moradorId, ':mes' => $mesAno]);

            $this->db->commit();
            return ['sucesso' => true, 'mensagem' => 'Agendamento cancelado. Crédito devolvido.'];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['sucesso' => false, 'mensagem' => 'Erro ao cancelar agendamento.'];
        }
    }

    public function getMeusAgendamentos(int $moradorId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM agendamentos_churrasqueira
             WHERE morador_id = :mid AND data_agendamento >= CURDATE()
               AND status = "confirmado"
             ORDER BY data_agendamento, FIELD(turno, "manha", "tarde", "noite")'
        );
        $stmt->execute([':mid' => $moradorId]);
        return $stmt->fetchAll();
    }

    public function getHistorico(int $moradorId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM agendamentos_churrasqueira
             WHERE morador_id = :mid
             ORDER BY data_agendamento DESC
             LIMIT 20'
        );
        $stmt->execute([':mid' => $moradorId]);
        return $stmt->fetchAll();
    }
}
