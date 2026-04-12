<?php
// app/controllers/LavanderiaController.php

require_once __DIR__ . '/../models/LavanderiaModel.php';
require_once __DIR__ . '/../models/ChurrasqueiraModel.php';
require_once __DIR__ . '/AuthController.php';

class LavanderiaController
{
    private LavanderiaModel $model;
    private AuthController $auth;

    public function __construct()
    {
        $this->model = new LavanderiaModel();
        $this->auth  = new AuthController();
        $this->auth->requireLogin();
    }

    public function index(): void
    {
        $morador = $this->auth->getCurrentMorador();
        $agendamentos = $this->model->getMeusAgendamentos($morador['id']);
        $flash = $this->auth->getFlash();
        require __DIR__ . '/../views/laundry/index.php';
    }

    // API: Retorna slots disponíveis para uma data
    public function getSlotsApi(): void
    {
        header('Content-Type: application/json');
        $data = $_GET['data'] ?? '';

        if (!$data || !$this->validarData($data)) {
            echo json_encode(['erro' => 'Data inválida.']);
            return;
        }

        $morador = $this->auth->getCurrentMorador();

        // getSlotsDisponiveis já busca horários do banco automaticamente
        $slots     = $this->model->getSlotsDisponiveis($data);
        $horasDisp = $this->model->getHorasDisponiveisSemana($morador['id'], $data);
        $limite    = $this->model->getLimiteSemanal();

        echo json_encode([
            'slots'             => $slots,
            'horas_disponiveis' => $horasDisp,
            'limite_semanal'    => $limite,
        ]);
    }

    public function agendar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->auth->redirect('lavanderia');
            return;
        }

        header('Content-Type: application/json');
        $morador  = $this->auth->getCurrentMorador();
        $data     = $_POST['data'] ?? '';
        $hora     = $_POST['hora_inicio'] ?? '';
        $duracao  = (int) ($_POST['duracao'] ?? 0);

        if (!$data || !$hora || !$duracao) {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Dados incompletos.']);
            return;
        }

        $resultado = $this->model->agendar($morador['id'], $data, $hora, $duracao);
        echo json_encode($resultado);
    }

    public function cancelar(): void
    {
        header('Content-Type: application/json');
        $morador = $this->auth->getCurrentMorador();
        $id = (int) ($_POST['id'] ?? 0);

        if (!$id) {
            echo json_encode(['sucesso' => false, 'mensagem' => 'ID inválido.']);
            return;
        }

        $resultado = $this->model->cancelar($id, $morador['id']);
        echo json_encode($resultado);
    }

    public function historico(): void
    {
        header('Content-Type: application/json');
        $morador = $this->auth->getCurrentMorador();
        $historico = $this->model->getHistorico($morador['id']);
        echo json_encode($historico);
    }

    private function validarData(string $data): bool
    {
        $d = DateTime::createFromFormat('Y-m-d', $data);
        return $d && $d->format('Y-m-d') === $data;
    }
}
