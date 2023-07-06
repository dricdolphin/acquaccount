<?php
namespace acquaccount;
use mysqli;
use DateTime;
use DatePeriod;
use DateInterval;
use DateTimeImmutable;
use Exception;

/**************************
 * Classe relatorio_unidade
 * ------------------
 * 
 * Produz relatórios
 * 
 */

class relatorio_unidade {
    private $id; 
    private $id_unidade; 
    private $valor_m3; 
    private $valor_reais; 
    private $mes; 
    private $ano;
    
    function __construct() {
        
    }
}