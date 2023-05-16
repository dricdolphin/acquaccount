<?php
namespace acquaccount;
use mysqli;
use DateTime;
use DatePeriod;
use DateInterval;
use DateTimeImmutable;
use Exception;

/**************************
 * Classe consumo_unidade
 * ------------------
 * 
 * Contém as informações relativas ao consumo de uma determinada unidade,
 * além de funções de cadastro e consulta
 * 
 */

class consumo_unidade {
   private $id = 0;	
   private $id_unidade;	
   private $valor_m3;	
   private $imagem_consumo;	
   private $mes;	
   private $ano;	
   private $validado;
   private $id_validador;
   private $id_leiturista;
   private $data_validado;
   private $data_criado;	
   private $data_modificado;

   function __construct() {
      $this->id = 0;	
      $this->id_unidade = null;	
      $this->valor_m3 = null;
      $this->imagem_consumo = null;	
      $this->mes = null;
      $this->ano = null;
      $this->validado = null;
      $this->id_validador = null;
      $this->id_leiturista = null;
      $this->data_validado = null;
      $this->data_criado = null;
      $this->data_modificado = null;
   }

   function classe_plural() {
      return "Consumo das Unidades";
   }

   function classe_singular() {
      return "Consumo da Unidade";
   }

   function pega_consumo_unidade_por_id($id) {
      global $conecta_db;

      $dados_consumo_unidade = $conecta_db->pega_consumo_unidade_por_id($this, $id);
      if (is_null($dados_consumo_unidade)) {
         $this->id = 0; 
         return false;
      }

      $this->id = $dados_consumo_unidade['id'];
      $this->id_unidade = $dados_consumo_unidade['id_unidade'];
      $this->valor_m3 = $dados_consumo_unidade['valor_m3'];
      $this->imagem_consumo = $dados_consumo_unidade['imagem_consumo'];
      $this->mes = str_pad($dados_consumo_unidade['mes'], 2, '0', STR_PAD_LEFT);
      $this->ano = $dados_consumo_unidade['ano'];
      $this->validado = $dados_consumo_unidade['validado'];
      $this->id_validador = $dados_consumo_unidade['id_validador'];
      $this->data_validado = $dados_consumo_unidade['data_validado'];
      $this->id_leiturista = $dados_consumo_unidade['id_leiturista'];
      $this->data_criado = $dados_consumo_unidade['data_criado'];
      $this->data_modificado = $dados_consumo_unidade['data_modificado'];

      return true;    
   }

  function salva_consumo_unidade($dados) {
      global $conecta_db;

      $interval = new DateTime("{$dados['ano']}-{$dados['mes']}-01");
      $interval = $interval->sub(new DateInterval('P1M'));
      $mes = $interval->format('m');
      $ano = $interval->format('Y');
      
      $id_mes_anterior = $this->verifica_consumo_unidade($dados['id_unidade'], $mes, $ano);
      $dados_consumo_unidade = $conecta_db->pega_consumo_unidade_por_id($this, $id_mes_anterior);
      if (isset($dados_consumo_unidade['valor_m3'])) {
         if ($dados['valor_m3'] < $dados_consumo_unidade['valor_m3']) {
            return "<span class=\"w3-small\">A MEDIÇÃO DO MÊS ATUAL NÃO PODE SER MENOR QUE DE MESES ANTERIORES!<br>ÚLTIMA MEDIÇÃO: {$dados_consumo_unidade['valor_m3']} m³</span>";
         }
      }
      
      return $dados_consumo_unidade = $conecta_db->salva_dados_consumo_unidade($this, $dados, $this->id);     
   }

  function verifica_consumo_unidade($id_unidade, $mes, $ano) {
      global $conecta_db;

      return $conecta_db->verifica_consumo_unidade($id_unidade, $mes, $ano);
   }

   function pega_id() {
      return $this->id;
   }

   function pega_id_leiturista() {
      return $this->id_leiturista;
   }

   function pega_id_validador() {
      return $this->id_validador;
   }

   function pega_nome() {
      //return $this->nome;
      return "";
   }

   function pega_validado() {
      return $this->validado;
   }

   function pega_cor() {
      $cor = "w3-green";
      if ($this->id == 0) {
         $cor = "w3-deep-orange"; 
      } elseif (!$this->validado) {
         $cor = "w3-khaki";  
      }
      return $cor;
   }

   function pega_icone($padrao = false) {
      if ($padrao) { return "fa fa-faucet-drip"; }
      
      $icone['icone'] = "fa fa-faucet-drip";
      $icone['titulo_icone'] = "Consumo Validado"; 
      if ($this->id == 0) {
         $icone['icone'] = "fa fa-droplet-slash";
         $icone['titulo_icone'] = "Aguardando leitura"; 
      } elseif (!$this->validado) {
         $icone['icone'] = "fa fa-hand-holding-droplet";
         $icone['titulo_icone'] = "Aguardando validação da leitura"; 
      }
      
      return $icone;
   }

   function pega_ascendentes() {
      return "";
   }

   function pega_todos_ids($user, $perfil, $id_unidade) {
      global $conecta_db;

      return $conecta_db->pega_todos_consumo_unidade_ids($user, $perfil, $this, $id_unidade);
   }  

   function pega_meses_consumo($id_unidade = '') {
      global $conecta_db;

      return $conecta_db->pega_meses_consumo_unidade($id_unidade);
   }

   function pega_consumo_unidade_por_mes($perfil, $mes, $ano) {
      global $conecta_db;

      return $conecta_db->pega_consumo_unidade_por_mes($perfil, $mes, $ano);
   }

   function pega_html_consumos_unidade($user, $perfil, $id_unidade) {
      global $conecta_db;

      $html = "";
      $dados_chart = $this->pega_consumos_unidade($user, $perfil, $id_unidade);
      $consumo = "";
      $medicao = [];
      foreach ($dados_chart as $chave => $valor) {
         if (count($medicao) > 0) {
            $ultima_medicao = count($medicao) - 1;
            $calculo_consumo = $valor['medicao'] - $medicao[$ultima_medicao];
            $consumo = "\"v\": {$calculo_consumo}";
         }
         $mes_ano_reduz = substr($valor['mes_ano'],0,2)."/".substr($valor['mes_ano'],-2);
         $html .= "{\"c\":[{\"v\": \"{$mes_ano_reduz}\"}, {\"v\": {$valor['medicao']}}, {{$consumo}}]},";
         $medicao[] = $valor['medicao'];
      }
      if ($html != "") { $html = substr($html,0,-1); }    
      
       return $html;
   }

   function pega_consumos_unidade($user, $perfil, $id_unidade) {
      global $conecta_db;

      return $conecta_db->pega_consumos_unidade($user, $perfil, $id_unidade);
   }

   function pega_consumos_unidades_condominio_mes_ano($user, $perfil, $id_condominio, $mes, $ano) {
      global $conecta_db;

      return $conecta_db->pega_consumos_unidades_condominio_mes_ano($user, $perfil, $id_condominio, $mes, $ano);
   }

   function select_meses($mes = '', $ano = '') {
      $datas_select = $this->pega_meses_consumo($this->id_unidade);
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

   function pega_dados_chart_unidades($user, $perfil, $id_condominio = 0) {
      global $conecta_db;

      return $conecta_db->pega_dados_chart_unidades($this, $user, $perfil, $id_condominio);
   }
   
   function pega_dados_chart($user, $perfil, $id_condominio = 0) {
      $dados_chart = $this->pega_dados_chart_unidades($user, $perfil, $id_condominio);

      $html = "";
      foreach ($dados_chart as $chave => $valor) {
         $html .= "{\"c\":[{\"v\": \"{$valor['nome']}\"}, {\"v\": {$valor['valor']}}]},";
      }
      if ($html != "") { $html = substr($html,0,-1); }

      return $html;
   }

   function pega_cores_chart() {
      return "\"slices\": {
         \"0\": {\"color\":\"#ff5722\"}, \"1\": {\"color\":\"#f0e68c\"}, \"2\": {\"color\":\"#4CAF50\"}
      },";
   }

   function exibe_html($user, $perfil, $id_unidade, $mes='', $ano = '') {
      global $conecta_db;

      $unidade = new unidade();
      $unidade->pega_unidade_por_id($id_unidade);
      $bloco = new bloco();
      $bloco->pega_bloco_por_id($unidade->pega_id_bloco());
      $condominio = new condominio();
      $condominio->pega_condominio_por_id($unidade->pega_id_condominio());

      if (($mes) == '' || ($ano) == '') {
         $interval = new DateTime("now");
         $this->mes = $interval->format('m');
         $this->ano = $interval->format('Y');
      } elseif ($this->id == 0) {
         $this->mes = $mes;
         $this->ano = $ano;
      }

      $datas_option = $this->select_meses($this->mes, $this->ano);
       
      $chk_validado = "";
      if ($this->id == 0) {
         $chk_validado = "disabled";
         $this->id_leiturista = $user->pega_id();
         $this->id_validador =$user->pega_id();
      }

      $valor_m3_validado = "";
      $label_validado = " Validar a leitura";  
      if (!($perfil->admin() || $perfil->cadastrador() || $perfil->autorizado($user, "consumo_unidade", $unidade->pega_id_condominio()))) {
         $valor_m3_validado = "disabled";
         $chk_validado = "disabled";
      }
      
      if ($this->validado) {
         $interval = new DateTime($this->data_validado);
         $data_validado_formatada = $interval->format('Y-m-d H:i');
         $validador = $user;
         $validador->pega_user_por_id($this->id_validador);
         $valor_m3_validado = "disabled";
         $chk_validado = "checked disabled";
         $label_validado = " Leitura validada por {$validador->pega_nome_completo()} em {$data_validado_formatada}";
      } else {
         $this->id_validador = $user->pega_id();
      }
      $leiturista = $user;
      $leiturista->pega_user_por_id($this->id_leiturista);
      $nome_leiturista = $leiturista->pega_nome_completo();

      $html = "<h5>Unidade {$unidade->pega_numero()} - Bloco {$bloco->pega_nome()} - Condomínio '{$condominio->pega_nome()}'</b></h5>
         <div class=\"w3-row-padding w3-margin-bottom\">
         <form class=\"my-form w3-container\" id=\"form_dados\">
         <input type=\"hidden\" id=\"id\" value=\"{$this->id}\">
         <input type=\"hidden\" id=\"objeto\" value=\"consumo_unidade\">
         <input type=\"hidden\" id=\"id_unidade\" value=\"{$id_unidade}\">
         <input type=\"hidden\" id=\"id_leiturista\" value=\"{$this->id_leiturista}\">
         <div class=\"form_cadastro\">
            <p id=\"nome_leiturista\">Leitura realizada por {$nome_leiturista}</p>
            <label for=\"mes_ano\">Mês da Leitura</label>: 
            <select class=\"w3-input\" name=\"mes_ano\" id=\"mes_ano\" disabled> 
            {$datas_option}
            </select>
            <input type=\"hidden\" id=\"mes\" value=\"{$this->mes}\">
            <input type=\"hidden\" id=\"ano\" value=\"{$this->ano}\">
         </div>
         
         <div class=\"form_cadastro\">
            <label for=\"valor_m3\">Leitura (m³)</label>: <input type=\"text\" pattern=\"[0-9]*\" class=\"w3-input\" id=\"valor_m3\" name=\"valor_m3\" value=\"{$this->valor_m3}\" {$valor_m3_validado}><br>
            <input type=\"checkbox\" id=\"validado\" name=\"validado\"\ value=\"1\" {$chk_validado}> - <label id=\"label_validado\" for=\"validado\">{$label_validado}</label>
            <input type=\"hidden\" id=\"id_validador\" value=\"{$this->id_validador}\">
            <input type=\"hidden\" id=\"data_validado\" value=\"{$this->data_validado}\">
         </div>
         <div class=\"form_cadastro\" id=\"drop-area\">
            <p>Carregue a imagem da Leitura realizada para a Unidade</p>
            <input type=\"file\" id=\"fileElem\" accept=\"image/*\" onchange=\"handleFiles(this.files)\" {$valor_m3_validado}>
            <label class=\"button {$valor_m3_validado}\" for=\"fileElem\" id=\"label_button\">Selecione a imagem</label>
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