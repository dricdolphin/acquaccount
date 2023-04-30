<?php
namespace acquaccount;
use mysqli;
use DateTime;
use DatePeriod;
use DateInterval;
use DateTimeImmutable;
use Exception;
use Countable;
use Iterator;

/**
 *  Classe links_perfil
 *  --------------------
 * 
 *  Contém os links, títulos, descrição e ícones
 *  das ações permitidas para cada Perfil
 * 
 * 
 */

 class links_perfil implements Countable, Iterator {
    private $nome = [];
    private $cor = [];
    private $icone = [];
    private $link = [];
    private $dashboard = [];
    private $position;
    private $total_links;

    function __construct() {
        $this->position = 0;
        
        /** LINK 0 */
        $this->nome[] = "Usuários";
        $this->cor[] = "w3-teal";
        $this->icone[] = "fa fa-users";
        $this->link[] = "user";
        $this->dashboard[] = true;
 
        /** LINK 1 */
        $this->nome[] = "Perfis";
        $this->cor[] = "w3-teal";
        $this->icone[] = "fa fa-id-card";
        $this->link[] = "perfil";
        $this->dashboard[] = true;

        /** LINK 2 */
        $this->nome[] = "Condomínios";
        $this->cor[] = "w3-blue";
        $this->icone[] = "fa fa-building";
        $this->link[] = "condominio";
        $this->dashboard[] = true;    
        
        /** LINK 3 */
        $this->nome[] = "Blocos";
        $this->cor[] = "w3-blue";
        $this->icone[] = "far fa-building";
        $this->link[] = "bloco";
        $this->dashboard[] = true;         

        /** LINK 4 */
        $this->nome[] = "Consumo do Condomínio";
        $this->cor[] = "w3-blue";
        $this->icone[] = "fa fa-money-bill-wave";
        $this->link[] = "consumo_condominio";
        $this->dashboard[] = false;

        /** LINK 5 */
        $this->nome[] = "Unidades";
        $this->cor[] = "w3-indigo";
        $this->icone[] = "fa fa-building-user";
        $this->link[] = "unidade";
        $this->dashboard[] = true;

        /** LINK 6 */
        $this->nome[] = "Consumo das Unidades";
        $this->cor[] = "w3-indigo";
        $this->icone[] = "fa fa-faucet-drip";
        $this->link[] = "consumo_unidade";
        $this->dashboard[] = false;

         /** LINK 7 */
         $this->nome[] = "Relatórios";
         $this->cor[] = "w3-deep-orange";
         $this->icone[] = "fa fa-file-invoice";
         $this->link[] = "relatorios";
         $this->dashboard[] = false;       

        $this->total_links = count($this->nome);
    }

    function pega_nome($id): string  {
        return $this->nome[$id];
    }

    function pega_cor($id): string  {
        return $this->cor[$id];
    }

    function pega_icone($id): string  {
        return $this->icone[$id];
    }

    function pega_link($id): string  {
        return $this->link[$id];
    }

    function dashboard($id): bool {
        return $this->dashboard[$id];
    }
    
    function lista_checkbox($perfil, $ids_checked, $desabilita_edicao = '') {
        $html_checkbox = "";
        foreach ($this->link as $chave => $valor) {
           $checkbox_checked = "";
           if (in_array($valor, $ids_checked)) {
              $checkbox_checked = "checked";
           }
           $html_checkbox .= "<input type=\"checkbox\" name=\"links_autorizado\" id=\"links_autorizado[]\" value=\"{$valor}\" {$checkbox_checked} {$desabilita_edicao}> - <label for=\"links_autorizado\">{$this->nome[$chave]}</label><br>";
        }
        
        return $html_checkbox;
    }

    /** Implementando metódos das Interfaces */
    function count(): int {
        return $this->total_links;
    }
    
    function rewind(): void {
        $this->position = 0;
    }
    
    public function current(): mixed {
        return $this->nome[$this->position];
    }
    
    public function key(): mixed {
        return $this->position;
    }

    public function next(): void {
        ++$this->position;
    }

    public function valid(): bool {
        return isset($this->nome[$this->position]);
    }   
 }
?>