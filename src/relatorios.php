<?php
namespace acquaccount;
use mysqli;
use DateTime;
use DatePeriod;
use DateInterval;
use DateTimeImmutable;
use Exception;

/**************************
 * Classe relatórios
 * ------------------
 * 
 * Produz relatórios
 * 
 */

class relatorios {
   private $id = 0;
   private $id_condominio = 0;

   function __construct() {
      $this->id = 0;
      $this->id_condominio = 0;
   }

   function classe_plural() {
      return "Relatórios";
   }

   function classe_singular() {
      return "Relatório";
   }

   function pega_adjetivo_novo() {
      return "Novo";
   }

   function pega_cor() {
      return "w3-deep-orange";
   }

   function pega_icone() {
      return "fa fa-file-invoice";
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