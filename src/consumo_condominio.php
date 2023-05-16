<?php
namespace acquaccount;
use mysqli;
use DateTime;
use DatePeriod;
use DateInterval;
use DateTimeImmutable;
use Exception;

/**************************
 * Classe consumo_condominio
 * ------------------
 * 
 * Contém as informações relativas ao consumo de um determinado condomínio,
 * além de funções de cadastro e consulta
 * 
 */

class consumo_condominio {
   private $id = 0;	
   private $id_condominio;	
   private $valor_m3 = 0;	
   private $consumo = 0;
   private $valor_reais = 0;
   private $valor_minimo_reais = 0;		
   private $imagem_consumo;	
   private $mes;	
   private $ano;	
   private $data_criado;	
   private $data_modificado;

   function __construct() {
      $this->id = 0;	
      $this->id_condominio;	
      $this->valor_m3 = 0;	
      $this->consumo = 0;
      $this->valor_reais = 0;
      $this->valor_minimo_reais = 0;		
      $this->imagem_consumo = null;	
      $this->mes = null;
      $this->ano = null;
      $this->data_criado = null;	
      $this->data_modificado = null;
   }

   function classe_plural() {
      return "Consumo dos Condomínios";
   }

   function classe_singular() {
      return "Consumo do Condomínio";
   }

   function pega_consumo_condominio_por_id($id) {
      global $conecta_db;

      $dados_consumo_condominio = $conecta_db->pega_consumo_condominio_por_id($this, $id);
      if (is_null($dados_consumo_condominio)) {
         $this->__construct();
         return false;
      }

      $this->id = $dados_consumo_condominio['id'];
      $this->id_condominio = $dados_consumo_condominio['id_condominio'];
      $this->valor_m3 = $dados_consumo_condominio['valor_m3'];
      $this->consumo = $dados_consumo_condominio['consumo'];
      $this->valor_reais = $dados_consumo_condominio['valor_reais'];
      $this->valor_minimo_reais = $dados_consumo_condominio['valor_minimo_reais'];
      $this->imagem_consumo = $dados_consumo_condominio['imagem_consumo'];
      $this->mes = str_pad($dados_consumo_condominio['mes'], 2, '0', STR_PAD_LEFT);
      $this->ano = $dados_consumo_condominio['ano'];
      $this->data_criado = $dados_consumo_condominio['data_criado'];
      $this->data_modificado = $dados_consumo_condominio['data_modificado'];

      return true;    
   }

   function salva_consumo_condominio($dados) {
      global $conecta_db;
      
      return $dados_consumo_condominio = $conecta_db->salva_dados_consumo_condominio($this, $dados, $this->id);     
   }    
     
   function verifica_consumo_condominio($id_condominio, $mes, $ano) {
      global $conecta_db;

      return $conecta_db->verifica_consumo_condominio($id_condominio, $mes, $ano);
   }

   function pega_id() {
      return $this->id;
   }

   function pega_nome() {
      //return $this->nome;
      return "";
   }

   function pega_cor() {
      $cor = "w3-blue";
      if ($this->id == 0) {
         $cor = "w3-deep-orange"; 
      }
      return $cor;
   }

   function pega_icone($padrao = false) {
      if ($padrao) { return "fa fa-money-bill-wave"; }

      $icone['icone'] = "fa fa-money-bill-wave";
      $icone['titulo_icone'] = "Consumo lançado"; 
      if ($this->id == 0) {
         $icone['icone'] = "fa fa-hand-holding-dollar";
         $icone['titulo_icone'] = "Aguardando leitura"; 
      }
      
      return $icone;
   }
   
   function pega_ascendentes() {
      return "";
   }

   function pega_todos_ids() {
      global $conecta_db;

      return array();
   }

   function pega_valor_minimo_reais() {
      return str_replace(",",".",$this->valor_minimo_reais);
   }

   function pega_valor_reais() {
      return str_replace(",",".",$this->valor_reais);
   }

   function pega_valor_m3() {
      return $this->valor_m3;
   }

   function pega_meses_consumo($id_condominio = '') {
      global $conecta_db;

      return $conecta_db->pega_meses_consumo_condominio($id_condominio);
   }

   function pega_consumo_condominio_por_mes($perfil, $mes, $ano) {
      global $conecta_db;

      return $conecta_db->pega_consumo_condominio_por_mes($perfil, $mes, $ano);
   }

   function select_meses($mes = '', $ano = '') {
      $datas_select = $this->pega_meses_consumo($this->id_condominio);
      $datas_option = "";

      if ($mes == '' && $ano == '') {
         $mes = $this->mes;
         $ano = $this->ano;
      }
      foreach ($datas_select as $chave => $valor) {
         $mes_ano = str_replace("/","",$valor);
         if ($valor == "{$mes}/{$ano}") {
            $datas_option .= "<option value=\"{$mes_ano}\" selected>{$valor}</option>";
         } else {
            $datas_option .= "\n<option value=\"{$mes_ano}\">{$valor}</option>";
         }
      }

      return $datas_option;
   }

   function pega_dados_chart($user, $perfil) {
      global $conecta_db;

      $dados_chart = $conecta_db->pega_dados_chart_condominios($this, $user, $perfil);

      $html = "";
      foreach ($dados_chart as $chave => $valor) {
         $html .= "{\"c\":[{\"v\": \"{$valor['nome']}\"}, {\"v\": {$valor['valor']}}]},";
      }
      if ($html != "") { $html = substr($html,0,-1); }

      return $html;
   }

   function pega_cores_chart() {
      return "\"colors\": [\"#ff5722\",\"#2196F3\"],";
   }
   
   function pega_consumos_condominio($user, $perfil, $id_condominio) {
      global $conecta_db;

      return $conecta_db->pega_consumos_condominio($user, $perfil, $id_condominio);
   }

   function pega_consumos_unidades_condominio($user, $perfil, $id_condominio) {
      global $conecta_db;

      return $conecta_db->pega_consumos_unidades_condominio($user, $perfil, $id_condominio);
   }

   function pega_html_consumos_condominio($user, $perfil, $id_condominio) {
      global $conecta_db;

      $html = "";
      $dados_chart = $this->pega_consumos_condominio($user, $perfil, $id_condominio);
      $consumo = "";
      $medicao = [];
      foreach ($dados_chart as $chave => $valor) {
         if (count($medicao) > 0) {
            $ultima_medicao = count($medicao) - 1;
            //$calculo_consumo = $valor['medicao'] - $medicao[$ultima_medicao];
            $consumo = "\"v\": {$valor['consumo']}";
         }
         $mes_ano_reduz = substr($valor['mes_ano'],0,2)."/".substr($valor['mes_ano'],-2);
         $html .= "{\"c\":[{\"v\": \"{$mes_ano_reduz}\"}, {\"v\": {$valor['medicao']}}, {{$consumo}}]},";
         $medicao[] = $valor['medicao'];
      }
      if ($html != "") { $html = substr($html,0,-1); }    
      
       return $html;
   }

   function exibe_html($user, $perfil, $id_condominio, $mes, $ano) {
      global $conecta_db;

      $condominio = new condominio();
      $condominio->pega_condominio_por_id($id_condominio);

      if (($mes) == '' || ($ano) == '') {
         $interval = new DateTime("now");
         $this->mes = $interval->format('m');
         $this->ano = $interval->format('Y');
      } elseif ($this->id == 0) {
         $this->mes = $mes;
         $this->ano = $ano;
      }

      $desabilita_edicao = "disabled";
      if ($perfil->admin() || ($perfil->cadastrador() && $perfil->autorizado($user, "consumo_condominio", $id_condominio))) {
         $desabilita_edicao = "";
      }

      $datas_option = $this->select_meses($this->mes, $this->ano);
       
      $html = "<h5>Condomínio '{$condominio->pega_nome()}'</b></h5>
         <div class=\"w3-row-padding w3-margin-bottom\">
         <form class=\"my-form w3-container\" id=\"form_dados\">
         <input type=\"hidden\" id=\"id\" value=\"{$this->id}\">
         <input type=\"hidden\" id=\"objeto\" value=\"consumo_condominio\">
         <input type=\"hidden\" id=\"id_condominio\" value=\"{$id_condominio}\">
         <div class=\"form_cadastro\">
            <label for=\"mes_ano\">Mês da Leitura</label>: 
            <select class=\"w3-input\" name=\"mes_ano\" id=\"mes_ano\" disabled> 
            {$datas_option}
            </select>
            <input type=\"hidden\" id=\"mes\" value=\"{$this->mes}\">
            <input type=\"hidden\" id=\"ano\" value=\"{$this->ano}\">
         </div>
         
         <div class=\"form_cadastro\">
            <label for=\"valor_m3\">Leitura (m³)</label>: <input type=\"text\" pattern=\"[0-9]*\" class=\"w3-input\" id=\"valor_m3\" name=\"valor_m3\" value=\"{$this->valor_m3}\" {$desabilita_edicao}><br>
            <label for=\"consumo\">Consumo (m³)</label>: <input type=\"text\" pattern=\"[0-9]*\" class=\"w3-input\" id=\"consumo\" name=\"consumo\" value=\"{$this->consumo}\" {$desabilita_edicao}><br>
            <label for=\"valor_minimo_reais\">Valor Consumo Mínimo (R$)</label>: <input type=\"text\" pattern=\"[0-9]*[,]{0,1}[0-9]{0,2}\" class=\"w3-input\" id=\"valor_minimo_reais\" name=\"valor_minimo_reais\" value=\"{$this->valor_minimo_reais}\" {$desabilita_edicao}><br>
            <label for=\"valor_reais\">Valor Total(R$)</label>: <input type=\"text\" pattern=\"[0-9]*[,]{0,1}[0-9]{0,2}\" class=\"w3-input\" id=\"valor_reais\" name=\"valor_reais\" value=\"{$this->valor_reais}\" {$desabilita_edicao}>
         </div>
         <div class=\"form_cadastro\" id=\"drop-area\">
            <p>Carregue a imagem da Conta de Água do Condomínio</p>
            <input type=\"file\" id=\"fileElem\" accept=\"image/*\" onchange=\"handleFiles(this.files)\">
            <label class=\"button\" for=\"fileElem\" id=\"label_button\" {$desabilita_edicao}>Selecione a imagem</label>
            <input type=\"hidden\" id=\"imagem_consumo\" value =\"{$this->imagem_consumo}\">
         </form>
         <progress id=\"progress-bar\" max=100 value=0></progress>
         <div id=\"gallery\" class=\"img-magnifier-container\">
            <img id=\"img_imagem_consumo\" src=\"{$this->imagem_consumo}\">
         </div>
         </div>
         </div>
      ";

      return $html;
    }
}
?>