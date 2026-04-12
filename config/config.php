<?php
// config/config.php

define('DB_HOST',    'localhost');
define('DB_NAME',    'condominio_agendamento');
define('DB_USER',    'root');        // altere para seu usuário MySQL
define('DB_PASS',    '');            // altere para sua senha MySQL
define('DB_CHARSET', 'utf8mb4');
define('DB_PORT',    3306);

// Aplicação
define('APP_NAME', 'Nomade Agenda');
define('APP_URL',  'http://localhost/nomade-app/public');
define('SESSION_TIMEOUT', 3600); // 1 hora

// ─── ASSETS_URL ──────────────────────────────────────────────
// Detecta automaticamente o caminho base dos assets (CSS, JS, imagens).
// Funciona independente do nome da pasta da aplicação no servidor.
// Ex: se a pasta se chama "nomade", ASSETS_URL = "/nomade/public"
define('ASSETS_URL', rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php'), '/\\'));

// Timezone
date_default_timezone_set('America/Sao_Paulo');
