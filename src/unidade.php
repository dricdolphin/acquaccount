<?php
namespace acquaccount;
use mysqli;
use DateTime;
use DatePeriod;
use DateInterval;
use DateTimeImmutable;
use Exception;

/**************************
 * Classe unidade
 * ------------------
 * 
 * Contém as informações do Unidade e as funções de cadastro e atualização
 * 
 */

class unidade {
   private $id = 0;
   private $id_condominio = 0;
   private $id_bloco;
   private $numero;	
   private $hidrometro;
   private $data_criado;	
   private $data_modificado;

   function __construct() {
      $this->id = 0;
      $this->id_condominio = 0;
      $this->id_bloco = null;
      $this->numero = null;
      $this->hidrometro = null;
      $this->data_criado = null;
      $this->data_modificado = null;
   }

   function classe_plural() {
      return "unidades";
   }

   function pega_adjetivo_novo() {
      return "Nova";
    }

   function classe_singular() {
      return "unidade";
   }

   function pega_unidade_por_id($id) {
      global $conecta_db;

      $dados_unidade = $conecta_db->pega_unidade_por_id($this, $id);
      if (is_null($dados_unidade)) {
         $this->__construct();
         return false;
      }

      $this->id = $dados_unidade['id'];
      $this->id_condominio = $dados_unidade['id_condominio'];
      $this->id_bloco = $dados_unidade['id_bloco'];
      $this->numero = $dados_unidade['numero'];
      $this->hidrometro = $dados_unidade['hidrometro'];   
      $this->data_criado = $dados_unidade['data_criado'];
      $this->data_modificado = $dados_unidade['data_modificado'];

      return true;    
   }

   function salva_unidade($dados) {
      global $conecta_db;
      
      return $dados_unidade = $conecta_db->salva_dados_unidade($this, $dados, $this->id);    
   }

   function deleta_unidade() {
      global $conecta_db;
      
      if ($this->id == 0) {
         return false;
      }
      
      $consumo_unidade = new consumo_unidade();
      if ($conecta_db->verifica_unidade_em_uso($consumo_unidade, $this->id) > 0) {
          $mensagem = [];
          $mensagem['dados'] = false;
          $mensagem['erro'] = true;
          $mensagem['mensagem_erro'] = "NÃO É POSSÍVEL DELETAR UMA UNIDADE COM DADOS DE CONSUMO!";
          
          return $mensagem;
      }    

      return $dados = $conecta_db->deleta_unidade($this, $this->id);     
   }

   function pega_numero() {
      return $this->numero;
   }

   function pega_id_condominio() {
      return $this->id_condominio;
   }

   function pega_id_bloco() {
      return $this->id_bloco;
   }

   function pega_id() {
      return $this->id;
   }

   function pega_nome() {
      return $this->numero;
   }

   function pega_cor() {
      return "w3-indigo";
   }

   function pega_icone() {
        return "fa fa-building-user";
   }

   function pega_ascendentes($inline = false) {
      $condominio = new condominio();
      $condominio->pega_condominio_por_id($this->id_condominio);
      $bloco = new bloco();
      $bloco->pega_bloco_por_id($this->id_bloco);
      $nome_bloco = $bloco->pega_nome();
      $nome_condominio = $condominio->pega_nome();

      if ($inline) { return "<b>{$nome_condominio}</b> - Bloco {$nome_bloco}"; }
      return "<b>{$nome_condominio}</b><br>Bloco {$nome_bloco}";
   }

   function pega_todos_ids($id_condominio = 0) {
      global $conecta_db;

      return $conecta_db->pega_todos_ids_unidade($this, $id_condominio);
   }

   function exibe_html($user, $perfil) {
      global $conecta_db;

      $condominio = new condominio();
      $bloco = new bloco();
      if ($this->id_condominio != 0) {
         $condominio->pega_condominio_por_id($this->id_condominio);
         $bloco->pega_bloco_por_id($this->id_bloco);
         $id_condominio = $this->id_condominio;
      } else {
         $ids_condominio = $condominio->pega_todos_ids($condominio);
         $id_condominio = $ids_condominio[0]['id'];
      }
      $options_condominio = $condominio->lista_select($perfil);
      $options_bloco = $bloco->lista_select($perfil, $id_condominio);

      $html = "         
      <div class=\"w3-row-padding w3-margin-bottom\">
          <form class=\"my-form w3-container\" id=\"form_dados\">
              <div class=\"form_cadastro\">
                  <input type=\"hidden\" id=\"id\" value=\"{$this->id}\">
                  <input type=\"hidden\" id=\"objeto\" value=\"unidade\">
                  <label for=\"id_condominio\">Condomínio</label>: <select class=\"w3-input\" name=\"id_condominio\" id=\"id_condominio\"> 
                  {$options_condominio}
                  </select><br>
                  <label for=\"id_bloco\">Bloco</label>: <select class=\"w3-input\" name=\"id_bloco\" id=\"id_bloco\"> 
                  {$options_bloco}
                  </select><br>
                  <label for=\"numero\">Número</label>: <input type=\"text\" class=\"w3-input\" name=\"numero\" id=\"numero\" value=\"{$this->numero}\"><br>
                  <label for=\"hidrometro\">Hidrômetro</label>: <input type=\"text\" class=\"w3-input\" name=\"hidrometro\" id=\"hidrometro\" value=\"{$this->hidrometro}\"><br>
              </div>
          </form>
      </div>";

      return $html;
   }
}
?>