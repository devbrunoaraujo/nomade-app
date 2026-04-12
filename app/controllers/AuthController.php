<?php
// app/controllers/AuthController.php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../models/MoradorModel.php';

class AuthController
{
    private MoradorModel $moradorModel;

    public function __construct()
    {
        $this->moradorModel = new MoradorModel();
    }

    public function showLogin(): void
    {
        if ($this->isLoggedIn()) {
            $this->redirect('dashboard');
        }
        require __DIR__ . '/../views/auth/login.php';
    }

    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('login');
            return;
        }

        $apartamento = trim($_POST['apartamento'] ?? '');
        $senha = $_POST['senha'] ?? '';

        if (empty($apartamento) || empty($senha)) {
            $this->setFlash('error', 'Preencha todos os campos.');
            $this->redirect('login');
            return;
        }

        $morador = $this->moradorModel->findByApartamento($apartamento);

        if (!$morador || !$this->moradorModel->verifyPassword($senha, $morador['senha_hash'])) {
            $this->setFlash('error', 'Apartamento ou senha incorretos.');
            $this->redirect('login');
            return;
        }

        $_SESSION['morador_id']        = $morador['id'];
        $_SESSION['morador_nome']      = $morador['nome'];
        $_SESSION['morador_apartamento'] = $morador['apartamento'];
        $_SESSION['login_time']        = time();

        $this->redirect('dashboard');
    }

    public function adminLogin(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('login');
            return;
        }

        $usuario = trim($_POST['usuario'] ?? '');
        $senha = $_POST['senha'] ?? '';

        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT * FROM admins WHERE usuario = :u LIMIT 1');
        $stmt->execute([':u' => $usuario]);
        $admin = $stmt->fetch();

        if (!$admin || !password_verify($senha, $admin['senha_hash'])) {
            $this->setFlash('error', 'Credenciais inválidas.');
            $this->redirect('login');
            return;
        }

        $_SESSION['admin_id']    = $admin['id'];
        $_SESSION['admin_usuario'] = $admin['usuario'];
        $_SESSION['login_time']  = time();

        $this->redirect('admin');
    }

    public function logout(): void
    {
        session_destroy();
        $this->redirect('login');
    }

    public function isLoggedIn(): bool
    {
        if (empty($_SESSION['morador_id'])) return false;
        if (time() - ($_SESSION['login_time'] ?? 0) > SESSION_TIMEOUT) {
            session_destroy();
            return false;
        }
        $_SESSION['login_time'] = time();
        return true;
    }

    public function isAdminLoggedIn(): bool
    {
        return !empty($_SESSION['admin_id']);
    }

    public function requireLogin(): void
    {
        if (!$this->isLoggedIn()) {
            $this->redirect('login');
            exit;
        }
    }

    public function requireAdmin(): void
    {
        if (!$this->isAdminLoggedIn()) {
            $this->redirect('login');
            exit;
        }
    }

    public function getCurrentMoradorId(): ?int
    {
        return $_SESSION['morador_id'] ?? null;
    }

    public function getCurrentMorador(): array
    {
        return [
            'id'          => $_SESSION['morador_id'] ?? null,
            'nome'        => $_SESSION['morador_nome'] ?? '',
            'apartamento' => $_SESSION['morador_apartamento'] ?? '',
        ];
    }

    public function redirect(string $page): void
    {
        header("Location: index.php?page=$page");
        exit;
    }

    public function setFlash(string $type, string $msg): void
    {
        $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
    }

    public function getFlash(): ?array
    {
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        return $flash;
    }
}
