<?php
namespace acquaccount;
use mysqli;
use DateTime;
use DatePeriod;
use DateInterval;
use DateTimeImmutable;
use Exception;

/**************************
 * Classe relatorio_condominio
 * ------------------
 * 
 * Produz relatórios
 * 
 */

class relatorio_condominio {
    private $id;
    private $id_condominio;
    private $taxa_minima;
    private $taxa_m3_completa;
    private $taxa_m3;
    private $consumo_condominio_m3;
    private $mes;
    private $ano;

    function __construct() {

    }
}