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

  function exibe_pagina_inicial() {
    $html = "<h3><span style='color: blue; font-weight: bold;'>Acquaccount</span> - Individualização de Água em Condomínios</h3>
    <div><img src='img/torneira.png' style='float: left; width: 190px; height: auto; margin: 3px;'>
    <p>Bem-vindo ao <span style='color: blue;'>Acquaccount</span>, sua solução completa para Individualização de Água em Condomínios.</p><br>
    <p>Entendemos que a gestão eficiente dos recursos hídricos é uma preocupação crescente em todo o mundo, e é por isso que 
    oferecemos um sistema de individualização de água preciso e confiável para condomínios.</p>
    <p>Ao utilizar o nosso sistema, você terá acesso a informações precisas e atualizadas sobre o consumo de água de cada unidade, 
    monitorando de perto o consumo de água e identificando possíveis desperdícios e vazamentos. 
    Isso pode levar a uma redução significativa do consumo de água, incentivando a economia de água e a sustentabilidade ambiental.</p>
    <p>Nosso sistema é fácil de usar e compatível com todos os modelos de hidrômetros, e nossa equipe altamente treinada está 
    pronta para fornecer todo o suporte necessário para garantir a qualidade do serviço prestado.</p>
    </div>
    <div><span style='color: blue; font-weight: bold;'>Acquaccount</span> - um sistema integrado de leitura e individualização de água para condomínios.
    Entre em <a href='?contato=true' id=\"contato\">contato</a> conosco e veja como reduzir seus custos e ainda economizar água!
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

  function exibe_html($user, $perfil, $html_body, $html_top_container = "", $html_menu_lateral = "") {
    $inclui_javascript = new inclui_javascript();
    $html_javascript = $inclui_javascript->exibe_html();
    $interval = new DateTimeImmutable("now");
    $ano = $interval->format('Y');
    
    $class_pagina_principal = "";
    if ($html_menu_lateral != "") {
      $class_pagina_principal = " pagina_principal";
    }

    if (!$user->logado()) {
      $class_pagina_principal .= " nao_logado";
    }

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
        {$html_javascript}
      </head>
      <body class=\"w3-light-grey preload div_para_imprimir\" onload=\"onload_w3c()\">
      <!-- Top container -->
      <div class=\"w3-bar w3-top w3-black w3-large div_para_imprimir\" style=\"z-index:4\">{$html_top_container}
        <span class=\"w3-bar-item w3-right div_para_imprimir\"><div class=\"logo_acquaccount div_para_imprimir\">&nbsp;</div></span>
      </div>
      {$html_menu_lateral}
      <!-- !PAGE CONTENT! -->
      <div class=\"w3-main {$class_pagina_principal} div_para_imprimir\">
      {$html_body}
      </div>
      </body>
      <!-- Footer -->
      <footer class=\"w3-container w3-padding-16 w3-light-grey\">
        <div class=\"w3-center\"><p>Acquaccount&copy; - {$ano} &nbsp; | &nbsp; Estilos por <a href=\"https://www.w3schools.com/w3css/default.asp\" target=\"_blank\">w3.css</a></p>
        <p><a href=\"#\" id=\"politica_privacidade\">Política de Privacidade</a> | <a href='#' id=\"termos_de_uso\">Termos de Uso</a> | <a href='?contato=true' id=\"contato\">Contato</a></p></div>
      </footer>
      <div id=\"div_privacidade\" class=\"w3-panel w3-pale-blue div_privacidade\">
        <a href=\"#\" id=\"fecha_alerta\" class=\"w3-button w3-large w3-display-topright\">×</a>
        <div id=\"div_privacidade_conteudo\">&nbsp;</div>
      </div>
      <div id=\"div_termos_de_uso\" class=\"w3-panel w3-pale-blue div_privacidade\">
      <a href=\"#\" id=\"fecha_alerta\" class=\"w3-button w3-large w3-display-topright\">×</a>
      <div id=\"div_termos_de_uso_conteudo\">&nbsp;</div>
    </div>
    </html>";

    return $html;
  }
}
?>