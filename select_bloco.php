<?php
namespace acquaccount;
use mysqli;
use DateTime;
use DatePeriod;
use DateInterval;
use DateTimeImmutable;
use Exception;

/**************
 * Classe select_bloco
 * -----------------
 * 
 * Processa objetos enviados do site
 * 
 */

//Chama os arquivos principais do programa
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';
require_once 'autoload.php';

class select_bloco {
    function __construct() {

    }

    function lista_select($perfil, $id_condominio) {
        $bloco = new bloco();
        return $bloco->lista_select($perfil, $id_condominio);
    }

}

session_start();
$conecta_db = new conecta_db(); //Variável GLOBAL de conexão com o banco de dados
$options_bloco = [];
$erro = [];

if (isset($_SESSION['dados_cliente']) && isset($_POST['id_condominio']))
{
    $user = new user();
    $user->pega_user_por_email($_SESSION['dados_cliente']['email']);
    $perfil = new perfil();
    $perfil->pega_perfil_por_id($user->pega_id_perfil());
    $select_bloco = new select_bloco();

    if ($perfil->admin() || $perfil->cadastrador()) {
        $options_bloco['html'] = $select_bloco->lista_select($perfil, $_POST['id_condominio']);
        $json = json_encode($options_bloco);
        die($json);
    }
}

$erro['erro'] = true;
$erro['mensagem_erro'] = "ACESSO NEGADO!";
$json = json_encode($erro);
die($json);
?>