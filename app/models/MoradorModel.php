<?php
// app/models/MoradorModel.php

require_once __DIR__ . '/../../config/Database.php';

class MoradorModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findByApartamento(string $apartamento): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM moradores WHERE apartamento = :apt AND ativo = 1 LIMIT 1'
        );
        $stmt->execute([':apt' => $apartamento]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM moradores WHERE id = :id AND ativo = 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getAll(): array
    {
        $stmt = $this->db->query('SELECT id, nome, apartamento, ativo, criado_em FROM moradores ORDER BY apartamento');
        return $stmt->fetchAll();
    }

    public function create(string $nome, string $apartamento, string $senha): bool
    {
        $hash = password_hash($senha, PASSWORD_BCRYPT, ['cost' => 10]);
        $stmt = $this->db->prepare(
            'INSERT INTO moradores (nome, apartamento, senha_hash) VALUES (:nome, :apt, :hash)'
        );
        return $stmt->execute([':nome' => $nome, ':apt' => $apartamento, ':hash' => $hash]);
    }

    public function update(int $id, string $nome, string $apartamento, ?string $senha = null): bool
    {
        if ($senha) {
            $hash = password_hash($senha, PASSWORD_BCRYPT, ['cost' => 10]);
            $stmt = $this->db->prepare(
                'UPDATE moradores SET nome = :nome, apartamento = :apt, senha_hash = :hash WHERE id = :id'
            );
            return $stmt->execute([':nome' => $nome, ':apt' => $apartamento, ':hash' => $hash, ':id' => $id]);
        }

        $stmt = $this->db->prepare(
            'UPDATE moradores SET nome = :nome, apartamento = :apt WHERE id = :id'
        );
        return $stmt->execute([':nome' => $nome, ':apt' => $apartamento, ':id' => $id]);
    }

    public function toggleAtivo(int $id): bool
    {
        $stmt = $this->db->prepare('UPDATE moradores SET ativo = NOT ativo WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }

    public function verifyPassword(string $senha, string $hash): bool
    {
        return password_verify($senha, $hash);
    }

    public function delete(int $id): array
    {
        // Verifica se tem agendamentos futuros confirmados
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM agendamentos_lavanderia
             WHERE morador_id = :id AND data_agendamento >= CURDATE() AND status = "confirmado"'
        );
        $stmt->execute([':id' => $id]);
        $lavFuturos = (int) $stmt->fetchColumn();

        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM agendamentos_churrasqueira
             WHERE morador_id = :id AND data_agendamento >= CURDATE() AND status = "confirmado"'
        );
        $stmt->execute([':id' => $id]);
        $churrFuturos = (int) $stmt->fetchColumn();

        if ($lavFuturos + $churrFuturos > 0) {
            return [
                'sucesso'  => false,
                'mensagem' => "Este morador possui {$lavFuturos} agendamento(s) de lavanderia e {$churrFuturos} de churrasqueira ainda ativos. Cancele-os antes de excluir."
            ];
        }

        $stmt = $this->db->prepare('DELETE FROM moradores WHERE id = :id');
        $ok   = $stmt->execute([':id' => $id]);

        return [
            'sucesso'  => $ok,
            'mensagem' => $ok ? 'Morador excluído com sucesso.' : 'Erro ao excluir morador.',
        ];
    }

    public function apartamentoExists(string $apartamento, ?int $excludeId = null): bool
    {
        if ($excludeId) {
            $stmt = $this->db->prepare(
                'SELECT id FROM moradores WHERE apartamento = :apt AND id != :id LIMIT 1'
            );
            $stmt->execute([':apt' => $apartamento, ':id' => $excludeId]);
        } else {
            $stmt = $this->db->prepare('SELECT id FROM moradores WHERE apartamento = :apt LIMIT 1');
            $stmt->execute([':apt' => $apartamento]);
        }
        return (bool) $stmt->fetch();
    }
}
