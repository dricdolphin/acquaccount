<?php
namespace acquaccount;
use mysqli;
use DateTime;
use DatePeriod;
use DateInterval;
use DateTimeImmutable;
use Exception;

/**************
 * Classe pega_validador
 * -----------------
 * 
 * Informa o nome e o id do validador (usuário atual)
 * 
 */
//Chama os arquivos principais do programa
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';
require_once 'autoload.php';

class pega_validador {

   function __construct() {

   }

}

session_start();
$conecta_db = new conecta_db(); //Variável GLOBAL de conexão com o banco de dados
$dados = [];
$dados['erro'] = true;
$user = new user();
$user->pega_user_por_email($_SESSION['dados_cliente']['email']);
$perfil = new perfil();
$perfil->pega_perfil_por_id($user->pega_id_perfil());

if (isset($_SESSION['dados_cliente']) && isset($_POST['objeto']) && isset($_POST['id_unidade']))
{
    $unidade = new unidade();
    $unidade->pega_unidade_por_id($_POST['id_unidade']);
    if ($perfil->cadastrador() && $perfil->autorizado($user, $_POST['objeto'], 
        $unidade->pega_id_condominio(), $unidade->pega_id())) {
        $dados['erro'] = false;
    } elseif ($perfil->admin()) {
        $dados['erro'] = false;
    }  
}

if ($dados['erro'] == false) {
    $dados['nome_validador'] = $user->pega_nome_completo();
    $dados['id_validador'] = $user->pega_id();
} else {
    $dados = [];
    $dados['erro'] = true;
    $dados['mensagem_erro'] = "ACESSO NEGADO!";
}

$json = json_encode($dados);
die($json);
?>