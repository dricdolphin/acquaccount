<?php
namespace acquaccount;
use mysqli;
use DateTime;
use DatePeriod;
use DateInterval;
use DateTimeImmutable;
use Exception;

/**************************
 * Classe condominio
 * ------------------
 * 
 * Contém as informações do Condompínio e as funções de cadastro e atualização
 * 
 */

class condominio {
   private $id = 0;	
   private $nome;	
   private $descricao;	
   private $CEP;
   private $endereco;
   private $numero;
   private $bairro;
   private $cidade;
   private $estado;	
   private $data_criado;	
   private $data_modificado;

   function __construct() {
      $this->id = 0;	
      $this->nome = null;	
      $this->descricao = null;
      $this->CEP = null;
      $this->endereco = null;
      $this->numero = null;
      $this->bairro = null;
      $this->cidade = null;
      $this->estado = null;
      $this->data_criado = null;
      $this->data_modificado = null;
   }
    
   function classe_plural() {
      return "condomínios";
   }

   function classe_singular() {
      return "condomínio";
   }

   function pega_adjetivo_novo() {
      return "Novo";
   }

   function pega_condominio_por_id($id) {
      global $conecta_db;

      $dados_condominio = $conecta_db->pega_condominio_por_id($this, $id);
      if (is_null($dados_condominio)) {
         return false;
      }

      $this->id = $dados_condominio['id'];
      $this->nome = $dados_condominio['nome'];
      $this->descricao = $dados_condominio['descricao'];
      $this->CEP = $dados_condominio['CEP'];
      $this->endereco = $dados_condominio['endereco'];
      $this->numero = $dados_condominio['numero'];
      $this->bairro = $dados_condominio['bairro'];
      $this->cidade = $dados_condominio['cidade'];
      $this->estado = $dados_condominio['estado'];
      $this->data_criado = $dados_condominio['data_criado'];
      $this->data_modificado = $dados_condominio['data_modificado'];

      return true;    
   }

   function salva_condominio($dados) {
      global $conecta_db;
      
      return $dados_condominio = $conecta_db->salva_dados_condominio($this, $dados, $this->id);     
   }

   function deleta_condominio() {
      global $conecta_db;
      
      if ($this->id == 0) {
         return false;
      }
      
      $bloco = new bloco();
      if ($conecta_db->verifica_condominio_em_uso($bloco, $this->id) > 0) {
          $mensagem = [];
          $mensagem['dados'] = false;
          $mensagem['erro'] = true;
          $mensagem['mensagem_erro'] = "NÃO É POSSÍVEL DELETAR UM CONDOMÍNIO EM USO!";
          
          return $mensagem;
      }
      
      $unidade = new unidade();
      if ($conecta_db->verifica_condominio_em_uso($unidade, $this->id) > 0) {
          $mensagem = [];
          $mensagem['dados'] = false;
          $mensagem['erro'] = true;
          $mensagem['mensagem_erro'] = "NÃO É POSSÍVEL DELETAR UM CONDOMÍNIO EM USO!";
          
          return $mensagem;
      }      

      return $dados = $conecta_db->deleta_condominio($this, $this->id);     
   }

   function pega_id() {
      return $this->id;
   }
   
   function pega_nome() {
      return $this->nome;
   }

   function pega_icone() {
      return "fa fa-building";
   }

   function pega_cor() {
      return "w3-blue";
   }

   function pega_todos_ids() {
      global $conecta_db;

      return $conecta_db->pega_todos_condominio_id($this);
   }

   function pega_todos_condominio_id_nome() {
      global $conecta_db;

      return $conecta_db->pega_todos_condominio_id_nome($this);      
   }

   function pega_numero_unidades_condominio() {
      $unidade = new unidade();

      return count($unidade->pega_todos_ids($this->id));
   }

   function pega_ascendentes() {
      return "";
   }

   function lista_select($perfil) {
      global $conecta_db;

      $options_condominio = "";
      $condominios = $this->pega_todos_condominio_id_nome($this);
      foreach ($condominios as $chave => $valor) {
          $seleciona = "";
          if ($this->id == $valor['id']) {
              $seleciona = "selected";
          }
          $options_condominio .= "<option value=\"{$valor['id']}\" {$seleciona}>{$valor['nome']}</option>";
      }

      return $options_condominio;
   }

   function lista_checkbox($perfil, $ids_checked, $desabilita_edicao = '') {
      $condominios = $this->pega_todos_condominio_id_nome($this);

      $html_checkbox_condominio = "";
      foreach ($condominios as $chave => $valor) {
         $desabilita_edicao_final = $desabilita_edicao;
         $checkbox_condominio_checked = "";
         if (!($perfil->admin() || $perfil->autorizado_id_condominio($valor['id']) 
            || in_array($valor['id'], $ids_checked))) {
            continue;
         }
         
         if (in_array($valor['id'], $ids_checked)) {
            $checkbox_condominio_checked = "checked";
         }
         if (!($perfil->admin() || $perfil->autorizado_id_condominio($valor['id']))) {
            $desabilita_edicao_final = "disabled";
         }
         $html_checkbox_condominio .= "<input type=\"checkbox\" name=\"ids_condominio\" id=\"ids_condominio[]\" value=\"{$valor['id']}\" {$checkbox_condominio_checked} {$desabilita_edicao_final}> - <label for=\"ids_condominio\">{$valor['nome']}</label><br>";
      }
      
      return $html_checkbox_condominio;
   }

   function exibe_html($user, $perfil) {
      $html = "         
      <div class=\"w3-row-padding w3-margin-bottom\">
          <form class=\"my-form w3-container\" id=\"form_dados\">
              <div class=\"form_cadastro\">
                  <input type=\"hidden\" id=\"id\" value=\"{$this->id}\">
                  <input type=\"hidden\" id=\"objeto\" value=\"condominio\">
                  <label for=\"nome\">Nome</label>: <input type=\"text\" class=\"w3-input\" name=\"nome\" id=\"nome\" value=\"{$this->nome}\"><br>
                  <label for=\"descricao\">Descrição</label>: <input type=\"text\" class=\"w3-input\" name=\"descricao\" id=\"descricao\" value=\"{$this->descricao}\"><br>
                  <label for=\"CEP\">CEP</label>: <input type=\"text\" pattern=\"[0-9]*\" maxlength=\"8\" class=\"w3-input\" name=\"CEP\" id=\"CEP\" value=\"{$this->CEP}\"><br>
                  <label for=\"endereco\">Endereço</label>: <input type=\"text\" class=\"w3-input\" name=\"endereco\" id=\"endereco\" value=\"{$this->endereco}\"><br>
                  <label for=\"numero\">Número</label>: <input type=\"text\" class=\"w3-input\" name=\"numero\" id=\"numero\" value=\"{$this->numero}\"><br>
                  <label for=\"bairro\">Bairro</label>: <input type=\"text\" class=\"w3-input\" name=\"bairro\" id=\"bairro\" value=\"{$this->bairro}\"><br>
                  <label for=\"cidade\">Cidade</label>: <input type=\"text\" class=\"w3-input\" name=\"cidade\" id=\"cidade\" value=\"{$this->cidade}\"><br>
                  <label for=\"estado\">Estado</label>: <input type=\"text\" class=\"w3-input\" name=\"estado\" id=\"estado\" value=\"{$this->estado}\"><br>
                  </div>
          </form>
      </div>";

      return $html;
   }
}
?>