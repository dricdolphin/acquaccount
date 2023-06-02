<?php
namespace acquaccount;
use mysqli;
use DateTime;
use DatePeriod;
use DateInterval;
use DateTimeImmutable;
use Exception;

/**************
 * Classe processa_lgpd
 * -----------------
 * 
 * Processa o aceite da LGPD e Cookies
 * 
 */

 //Chama os arquivos principais do programa
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';
require_once 'autoload.php';

class processa_lgpd {

    function __construct() {

    }
}

session_start();
$conecta_db = new conecta_db(); //Variável GLOBAL de conexão com o banco de dados
$user_id = 0;
if (isset($_POST['id']) && isset($_SESSION['dados_cliente']))
{
    $user = new user();
    $user->pega_user_por_email($_SESSION['dados_cliente']['email']);
    $perfil = new perfil();
    $perfil->pega_perfil_por_id($user->pega_id_perfil());
    $user_id = $user->pega_id();
    if ($_POST['id'] == $user_id) {
        $user->salva_aceite_lgpd();
        $resposta['erro'] = false;
        $resposta['mensagem_erro'] = "OK";
        $json = json_encode($resposta);
        die($json);
    }
}

$erro['erro'] = true;
$erro['mensagem_erro'] = "ACESSO NEGADO!";
$erro['dados_post'] = $_POST;
$erro['user_email'] = $_SESSION['dados_cliente']['email'];
$erro['user_id'] = $user_id;
$json = json_encode($erro);
die($json);

?>