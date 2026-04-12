<?php
// app/controllers/ChurrasqueiraController.php

require_once __DIR__ . '/../models/ChurrasqueiraModel.php';
require_once __DIR__ . '/AuthController.php';

class ChurrasqueiraController
{
    private ChurrasqueiraModel $model;
    private AuthController $auth;

    public function __construct()
    {
        $this->model = new ChurrasqueiraModel();
        $this->auth  = new AuthController();
        $this->auth->requireLogin();
    }

    public function index(): void
    {
        $morador = $this->auth->getCurrentMorador();
        $agendamentos = $this->model->getMeusAgendamentos($morador['id']);
        $flash = $this->auth->getFlash();
        require __DIR__ . '/../views/bbq/index.php';
    }

    // API: retorna turnos de uma data
    public function getTurnosApi(): void
    {
        header('Content-Type: application/json');
        $data = $_GET['data'] ?? '';

        if (!$data) {
            echo json_encode(['erro' => 'Data inválida.']);
            return;
        }

        $morador  = $this->auth->getCurrentMorador();
        $ocupados = $this->model->getTurnosOcupados($data);
        $disponiveis = $this->model->getUsosDisponiveisMes($morador['id'], $data);
        $turnos = ChurrasqueiraModel::$turnos;

        $resultado = [];
        foreach ($turnos as $key => $turno) {
            $resultado[] = [
                'key'       => $key,
                'label'     => $turno['label'],
                'horario'   => $turno['horario'],
                'disponivel'=> !isset($ocupados[$key]),
                'ocupado_por'=> $ocupados[$key]['apartamento'] ?? null,
            ];
        }

        echo json_encode([
            'turnos'     => $resultado,
            'creditos'   => $disponiveis,
        ]);
    }

    public function agendar(): void
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Método inválido.']);
            return;
        }

        $morador = $this->auth->getCurrentMorador();
        $data  = $_POST['data'] ?? '';
        $turno = $_POST['turno'] ?? '';

        if (!$data || !$turno) {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Dados incompletos.']);
            return;
        }

        $resultado = $this->model->agendar($morador['id'], $data, $turno);
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
}
