<?php
namespace acquaccount;
use mysqli;
use DateTime;
use DatePeriod;
use DateInterval;
use DateTimeImmutable;
use Exception;

/**************************
 * Classe contato
 * ------------------
 * 
 * Exibe um formulário para o contato do Administrador do Sistema
 * 
 */

class contato {
    
    function __construct() {

    }

    function classe_plural() {
        return "Contatos";
     }
  
     function classe_singular() {
        return "Contato";
     }
  
     function pega_adjetivo_novo() {
        return "Novo";
     }
  
     function pega_cor() {
        return "w3-green";
     }
  
     function pega_icone() {
        return "fa fa-envelope-open";
     }
  
     function pega_ascendentes($inline = false) {
        return "";
     }
  
     function pega_todos_ids() {
        return array();
     }
  
     function exibe_html($user, $perfil) {
      $placeholder = [];
      $placeholder['given_name'] = "placeholder=\"Primeiro Nome\"";
      $placeholder['family_name'] = "placeholder=\"Sobrenome\"";
      $placeholder['email'] = "placeholder=\"email\"";

      if ($user->autenticado()) {
         $placeholder['given_name'] = "value=\"".$user->pega_nome()."\" disabled";
         $placeholder['family_name'] = "value=\"".$user->pega_sobrenome()."\" disabled";
         $placeholder['email'] = "value=\"".$user->pega_email()."\" disabled";
      }
      
      $html = "<form action=\"processa_contato.php\" method=\"post\" 
      class=\"w3-container w3-card-4 w3-light-grey w3-text-green w3-margin\" id=\"form_contato\"
      onsubmit=\"return envia_form_contato(event, this);\">
      <h2 class=\"w3-center\">Contato</h2>
      <div class=\"w3-row w3-section\">
        <div class=\"w3-col\" style=\"width:50px\"><i class=\"w3-xxlarge fa fa-user\"></i></div>
          <div class=\"w3-rest\">
            <input class=\"w3-input w3-border\" name=\"given_name\" type=\"text\" {$placeholder['given_name']}>
          </div>
      </div>
      <div class=\"w3-row w3-section\">
        <div class=\"w3-col\" style=\"width:50px\"><i class=\"w3-xxlarge fa fa-user\"></i></div>
          <div class=\"w3-rest\">
            <input class=\"w3-input w3-border\" name=\"family_name\" type=\"text\" {$placeholder['family_name']}>
          </div>
      </div>
      <div class=\"w3-row w3-section\">
        <div class=\"w3-col\" style=\"width:50px\"><i class=\"w3-xxlarge fa fa-envelope-open\"></i></div>
          <div class=\"w3-rest\">
            <input class=\"w3-input w3-border\" name=\"email\" type=\"text\" {$placeholder['email']}>
          </div>
      </div>
      <div class=\"w3-row w3-section\">
        <div class=\"w3-col\" style=\"width:50px\"><i class=\"w3-xxlarge fa fa-pencil\"></i></div>
          <div class=\"w3-rest\">
            <input class=\"w3-input w3-border\" name=\"mensagem\" type=\"text\" placeholder=\"Mensagem ao Administrador do Sistema\">
          </div>
      </div>
      <p class=\"w3-center\">
      <button class=\"w3-button w3-section w3-green w3-ripple\" type=\"submit\" form=\"form_contato\" value=\"submit\" id=\"form_submit\" disabled> Enviar </button>
      </p>
      </form>";
  
      return $html;
     }
}
?>