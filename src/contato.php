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
        global $conecta_db;
  
        $html = "";
  
        return $html;
     }
}
?>