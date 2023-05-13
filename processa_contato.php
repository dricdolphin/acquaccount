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

    function envia_dados($dados_post) {
        $to      = EMAIL_ADMIN;
        $subject = "MENSAGEM DE CONTATO DE {$dados_post['given_name']} {$dados_post['family_name']}";
        $message = $dados_post['mensagem'];
        $headers = "From: {$dados_post['email']} \r\n" .
            "Reply-To: {$dados_post['email']}' \r\n" .
            "X-Mailer: PHP/" . phpversion();

        if (mail($to, $subject, $message, $headers)) {
            $erro['erro'] = false;
            $erro['mensagem_erro'] = "OK";
            $erro['dados_post'] = $dados_post;
            $json = json_encode($erro);
            die($json);   
        }

        $erro['erro'] = true;
        $erro['mensagem_erro'] = "OCORREU UM ERRO DESCONHECIDO!";
        $erro['dados_post'] = $dados_post;
        $json = json_encode($erro);
        die($json);
    }
}

require_once 'config.php';
require_once 'src/conecta_db.php';
require_once 'src/perfil.php';
require_once 'src/user.php';

session_start();
$conecta_db = new conecta_db(); //Variável GLOBAL de conexão com o banco de dados

$processa_contato = new processa_contato();
if (isset($_POST)) {
    $processa_contato->envia_dados($_POST);
}

$erro['erro'] = true;
$erro['mensagem_erro'] = "ACESSO NEGADO!";
$erro['dados_post'] = $_POST;
$erro['dados_session']['id'] = $_SESSION['id'];
$erro['dados_session']['objeto'] = $_SESSION['objeto'];
$json = json_encode($erro);
die($json);
?>