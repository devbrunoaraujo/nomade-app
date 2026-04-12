<?php
// app/controllers/DashboardController.php

require_once __DIR__ . '/AuthController.php';
require_once __DIR__ . '/../models/LavanderiaModel.php';
require_once __DIR__ . '/../models/ChurrasqueiraModel.php';

class DashboardController
{
    private AuthController $auth;
    private LavanderiaModel $lavModel;
    private ChurrasqueiraModel $churrModel;

    public function __construct()
    {
        $this->auth       = new AuthController();
        $this->lavModel   = new LavanderiaModel();
        $this->churrModel = new ChurrasqueiraModel();
        $this->auth->requireLogin();
    }

    public function index(): void
    {
        $morador = $this->auth->getCurrentMorador();
        $hoje    = date('Y-m-d');

        $horasDisp   = $this->lavModel->getHorasDisponiveisSemana($morador['id'], $hoje);
        $limiteLav   = $this->lavModel->getLimiteSemanal();
        $churrDisp   = $this->churrModel->getUsosDisponiveisMes($morador['id'], $hoje);
        $limiteChurr = $this->churrModel->getLimiteMensal();

        $proxLav   = $this->lavModel->getMeusAgendamentos($morador['id']);
        $proxChurr = $this->churrModel->getMeusAgendamentos($morador['id']);

        $flash = $this->auth->getFlash();

        require __DIR__ . '/../views/dashboard/index.php';
    }
}
