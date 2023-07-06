<?php
namespace acquaccount;
use mysqli;
use DateTime;
use DatePeriod;
use DateInterval;
use DateTimeImmutable;
use Exception;

/**************************
 * Classe relatório
 * ------------------
 * 
 * Produz relatórios
 * 
 */

class relatorio {
   private $flag_salvar_cache = false;
   private $dados_cache_unidades = [];
   private $dados_cache_condominio = [];

   function __construct() {
      $flag_salvar_cache = false;
      $dados_cache_unidades = [];
      $dados_cache_condominio = [];
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

   function pega_flag_salvar_cache() {
      return $flag_salvar_cache;
   }

   function define_flag_salvar_cache() {
      $this->flag_salvar_cache = !$this->flag_salvar_cache;
   }

   function pega_relatorio_por_id_unidade($id_unidade, $mes = "", $ano = "") {
      global $conecta_db;

      return "";
   }

   function pega_dados_consumo_unidades_por_id_condominio_mes_ano($user, $perfil, $id_condominio, $mes = "", $ano = "") 
   {
      global $conecta_db;

      $consumo_unidade = new consumo_unidade();

      $dados_consumo_unidades = $conecta_db->pega_cache_consumos_unidades_condominio_mes_ano($user, $perfil, $id_condominio, 
         $mes, $ano);
      if (count($dados_consumo_unidades) == 0) {
         $dados_consumo_unidades = [];
         $this->dados_cache_unidades = [];
         
         $interval = new DateTime("{$ano}-{$mes}-01");
         $interval = $interval->sub(new DateInterval('P1M'));
         $mes_anterior = $interval->format('m');
         $ano_anterior = $interval->format('Y');

         $dados_consumo_unidades_mes_atual = $consumo_unidade->pega_consumos_unidades_condominio_mes_ano($user, $perfil, $id_condominio, 
             $mes, $ano);
         $dados_consumo_unidades_mes_anterior = $consumo_unidade->pega_consumos_unidades_condominio_mes_ano($user, $perfil, $id_condominio, 
             $mes_anterior, $ano_anterior);
         
         foreach ($dados_consumo_unidades_mes_atual as $chave => $valor) {
             $dados_consumo_unidades[$chave]['valor_m3'] = $valor['valor_m3'] - $dados_consumo_unidades_mes_anterior[$chave]['valor_m3'];
             if ($dados_consumo_unidades[$chave]['valor_m3'] < 0) {
                 $dados_consumo_unidades[$chave]['valor_m3'] = 0;
             }
             $dados_consumo_unidades[$chave]['numero_unidade'] = $valor['numero_unidade'];
            
            //Se processou, precisa salvar!
            $this->dados_cache_unidades[$chave]['id_unidade'] = $valor['id_unidade'];
            $this->dados_cache_unidades[$chave]['valor_m3'] = $dados_consumo_unidades[$chave]['valor_m3'];
            $this->dados_cache_unidades[$chave]['valor_reais'] = 0;
            $this->dados_cache_unidades[$chave]['mes'] = $mes;
            $this->dados_cache_unidades[$chave]['ano'] = $ano;
         }
      }

      return $dados_consumo_unidades;
   }

   function pega_dados_relatorio_por_id_condominio_mes_ano ($user, $perfil, $id_condominio, $dados_consumo_unidades, 
      $mes = "", $ano = "") 
   {
      global $conecta_db;

      $condominio = new condominio();
      $condominio->pega_condominio_por_id($id_condominio);
      $consumo_condominio = new consumo_condominio();

      $numero_unidades = (int) $condominio->pega_numero_unidades_condominio();
      $id_consumo_condominio = $consumo_condominio->verifica_consumo_condominio($id_condominio, $mes, $ano);
      $consumo_condominio->pega_consumo_condominio_por_id($id_consumo_condominio);
      $consumo_minimo = $consumo_condominio->pega_valor_minimo_reais();
      $consumo_total = $consumo_condominio->pega_valor_reais();

      $dados_consumo_condominio = $conecta_db->pega_cache_consumo_condominio_mes_ano($user, $perfil, $id_condominio, 
         $mes, $ano);
      if (count($dados_consumo_condominio) == 0) {
         $dados_consumo_condominio = [];
         $dados_consumo_condominio['consumo_condominio_m3'] = array_sum(array_column($dados_consumo_unidades, 'valor_m3'));
         $dados_consumo_condominio['taxa_minima'] = number_format(round($consumo_minimo/$numero_unidades, 2), 2, ',', '');
         if ($dados_consumo_condominio['consumo_condominio_m3'] == 0) {
             $dados_consumo_condominio['taxa_m3_completa'] = "0,00";
             $dados_consumo_condominio['taxa_m3'] = "0,00";
         } else {
             $dados_consumo_condominio['taxa_m3_completa'] = ($consumo_total - $consumo_minimo)/$dados_consumo_condominio['consumo_condominio_m3'];
             $dados_consumo_condominio['taxa_m3'] = number_format(round(($consumo_total - $consumo_minimo)/$dados_consumo_condominio['consumo_condominio_m3'], 2), 2, ',', '');
         }
         $this->dados_cache_condominio = [];
         $this->dados_cache_condominio['id_condominio'] = $id_condominio;
         $this->dados_cache_condominio['taxa_minima'] = $dados_consumo_condominio['taxa_minima'];
         $this->dados_cache_condominio['taxa_m3_completa'] = $dados_consumo_condominio['taxa_m3_completa'];
         $this->dados_cache_condominio['taxa_m3'] = $dados_consumo_condominio['taxa_m3'];
         $this->dados_cache_condominio['consumo_condominio_m3'] = $dados_consumo_condominio['consumo_condominio_m3'];
         $this->dados_cache_condominio['mes'] = $mes;
         $this->dados_cache_condominio['ano'] = $ano;
         $this->salva_dados_cache();
      }

      return $dados_consumo_condominio;
   }

   private function salva_dados_cache() {
      global $conecta_db;
      
      $relatorio_unidade = new relatorio_unidade();
      $relatorio_condominio = new relatorio_condominio();

      foreach ($this->dados_cache_unidades as $chave => $valor) {
         $dados = [];
         $dados['id_unidade'] = $valor['id_unidade'];
         $dados['valor_m3'] = $valor['valor_m3'];
         $dados['valor_reais'] = str_replace(",",".",$this->dados_cache_condominio['taxa_minima']) + 
            str_replace(",",".",$this->dados_cache_condominio['taxa_m3']) * $dados['valor_m3'];
         $dados['mes'] = $valor['mes'];
         $dados['ano'] = $valor['ano'];

         $salva = $conecta_db->salva_dados_cache_relatorio_unidades($relatorio_unidade, $dados, 'id');
      }

      $salva = $conecta_db->salva_dados_cache_relatorio_condominio($relatorio_condominio, $this->dados_cache_condominio, 'id');
   }

   function pega_relatorio_por_id_condominio($user, $perfil, $id_condominio, $mes = "", $ano = "") {
      global $conecta_db;
      //O Consumo do Condomínio em m³ é a soma dos consumo de TODAS as unidades
      //$consumo_condominio_m3 = (float) $consumo_condominio->pega_valor_m3();
      $dados_consumo_unidades = $this->pega_dados_consumo_unidades_por_id_condominio_mes_ano($user, $perfil, $id_condominio, $mes, $ano);
      
      $dados_consumo_condominio = $this->pega_dados_relatorio_por_id_condominio_mes_ano($user, $perfil, $id_condominio, 
         $dados_consumo_unidades, $mes, $ano);

      $dados_relatorio = [];
      $dados_relatorio['dados_consumo_unidades'] = $dados_consumo_unidades; 
      $dados_relatorio['taxa_minima'] = $dados_consumo_condominio['taxa_minima']; 
      $dados_relatorio['taxa_m3_completa'] = $dados_consumo_condominio['taxa_m3_completa'];
      $dados_relatorio['taxa_m3'] = $dados_consumo_condominio['taxa_m3'];
      $dados_relatorio['consumo_condominio_m3'] = $dados_consumo_condominio['consumo_condominio_m3'];

      return $dados_relatorio;
   }

   function html_tabela_consumo_unidades($dados_consumo_unidades, $taxa_minima, $taxa_m3, $id_condominio) {
      $condominio = new condominio();
      $condominio->pega_condominio_por_id($id_condominio);
      $nome_condominio = $condominio->pega_nome();
      $html = "<div class=\"w3-button w3-teal div_para_imprimir\"><i class=\"fas fa-file-csv w3-large\"> &nbsp; </i><a href=\"#\" id=\"link_tabela_consumo_condominio_{$id_condominio}\">Exportar Tabela</a></div>
      <div class=\"w3-button w3-red div_para_imprimir\"><i class=\"fas fa-file-pdf w3-large\"> &nbsp; </i><a href=\"#\" id=\"link_imprimir_tabela_consumo_condominio_{$id_condominio}\">Imprimir Tabela</a></div>
      <table class=\"w3-table w3-white w3-striped w3-bordered\" id=\"tabela_consumo_condominio_{$id_condominio}\" data_nome_condominio=\"{$nome_condominio}\">
      <thead>
      <tr class=\"w3-indigo\"><th>Unidade</th><th>Consumo (m³)</th><th>Taxa Mínima (R$)</th><th>Valor Consumo m³ (R$)</th><th>Valor Total (R$)</th></tr>
      </thead>
      <tbody>";
      foreach ($dados_consumo_unidades as $chave => $valor) {
          $valor_consumo_m3 =  str_replace(",",".",$taxa_m3)*$valor['valor_m3'];
          $valor_total = number_format(round($valor_consumo_m3 +  str_replace(",",".",$taxa_minima), 2), 2, ',', '');
          if (!isset($dados_consumo_unidades[$chave]['valor_reais'])) {
              $valor_consumo_m3_arredondado = number_format(round($valor_consumo_m3, 2), 2, ',', '');
          } else {
              $valor_consumo_m3_arredondado = $dados_consumo_unidades[$chave]['valor_reais'];
          }
          
          $html .= "<tr><td>{$valor['numero_unidade']}</td><td>{$valor['valor_m3']}</td><td>{$taxa_minima}</td><td>{$valor_consumo_m3_arredondado}</td><td>{$valor_total}</td></tr>";
      }

      $html .= "</tbody>
      </table>";
      return $html;
   }

   function exibe_html($user, $perfil) {
      global $conecta_db;

      $html = "";

      return $html;
   }
}
?>