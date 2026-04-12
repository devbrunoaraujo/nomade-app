<?php
// app/controllers/AdminController.php

require_once __DIR__ . '/AuthController.php';
require_once __DIR__ . '/../models/MoradorModel.php';
require_once __DIR__ . '/../models/LavanderiaModel.php';
require_once __DIR__ . '/../models/ChurrasqueiraModel.php';
require_once __DIR__ . '/../../config/Database.php';

class AdminController
{
    private AuthController $auth;
    private MoradorModel $moradorModel;
    private LavanderiaModel $lavModel;
    private ChurrasqueiraModel $churrModel;

    public function __construct()
    {
        $this->auth         = new AuthController();
        $this->moradorModel = new MoradorModel();
        $this->lavModel     = new LavanderiaModel();
        $this->churrModel   = new ChurrasqueiraModel();
        $this->auth->requireAdmin();
    }

    public function index(): void
    {
        $moradores = $this->moradorModel->getAll();
        $flash = $this->auth->getFlash();
        require __DIR__ . '/../views/admin/index.php';
    }

    public function criarMorador(): void
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Método inválido.']);
            return;
        }

        $nome       = trim($_POST['nome'] ?? '');
        $apartamento= trim($_POST['apartamento'] ?? '');
        $senha      = $_POST['senha'] ?? '';

        if (strlen($nome) < 2 || empty($apartamento) || strlen($senha) < 4) {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Dados inválidos. Senha mínima: 4 caracteres.']);
            return;
        }

        if ($this->moradorModel->apartamentoExists($apartamento)) {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Apartamento já cadastrado.']);
            return;
        }

        $ok = $this->moradorModel->create($nome, $apartamento, $senha);
        echo json_encode([
            'sucesso'  => $ok,
            'mensagem' => $ok ? 'Morador cadastrado com sucesso!' : 'Erro ao cadastrar.',
        ]);
    }

    public function editarMorador(): void
    {
        header('Content-Type: application/json');
        $id         = (int) ($_POST['id'] ?? 0);
        $nome       = trim($_POST['nome'] ?? '');
        $apartamento= trim($_POST['apartamento'] ?? '');
        $senha      = $_POST['senha'] ?? '';

        if (!$id || strlen($nome) < 2 || empty($apartamento)) {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Dados inválidos.']);
            return;
        }

        if ($this->moradorModel->apartamentoExists($apartamento, $id)) {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Apartamento já cadastrado para outro morador.']);
            return;
        }

        $ok = $this->moradorModel->update($id, $nome, $apartamento, $senha ?: null);
        echo json_encode([
            'sucesso'  => $ok,
            'mensagem' => $ok ? 'Morador atualizado!' : 'Erro ao atualizar.',
        ]);
    }

    public function toggleMorador(): void
    {
        header('Content-Type: application/json');
        $id = (int) ($_POST['id'] ?? 0);
        if (!$id) {
            echo json_encode(['sucesso' => false, 'mensagem' => 'ID inválido.']);
            return;
        }
        $ok = $this->moradorModel->toggleAtivo($id);
        echo json_encode(['sucesso' => $ok]);
    }

    public function deletarMorador(): void
    {
        header('Content-Type: application/json');
        $id = (int) ($_POST['id'] ?? 0);
        if (!$id) {
            echo json_encode(['sucesso' => false, 'mensagem' => 'ID inválido.']);
            return;
        }
        $resultado = $this->moradorModel->delete($id);
        echo json_encode($resultado);
    }

    public function getAgendamentos(): void
    {
        header('Content-Type: application/json');
        $db     = Database::getInstance();
        $filtro = $_GET['filtro'] ?? 'todos'; // futuros | historico | todos

        // ── Lavanderia ──────────────────────────────────────────────
        $sqlLav = 'SELECT a.*, m.nome, m.apartamento
                   FROM agendamentos_lavanderia a
                   JOIN moradores m ON m.id = a.morador_id';

        if ($filtro === 'futuros') {
            $sqlLav .= ' WHERE a.data_agendamento >= CURDATE() AND a.status = "confirmado"';
        } elseif ($filtro === 'historico') {
            $sqlLav .= ' WHERE a.data_agendamento < CURDATE() OR a.status IN ("cancelado","concluido")';
        }

        $sqlLav .= ' ORDER BY a.data_agendamento DESC, a.horario_inicio DESC LIMIT 200';

        $lavanderia = $db->query($sqlLav)->fetchAll();

        // ── Churrasqueira ───────────────────────────────────────────
        $sqlChurr = 'SELECT a.*, m.nome, m.apartamento
                     FROM agendamentos_churrasqueira a
                     JOIN moradores m ON m.id = a.morador_id';

        if ($filtro === 'futuros') {
            $sqlChurr .= ' WHERE a.data_agendamento >= CURDATE() AND a.status = "confirmado"';
        } elseif ($filtro === 'historico') {
            $sqlChurr .= ' WHERE a.data_agendamento < CURDATE() OR a.status IN ("cancelado","concluido")';
        }

        $sqlChurr .= ' ORDER BY a.data_agendamento DESC, FIELD(a.turno,"manha","tarde","noite") LIMIT 200';

        $churrasqueira = $db->query($sqlChurr)->fetchAll();

        echo json_encode([
            'lavanderia'    => $lavanderia,
            'churrasqueira' => $churrasqueira,
        ]);
    }

    public function logout(): void
    {
        $this->auth->logout();
    }
    public function getConfiguracoes(): void
    {
        header('Content-Type: application/json');
        $db = Database::getInstance();
        $rows = $db->query("SELECT chave, valor FROM configuracoes")->fetchAll();
        $cfg = [];
        foreach ($rows as $r) $cfg[$r['chave']] = $r['valor'];
        echo json_encode(['sucesso' => true, 'dados' => $cfg]);
    }

    public function salvarConfiguracoes(): void
    {
        header('Content-Type: application/json');
        $db = Database::getInstance();

        $campos = [
            'horas_lavanderia_semana',
            'usos_churrasqueira_mes',
            'horario_abertura',
            'horario_fechamento',
            'nome_condominio',
        ];

        try {
            foreach ($campos as $chave) {
                $valor = trim($_POST[$chave] ?? '');
                if ($valor === '') continue;
                $stmt = $db->prepare(
                    "INSERT INTO configuracoes (chave, valor) VALUES (:k, :v)
                     ON DUPLICATE KEY UPDATE valor = :v2"
                );
                $stmt->execute([':k' => $chave, ':v' => $valor, ':v2' => $valor]);
            }
            echo json_encode(['sucesso' => true, 'mensagem' => 'Configurações salvas com sucesso!']);
        } catch (Exception $e) {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao salvar configurações.']);
        }
    }

}
