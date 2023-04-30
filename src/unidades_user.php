<?php
/**************************
 * Classe unidades_user
 * ------------------
 * 
 * Contém as informações relativas às unidades que um determinado usuário pode acessar,
 * além de funções de cadastro e consulta
 * 
 */

class unidades_user {
  private $id = 0;	
  private $id_user = 0;	
  private $ids_condominio;	
  private $ids_unidades;	
  private $data_modificado;

  function __construct() {
    $this->id = 0;	
    $this->id_user = 0;	
    $this->ids_condominio = null;	
    $this->ids_unidades = null;
    $this->data_modificado = null;
  }

  function salva_unidades_user($dados) {
      global $conecta_db;
      
      return true;
  }

  function deleta_unidades_user() {
      global $conecta_db;
      
      if ($this->id_user == 0) {
        $this->__construct();
        return false;
      }

      return $dados = $conecta_db->deleta_unidades_user($this, $this->id_user);     
  }

  function pega_unidades_user_por_id_user($id_user) {
      global $conecta_db;

      $dados_unidades_user = $conecta_db->pega_unidades_user_por_id_user($this, $id);
      if (is_null($dados_unidades_user)) {
          return false;
      }

      $this->id = $dados_unidades_user['id'];
      $this->id_user = $dados_unidades_user['id_user'];
      $this->ids_condominio = $dados_unidades_user['ids_condominio'];
      $this->ids_unidades = $dados_unidades_user['ids_unidades'];
      $this->data_modificado = $dados_unidades_user['data_modificado'];

      return true; 
  }
}
?>