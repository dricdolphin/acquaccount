<?php
namespace acquaccount;
use mysqli;
use DateTime;
use DatePeriod;
use DateInterval;
use DateTimeImmutable;
use Exception;

/**************
 * Classe processa_objeto
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

class processa_objeto {

    private $resposta = [];

    function __construct($objeto, $dados) {
        global $conecta_db;
        
        $objeto_com_namespace = __NAMESPACE__ . '\\' . $objeto;
        if (class_exists($objeto_com_namespace)) {
            $objeto_carregado = new $objeto_com_namespace();
        } else {
            $this->resposta['erro'] = true;
            $this->resposta['mensagem_erro'] = "O objeto '{$objeto}' não existe!";
            return $this->resposta;
        }

        if ($dados['id'] != 0) {
            $pega_objeto_por_id = "pega_{$objeto}_por_id";
            $objeto_carregado->$pega_objeto_por_id($dados['id']);
        }
        
        if (isset($dados['objeto'])) { unset($dados['objeto']); }


        if (isset($_GET['deletar']) ) {
            if ($_GET['deletar'] == "true") { $edita_objeto = "deleta_{$objeto}"; }
        } else {
            $edita_objeto = "salva_{$objeto}";
        }
        
        $this->resposta['dados'] = $objeto_carregado->$edita_objeto($dados);
        if ($this->resposta['dados'] === true) {
            $this->resposta['erro'] = false;
            $this->resposta['mensagem_erro'] = "OK";           
            if ($dados['id'] == 0 || $dados['id'] == "") {
                $this->resposta['dados'] = [];
                $this->resposta['dados']['id'] = $conecta_db->pega_insert_id();
                $_SESSION['id'] = $this->resposta['dados']['id'];
            }
        } else {
            if (is_array($this->resposta['dados'])) {
                $this->resposta = $this->resposta['dados'];
            }
            
            $this->resposta['erro'] = true;
            if (!$this->resposta['dados'] && !isset($this->resposta['mensagem_erro'])) {
                $this->resposta['mensagem_erro'] = $conecta_db->pega_erro();
            } else {
                if (str_contains($this->resposta['dados'],"Duplicate")) {
                    $this->resposta['mensagem_erro'] = "JÁ EXISTE UM OBJETO COM ESSES VALORES!";
                } elseif (!$this->resposta['dados'] && isset($this->resposta['mensagem_erro'])) {
                    return true;
                } else {
                    $this->resposta['mensagem_erro'] = $this->resposta['dados'];
                }   
            }
        }
    }

    function pega_resposta() {
        return $this->resposta;
    }
}

session_start();
$conecta_db = new conecta_db(); //Variável GLOBAL de conexão com o banco de dados
if (isset($_POST['id']) && isset($_SESSION['objeto']) && isset($_SESSION['dados_cliente']) 
    && (isset($_SESSION['id']) || isset($_GET['deletar'])))
{
    if (isset($_GET['deletar'])) {
        $_SESSION['id'] = $_POST['id'];
    }

    $user = new user();
    $user->pega_user_por_email($_SESSION['dados_cliente']['email']);
    $perfil = new perfil();
    $perfil->pega_perfil_por_id($user->pega_id_perfil());
    
    $id_condominio = 0;
    $id_unidade = 0;
    if (isset($_POST['id_condominio'])) { $id_condominio = $_POST['id_condominio']; }
    if (isset($_POST['id_unidade'])) { 
        $id_unidade = $_POST['id_unidade'];
        $unidade = new unidade();
        $unidade->pega_unidade_por_id($id_unidade);
        $id_condominio = $unidade->pega_id_condominio();
    }
    
    if (!$perfil->autorizado($user, $_SESSION['objeto'], $id_condominio, $id_unidade)) {
        $erro['erro'] = "true";
        $erro['mensagem_erro'] = "USUÁRIO SEM AUTORIZAÇÃO PARA REALIZAR ESSA AÇÃO!";
        $json = json_encode($erro);
        die($json);
    }

    //var_dump($_POST);
    if ($_SESSION['objeto'] == $_POST['objeto'] && $_SESSION['id'] == $_POST['id']) {
        $objeto = $_POST['objeto'];
        unset($_POST['objeto']);
        
        //Processamento especial para alguns objetos
        $processa_objeto = new processa_objeto($objeto, $_POST);
        $resposta = json_encode($processa_objeto->pega_resposta());
        die($resposta);
    }
    //Se chegou até aqui, é porque deu algum erro
    $erro['erro'] = "true";
    $erro['mensagem_erro'] = "Erro! Não foi possível alterar o objeto '{$_POST['objeto']}'!";
    $erro['dados_post']['id'] = $_POST['id'];
    $erro['dados_session']['id'] = $_SESSION['id'];
    $json = json_encode($erro);
    die($json);
}

$erro['erro'] = "true";
$erro['mensagem_erro'] = "ACESSO NEGADO!";
$erro['dados_post'] = $_POST;
$erro['dados_session']['id'] = $_SESSION['id'];
$erro['dados_session']['objeto'] = $_SESSION['objeto'];
$json = json_encode($erro);
die($json);
?>