<?php
namespace acquaccount;
use mysqli;
use DateTime;
use DatePeriod;
use DateInterval;
use DateTimeImmutable;
use Exception;

/*
 * Configurações básicas do sistema
 */

//Mostra todos os erros (desabilitar na versão de Produção)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Configuração do Banco de Dados
define('DB_HOST', 'mysql');
define('DB_USERNAME', 'USERNAME');
define('DB_PASSWORD', 'PASSWORD');
define('DB_NAME', 'DB_NAME');

//Configuração do API do Google Client
define('GOOGLE_CLIENT_ID', 'GOOGLE_CLIENT_ID');
define('GOOGLE_CLIENT_SECRET', 'GOOGLE_CLIENT_SECRET');
define('GOOGLE_REDIRECT_URL', 'GOOGLE_REDIRECT_URL');

//Configurações diversas
define('EMAIL_ADMIN', 'admin@acquaccount.com.br');
define('SITE', 'https://acquaccount.com.br');
?>