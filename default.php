<?php
namespace acquaccount;
use mysqli;
use DateTime;
use DatePeriod;
use DateInterval;
use DateTimeImmutable;
use Exception;

/*******************************
 * Acquaccount
 * ------------------------
 * 
 * Sistema de Individualização de consumo de água em condomínios
 * 
 * @version 1.0.1
 * @date 2023-05-07
 * 
 * @author Adriano Di Piero Filho <adrianodipiero@gmail.com>
 */

//Chama os arquivos principais do programa
require_once 'config.php';
require_once 'autoload.php';
require_once 'vendor/autoload.php';

$conecta_db = new conecta_db(); //Variável GLOBAL de conexão com o banco de dados
$acquaccount = new acquaccount();
$pagina_default = new pagina_default();

//Inicializa a sessão
session_start();

$user = new user();
$processa_login = $acquaccount->processa_login($_SESSION, $_POST, $user);
$html_body = $processa_login['html_body'];
$html_top_container = $processa_login['html_top_container'];
$html_menu_lateral = "";

if ($user->logado()) {
  if (!$user->data_login_valido()) {
    require_once 'logout.php';
  }
  //Atualiza o horário do Login para manter a sessão do usuário ativa por 8 horas
  $_SESSION['date_time_login'] = new DateTimeImmutable("now");
  
  $perfil = new perfil();
  $perfil->pega_perfil_por_id($user->pega_id_perfil());
  $html_top_container = "<button class=\"w3-bar-item w3-button w3-hide-large w3-hover-none w3-hover-text-light-grey\" onclick=\"w3_open();\"><i class=\"fa fa-bars\"></i>  Menu</button>";
  
  $menu_lateral = new menu_lateral();
  $html_menu_lateral = $menu_lateral->exibe_html($user, $perfil);
 
  $dashboard = new dashboard();
  $titulo_dashboard = "Erro";
  $icone_dashboard = "fa fa-triangle-exclamation"; 
  $link_voltar = "";
  
  //O "default" irá mostrar o consumo atual da unidade, caso seja um User
  //Caso seja um Admin, irá mostrar quantos usuários estão cadastrados, quantos condomínios e quantas unidades
  //Caso seja um Cadastrador, irá verificar exibir quantos usuários estão no Condomínio do Cadastrador, além do
  //Consumo Total (m³ e em reais) do condomínio.

  //Caso a ação seja uma específica (por exemplo, editando um Usuário, ou Cadastrando um consumo de uma unidade)
  //o "dashboard" irá mostrar os formulários devidos.

  if (!isset($_GET['acao'])) {
    unset($_SESSION['objeto']);
    unset($_SESSION['id']);
    if ($html_body == "") {
      if ($perfil->admin()) {
        $html_body = $dashboard->dashboard_admin($user, $perfil);
        $titulo_dashboard = "Dashboard do Admin";
        $icone_dashboard = "fa fa-dashboard";
        if ($perfil->sindico()) {
          $html_body .= "<div class=\"w3-clear\">&nbsp;</div>";
          $html_body .= $dashboard->dashboard_sindico($user, $perfil);
        }
      } elseif ($perfil->cadastrador()) {
        $html_body = $dashboard->dashboard_cadastrador($user, $perfil);
        $titulo_dashboard = "Dashboard do Leiturista";
        $icone_dashboard = "fa fa-dashboard";
        if ($perfil->sindico()) {
          $html_body .= "<div class=\"w3-clear\">&nbsp;</div>";
          $html_body .= $dashboard->dashboard_sindico($user, $perfil);
        }
      } elseif ($perfil->sindico()) {
        $titulo_dashboard = "";
        $icone_dashboard = "";
        if (count($perfil->pega_ids_links_autorizado()) > 0) {
          $html_body = $dashboard->dashboard_admin($user, $perfil);
          $titulo_dashboard = "Dashboard de Gestão";
          $icone_dashboard = "fa fa-dashboard";
        }
        $html_body .= "<div class=\"w3-clear\">&nbsp;</div>";
        $html_body .= $dashboard->dashboard_sindico($user, $perfil);
      } else {
        $html_body = $dashboard->dashboard_usuario($user, $perfil);;
        $titulo_dashboard = "Dashboard do Usuário";
        $icone_dashboard = "fa fa-dashboard";        
      }  
    }
  
  
  } elseif (isset($_GET['acao'])) {
    $_SESSION['objeto'] = $_GET['acao'];
    if (isset($_GET['id'])) {
      $_SESSION['id'] = $_GET['id'];
    } else {
      unset($_SESSION['id']);
    }
    $html_body = "";
    
    
    switch($_GET['acao']) {
      case "user":
        $processa_acao = $acquaccount->processa_user($user, $perfil, $_GET);
      break;
        
      case "consumo_unidade":
        $consumo_unidade = new consumo_unidade();
        $processa_acao = $acquaccount->processa_consumos($user, $perfil, $_GET, $consumo_unidade);
      break;        

      case "consumo_condominio":
        $consumo_condominio = new consumo_condominio();
        $processa_acao = $acquaccount->processa_consumos($user, $perfil, $_GET, $consumo_condominio);
      break;

      case "relatorios":
        $relatorios = new relatorios();
        $processa_acao = $acquaccount->processa_relatorios($user, $perfil, $_GET, $relatorios);
      break; 

      default:
        $processa_acao = $acquaccount->processa_acao($user, $perfil, $_GET);
        if (array_key_exists('erro', $processa_acao)) {
          $html_body = $processa_acao['html_body'];
          break;
        }
      break;        
    }  
    
    if (array_key_exists('erro', $processa_acao)) {
      $html_body = $processa_acao['html_body'];
    } else {
      $titulo_dashboard = $processa_acao['titulo_dashboard'];
      $icone_dashboard = $processa_acao['icone_dashboard'];
      $html_body = $processa_acao['html_body'];
      $link_voltar = $processa_acao['link_voltar'];
    }
  } 
  
  $html_body = $dashboard->exibe_html($html_body, $titulo_dashboard, $icone_dashboard, $link_voltar); 
}

//Mostra o HTML
echo $pagina_default->exibe_html($html_body, $html_top_container, $html_menu_lateral);
?>