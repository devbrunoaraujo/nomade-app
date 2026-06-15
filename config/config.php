<?php
// config/config.php

// ─── Banco de dados ───────────────────────────────────────────
define('DB_HOST',    getenv('DB_HOST')    ?: 'localhost');
define('DB_NAME',    getenv('DB_NAME')    ?: 'condominio_agendamento');
define('DB_USER',    getenv('DB_USER')    ?: 'root');
define('DB_PASS',    getenv('DB_PASS')    ?: '');
define('DB_CHARSET', 'utf8mb4');
define('DB_PORT',    (int)(getenv('DB_PORT') ?: 3306));

// ─── Aplicação ────────────────────────────────────────────────
define('APP_NAME', 'Nomade Agenda');
define('APP_URL',  getenv('APP_URL') ?: 'http://localhost/nomade-app/public');
define('SESSION_TIMEOUT', 3600);

// ─── Assets ───────────────────────────────────────────────────
define('ASSETS_URL', rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php'), '/\\'));

// ─── Timezone ─────────────────────────────────────────────────
date_default_timezone_set('America/Sao_Paulo');
