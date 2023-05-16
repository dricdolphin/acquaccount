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
 * @version 1.0.4
 * @date 2023-05-11
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
$perfil = new perfil();
$processa_login = $acquaccount->processa_login($_SESSION, $_POST, $user);
$html_body = $processa_login['html_body'];
$html_top_container = $processa_login['html_top_container'];
$html_menu_lateral = "";
$html_dashboard = "";

$dashboard = new dashboard();
$titulo_dashboard = "";
$icone_dashboard = ""; 
$link_voltar = "";

if ($user->logado()) {
  if (!$user->data_login_valido()) {
    require_once 'logout.php';
  }
  //Atualiza o horário do Login para manter a sessão do usuário ativa por 8 horas
  $_SESSION['date_time_login'] = new DateTimeImmutable("now");
  
  $links_perfil = new links_perfil();
  $perfil->pega_perfil_por_id($user->pega_id_perfil());
  $html_top_container = "<button class=\"w3-bar-item w3-button w3-hide-large w3-hover-none w3-hover-text-light-grey\" onclick=\"w3_open();\"><i class=\"fa fa-bars\"></i>  Menu</button>";
  
  $menu_lateral = new menu_lateral();
  $html_menu_lateral = $menu_lateral->exibe_html($user, $perfil);
   
  if (!isset($_GET['acao'])) {
    unset($_SESSION['objeto']);
    unset($_SESSION['id']);
    
    if ($html_body == "") {
      if ($perfil->admin() || 
        count($perfil->pega_ids_links_autorizado()) > (count($links_perfil->pega_ids_links_publicos()) 
        + count($links_perfil->pega_ids_links_sem_dashboard()))) {
        $titulo_dashboard = "Dashboard de Gestão";
        $icone_dashboard = "fa fa-dashboard";
        $html_body = $dashboard->dashboard_admin($user, $perfil);
        $html_dashboard .= $dashboard->exibe_html($html_body, $titulo_dashboard, $icone_dashboard, $link_voltar); 
      } 

      if ($perfil->sindico()) {
        $titulo_dashboard = "Dashboard do Síndico";
        $icone_dashboard = "fa fa-dashboard";
        $html_body = $dashboard->dashboard_sindico($user, $perfil);
        $html_dashboard .= $dashboard->exibe_html($html_body, $titulo_dashboard, $icone_dashboard, $link_voltar);
      } 
      
      if ($perfil->cadastrador()) {
        $titulo_dashboard = "Dashboard do Leiturista";
        $icone_dashboard = "fa fa-dashboard";
        $html_body = $dashboard->dashboard_cadastrador($user, $perfil);
        $html_dashboard .= $dashboard->exibe_html($html_body, $titulo_dashboard, $icone_dashboard, $link_voltar);
      } 

      if (isset($_GET['dashboard']) || !($perfil->admin() || $perfil->sindico() || $perfil->cadastrador())) {
        $titulo_dashboard = "Dashboard do Usuário";
        $icone_dashboard = "fas fa-chart-column";    
        $html_body = $dashboard->dashboard_usuario($user, $perfil);
        $html_dashboard = $dashboard->exibe_html($html_body, $titulo_dashboard, $icone_dashboard, $link_voltar);
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
      
      case "contato":
        $host = $_SERVER['HTTP_HOST'];
        $protocol = $_SERVER['PROTOCOL'] = isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) ? 'https' : 'http';
        $url = "$protocol://$host/?contato=true";
        header("Location: {$url}");
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
      $titulo_dashboard = "Erro";
      $icone_dashboard = "fa fa-triangle-exclamation"; 
      $html_body = $processa_acao['html_body'];
      $html_dashboard .= $dashboard->exibe_html($html_body, $titulo_dashboard, $icone_dashboard, $link_voltar);
    } else {
      $titulo_dashboard = $processa_acao['titulo_dashboard'];
      $icone_dashboard = $processa_acao['icone_dashboard'];
      $html_body = $processa_acao['html_body'];
      $link_voltar = $processa_acao['link_voltar'];
      $html_dashboard .= $dashboard->exibe_html($html_body, $titulo_dashboard, $icone_dashboard, $link_voltar);
    }
  } 
}

if (isset($_GET['contato'])) {
  if (!$user->logado()) {
    $user = new user();
    $perfil = new perfil();
  }
  
  $html_dashboard = "";
  $processa_acao = $acquaccount->processa_contato($user, $perfil, $_GET);
  $titulo_dashboard = $processa_acao['titulo_dashboard'];
  $icone_dashboard = $processa_acao['icone_dashboard'];
  $html_body = $processa_acao['html_body'];
  $link_voltar = $processa_acao['link_voltar'];
}

if ($html_dashboard == "") {
  $html_dashboard = $dashboard->exibe_html($html_body, $titulo_dashboard, $icone_dashboard, $link_voltar);
}

//Mostra o HTML
echo $pagina_default->exibe_html($user, $perfil, $html_dashboard, $html_top_container, $html_menu_lateral);
?>