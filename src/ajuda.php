<?php
namespace acquaccount;
use mysqli;
use DateTime;
use DatePeriod;
use DateInterval;
use DateTimeImmutable;
use Exception;

/**************************
 * Classe ajuda
 * ------------------
 * 
 * Controla a visualização dos arquivos de ajuda
 * 
 */

class ajuda {
    
    function __construct() {

    }

    function classe_plural() {
        return "Ajuda";
     }
  
     function classe_singular() {
        return "Ajuda";
     }
  
     function pega_adjetivo_novo() {
        return "Nova";
     }
  
     function pega_cor() {
        return "w3-green";
     }
  
     function pega_icone() {
        return "far fa-circle-question";
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