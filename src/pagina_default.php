<?php
namespace acquaccount;
use mysqli;
use DateTime;
use DatePeriod;
use DateInterval;
use DateTimeImmutable;
use Exception;

/************
 * Classe pagina_default
 * ----------------------
 * Exibe o HTML padrão
 * 
 */
class pagina_default {

  function __construct() {
    include_once "src/inclui_javascript.php";
  }

  function exibe_html($html_body, $html_top_container = "", $html_menu_lateral = "") {
    $inclui_javascript = new inclui_javascript();

    $html = "<!DOCTYPE html>
    <html>
      <head>
        <meta charset=\"UTF-8\">
        <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">
        <link rel=\"stylesheet\" href=\"https://www.w3schools.com/w3css/4/w3.css\">
        <link rel=\"stylesheet\" href=\"https://fonts.googleapis.com/css?family=Raleway\">
        <link rel=\"stylesheet\" href=\"https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/css/all.min.css\"/>
        <link rel=\"stylesheet\" href=\"estilo.css\">
        <style>
        html,body,h1,h2,h3,h4,h5 {font-family: \"Raleway\", sans-serif}
        </style>
        <title>Acquaccount - Individualização de consumo de água para condomínios</title>
        {$inclui_javascript->exibe_html()}
      </head>
      <body class=\"w3-light-grey\" onload=\"onload_w3c()\">
      <!-- Top container -->
      <div class=\"w3-bar w3-top w3-black w3-large\" style=\"z-index:4\">{$html_top_container}
        <span class=\"w3-bar-item w3-right\"><div class='logo_acquaccount'>&nbsp;</div></span>
      </div>
      {$html_menu_lateral}
      <!-- !PAGE CONTENT! -->
      <div class=\"w3-main pagina_principal\">
      {$html_body}
      </div>
      </body>
      <!-- Footer -->
      <footer class=\"w3-container w3-padding-16 w3-light-grey\">
        <div class=\"w3-center\"><p>Acquaccount&copy; - {$ano} &nbsp; | &nbsp; Estilos por <a href=\"https://www.w3schools.com/w3css/default.asp\" target=\"_blank\">w3.css</a></p>
        <p><a href='#' id=\"politica_privacidade\">Política de Privacidade</a> | <a href='#' id=\"termos_de_uso\">Termos de Uso</a> | <a href='?acao=contato' id=\"contato\">Contato</a></p></div>
      </footer>
    </html>";

    return $html;
  }

  function exibe_pagina_inicial() {
    $html = "<h3><span style='color: blue; font-weight: bold;'>Acquaccount</span> - Individualização de Água em Condomínios</h3>
    <div><img src='img/torneira.png' style='float: left; width: 190px; height: auto; margin: 3px;'><p>Não desperdice dinheiro! Use um serviço confiável, online e com atendimento
    rápido! Use o sistema Acquaccount!<br><br>
    Com Acquaccount, você pode realizar as leituras de consumo de água em seu condomínio e produzir relatórios de individualização
    no mesmo dia!<br><br> 
    As leituras são confiáveis, pois a leitura e a confirmação dos dados podem ser feitas por pessoas diferentes!<br><br>
    Os valores pagos pelo condomínio para a concessionária são cotizados pelo consumo de cada unidade, tornando os custos mais justos!</p>
    </div>
    <div><img src='img/cooperar_2.png' style='height: 270px; margin: 3px;'></div>
    <div><span style='color: blue; font-weight: bold;'>Acquaccount</span> - um sistema integrado de leitura e individualização de água para condomínios<br>
    Entre em contato conosco e veja como reduzir seus custos e ainda economizar água!
    </div>";

    return $html;
  }

  function link_pagina_principal() {
    $host = $_SERVER['HTTP_HOST'];
    $protocol = $_SERVER['PROTOCOL'] = isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) ? 'https' : 'http';
    $url = "$protocol://$host/";
    header("Location: {$url}");
  }

  function link_voltar($acao='/') {
    if ($acao != '/') { $acao = "?acao={$acao}"; }
    return "<div style='float: right;'><b><i class=\"fa fa-arrow-left-long\"></i><a href='{$acao}'> Voltar</a></b></div>";
  }

}
?>