<?php
namespace acquaccount;
use mysqli;
use DateTime;
use DatePeriod;
use DateInterval;
use DateTimeImmutable;
use Exception;

/**************************
 * Classe bloco
 * ------------------
 * 
 * Contém as informações do Bloco e as funções de cadastro e atualização
 * 
 */

class bloco {
   private $id = 0;	
   private $id_condominio = 0;	
   private $nome;	
   private $data_criado;	
   private $data_modificado;

   function __construct() {
      $this->id = 0;	
      $this->id_condominio = 0;	
      $this->nome = null;	
      $this->data_criado = null	;
      $this->data_modificado = null;
   }

   function classe_plural() {
      return "blocos";
   }

   function classe_singular() {
      return "bloco";
   }

   function pega_adjetivo_novo() {
      return "Novo";
   }

   function pega_bloco_por_id($id) {
      global $conecta_db;

      $dados_bloco = $conecta_db->pega_bloco_por_id($this, $id);
      if (is_null($dados_bloco)) {
         
         return false;
      }

      $this->id = $dados_bloco['id'];
      $this->id_condominio = $dados_bloco['id_condominio'];
      $this->nome = $dados_bloco['nome'];
      $this->data_criado = $dados_bloco['data_criado'];
      $this->data_modificado = $dados_bloco['data_modificado'];

      return true;    
   }

   function salva_bloco($dados) {
      global $conecta_db;
      
      return $dados_bloco = $conecta_db->salva_dados_bloco($this, $dados, $this->id);     
   }

   function deleta_bloco() {
      global $conecta_db;
      
      if ($this->id == 0) {
         return false;
      }
      
      $unidade = new unidade();
      if ($conecta_db->verifica_bloco_em_uso($unidade, $this->id) > 0) {
          $mensagem = [];
          $mensagem['dados'] = false;
          $mensagem['erro'] = true;
          $mensagem['mensagem_erro'] = "NÃO É POSSÍVEL DELETAR UM BLOCO EM USO!";
          
          return $mensagem;
      }      

      return $dados = $conecta_db->deleta_bloco($this, $this->id);     
   }

   function pega_id() {
      return $this->id;
   }

   function pega_nome() {
      return $this->nome;
   }

   function pega_cor() {
      return "w3-blue";
   }

   function pega_ascendentes() {
      $condominio = new condominio();
      $condominio->pega_condominio_por_id($this->id_condominio);
      $condominio_html = $condominio->pega_nome();

      return "<b>{$condominio_html}</b>";
   }

   function pega_icone() {
        return "far fa-building";
   }

   function pega_todos_ids() {
      global $conecta_db;

      return $conecta_db->pega_todos_bloco_id($this);
   }

   function lista_select($perfil, $id_condominio = '') {
      global $conecta_db;

      if ($id_condominio == '') {
         $id_condominio =  $this->id_condominio;
      }
      $options_bloco = "";
      $blocos = $conecta_db->pega_todos_bloco_id_nome($this, $id_condominio);
      foreach ($blocos as $chave => $valor) {
          $seleciona = "";
          if ($this->id == $valor['id']) {
              $seleciona = "selected";
          }
          $options_bloco .= "<option value=\"{$valor['id']}\" {$seleciona}>{$valor['nome']}</option>";
      }

      return $options_bloco;
   }

   function exibe_html($user, $perfil) {
      $condominio = new condominio();
      if ($this->id_condominio != '') {
         $condominio->pega_condominio_por_id($this->id_condominio);
      }
      $lista_select = $condominio->lista_select($perfil);
      
      $html = "         
      <div class=\"w3-row-padding w3-margin-bottom\">
          <form class=\"my-form w3-container\" id=\"form_dados\">
              <div class=\"form_cadastro\">
                  <input type=\"hidden\" id=\"id\" value=\"{$this->id}\">
                  <input type=\"hidden\" id=\"objeto\" value=\"bloco\">
                  <label for=\"id_condominio\">Condomínio</label>:
                  <select class=\"w3-input\" id=\"id_condominio\" name=\"id_condominio\">
                  {$lista_select}
                  </select><br>
                  <label for=\"nome\">Nome</label>: <input type=\"text\" class=\"w3-input\" name=\"nome\" id=\"nome\" value=\"{$this->nome}\"><br>
                   </div>
          </form>
      </div>";

      return $html;
   }

}
?>