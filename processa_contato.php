<?php
namespace acquaccount;
use mysqli;
use DateTime;
use DatePeriod;
use DateInterval;
use DateTimeImmutable;
use Exception;

/**************
 * Classe processa_contato
 * -----------------
 * 
 * Processa mensagens de contato enviados do site
 * 
 */
 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';
require_once 'autoload.php';

class processa_contato {

    function __construct() {

    }
}

require_once 'config.php';
require_once 'src/conecta_db.php';
require_once 'src/perfil.php';
require_once 'src/user.php';

session_start();
$conecta_db = new conecta_db(); //Variável GLOBAL de conexão com o banco de dados
?>