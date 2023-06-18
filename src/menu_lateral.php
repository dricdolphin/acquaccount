<?php
namespace acquaccount;
use mysqli;
use DateTime;
use DatePeriod;
use DateInterval;
use DateTimeImmutable;
use Exception;

/********************
 * Classe menu_lateral
 * -------------------
 * 
 * Exibe o menu lateral com os dados do usuário
 */

class menu_lateral {
  private $link_menu_lateral = [];

  function __construct() {
    $this->link_menu_lateral = [];
  }

  function pega_links_perfil($ids_links_autorizados) {
      $links_perfil = new links_perfil();
      
      foreach ($ids_links_autorizados as $chave => $valor) {
        $nome = $links_perfil->pega_nome($valor);
        $cor = $links_perfil->pega_cor($valor);
        $icone = $links_perfil->pega_icone($valor);
        $link = $links_perfil->pega_link($valor);
        $this->link_menu_lateral[$valor] = "<a href=\"?acao={$link}\" class=\"w3-bar-item w3-button w3-padding {$cor}\" title=\"{$nome}\"><i class=\"{$icone}\"></i>  {$nome}</a>";
      }
  }
    
  function exibe_html($user, $perfil) {
        $links_perfil = new links_perfil();
        $html_links_perfil = "";
        
        $ids_links_autorizados = [];
        if ($perfil->admin()) {
          for ($index = 0; $index < count($links_perfil); $index++) {
            $ids_links_autorizados[] = $index;
          }
        } elseif ($perfil->cadastrador()) {
          for ($index = 0; $index < count($links_perfil); $index++) {
            if (!$links_perfil->pega_dashboard($index) || $perfil->link_autorizado($links_perfil->pega_link($index))) {
              $ids_links_autorizados[] = $index;
            }  
          }
        } else {
          $ids_links_autorizados = $perfil->pega_ids_links_autorizado();
        }
        
        $this->pega_links_perfil($ids_links_autorizados);
        foreach ($this->link_menu_lateral as $chave => $valor) {
          $html_links_perfil .= $valor."\n";
        }

        $links_dashboard = "";
        if ($perfil->admin() || $perfil->cadastrador() || $perfil->sindico()) {
          $links_dashboard = "<a href=\"/\" class=\"w3-bar-item w3-button\" title=\"Dashboard\"><i class=\"fa fa-dashboard\"></i></a>";
        }
        $links_dashboard .= "<a href=\"?dashboard=user\" class=\"w3-bar-item w3-button\" title=\"Dashboard do Usuário\"><i class=\"fas fa-chart-column\"></i></a>";

        $html = "
        <!-- Sidebar/menu -->
  <nav class=\"w3-sidebar w3-collapse w3-white w3-animate-left menu_lateral\" id=\"mySidebar\"><br>
  <div class=\"w3-container w3-row\">
    <div class=\"w3-col s4\">
      <img src=\"{$user->pega_imagem()}\" class=\"w3-circle w3-margin-right\" style=\"width:46px\" alt=\"Foto do perfil\">
    </div>
    <div class=\"w3-col w3-bar\">
      <span>Olá, <strong>{$user->pega_nome()}</strong></span><br>
      <div class=\"w3-small\"><strong>Perfil:</strong> {$perfil->pega_nome()}</div>
      {$links_dashboard}
      <a href=\"?acao=user&id={$user->pega_id()}\" class=\"w3-bar-item w3-button\" title=\"Perfil\"><i class=\"fa fa-user\"></i></a>
      <a href=\"logout.php\" id=\"signout_button\" class=\"w3-bar-item w3-button g_id_signout\" title=\"Logout\"><i class=\"fa fa-arrow-right-from-bracket\"></i></a>
    </div>
  </div>
  <hr>
  <div class=\"w3-bar-block\">
    <a href=\"#\" class=\"w3-bar-item w3-button w3-padding-16 w3-hide-large w3-dark-grey w3-hover-black\" onclick=\"w3_close()\" title=\"fechar menu\"><i class=\"fa fa-remove fa-fw\"></i>  Fechar Menu</a>      
    {$html_links_perfil}
  </div>
  </nav>
  <!-- Overlay effect when opening sidebar on small screens -->
  <div class=\"w3-overlay w3-hide-large w3-animate-opacity\" onclick=\"w3_close()\" style=\"cursor:pointer\" title=\"Fechar menu\" id=\"myOverlay\"></div>";
        return $html;
  }
}
?>