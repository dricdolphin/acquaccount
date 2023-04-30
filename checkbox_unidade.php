<?php
/**************
 * Classe checkbox_unidade
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

class checkbox_unidade {

   function __construct() {

   }

   function exibe_html ($user, $perfil, $dados) {
    $unidade = new unidade();
    $condominio = new condominio();
    $bloco = new bloco();
    $condominio->pega_condominio_por_id($dados['id_condominio']);
    $html_checkbox_unidade = "            
    <p><b>".$condominio->pega_nome()."</b></p>";
    
    $lista_checkbox_unidades = $unidade->pega_todos_ids($dados['id_condominio']);
    $desabilita_edicao = "disabled";
    if (($perfil->admin() || ($perfil->link_autorizado('user') && $perfil->autorizado_id_condominio($_POST['id_condominio']))) 
        && $dados['id_user'] != $user->pega_id()) {
        $desabilita_edicao = "";
    }
    foreach ($lista_checkbox_unidades as $chave => $valor) {
        $unidade->pega_unidade_por_id($valor['id']);
        $bloco->pega_bloco_por_id($unidade->pega_id_bloco());
        $nome_unidade = $unidade->pega_nome() . " (Bloco '" . $bloco->pega_nome() ."')";
        $html_checkbox_unidade .= "<input type=\"checkbox\" name=\"ids_unidade\" id=\"ids_unidade[]\" value=\"{$valor['id']}\" {$desabilita_edicao}> - <label for=\"ids_unidade\">{$nome_unidade}</label><br>";
    }


    return $html_checkbox_unidade;
   }

}

session_start();
$conecta_db = new conecta_db(); //Variável GLOBAL de conexão com o banco de dados
$erro = [];

if (isset($_SESSION['dados_cliente']) && isset($_POST['id_condominio']) && isset($_POST['id_user']))
{
    $user = new user();
    $user->pega_user_por_email($_SESSION['dados_cliente']['email']);
    $perfil = new perfil();
    $perfil->pega_perfil_por_id($user->pega_id_perfil());
    $checkbox_unidades = new  checkbox_unidade();
    $erro = [];
    if (!($perfil->admin() || $perfil->link_autorizado('user'))) {
        $erro['erro'] = "true";
        $erro['mensagem_erro'] = "ACESSO NEGADO!";
    }

    $erro['html'] = $checkbox_unidades->exibe_html($user, $perfil, $_POST);
    $json = json_encode($erro);
    die($json);
}

$erro['erro'] = "true";
$erro['mensagem_erro'] = "ACESSO NEGADO!";
$json = json_encode($erro);
die($json);
?>