<?php
/**************************
 * Classe perfil
 * ------------------
 * 
 * Contém as informações relativas ao perfil dos usuários,
 * além de funções de cadastro e consulta
 * 
 */

class perfil {
    private $id = 0;
    private $nome;
    private $descricao;
    private $admin_master;
    private $sindico;
    private $cadastrador;
    private $links_autorizado = [];
    private $ids_condominio = [];
    private $data_criado;
    private $data_modificado;
  
    function __construct() {
        $this->id = 0;
        $this->nome = null;
        $this->descricao = null;
        $this->admin_master = null;
        $this->sindico = null;
        $this->cadastrador = null;
        $this->links_autorizado = [];
        $this->ids_condominio = [];
        $this->data_criado = null;
        $this->data_modificado = null;     
    }
    
    function classe_plural() {
        return "perfis";
      }
   
    function classe_singular() {
        return "perfil";
    }

    function pega_adjetivo_novo() {
        return "Novo";
      }

    function pega_perfil_por_id($id) {
        global $conecta_db;

        $dados_perfil = $conecta_db->pega_perfil_por_id($this, $id);
        if (is_null($dados_perfil)) {
            $this->__construct();
            return false;
        }

        $this->id = $dados_perfil['id'];
        $this->nome = $dados_perfil['nome'];
        $this->descricao = $dados_perfil['descricao'];
        $this->admin_master = $dados_perfil['admin_master'];
        $this->sindico = $dados_perfil['sindico'];
        $this->cadastrador = $dados_perfil['cadastrador'];
        $this->data_criado = $dados_perfil['data_criado'];
        $this->data_modificado = $dados_perfil['data_modificado'];

        $this->links_autorizado = unserialize($dados_perfil['links_autorizado']);
        $this->ids_condominio = unserialize($dados_perfil['ids_condominio']);

        return true;    
    }

    function salva_perfil($dados) {
        global $conecta_db;
      
        return  $dados_perfil = $conecta_db->salva_dados_perfil($this, $dados, $this->id);     
    }

    function deleta_perfil() {
        global $conecta_db;
        
        if ($this->id == 0) {
           return false;
        }
        
        $user = new user();
        if ($conecta_db->verifica_perfil_em_uso($user, $this->id) > 0) {
            $mensagem = [];
            $mensagem['dados'] = false;
            $mensagem['erro'] = true;
            $mensagem['mensagem_erro'] = "NÃO É POSSÍVEL DELETAR UM PERFIL EM USO!";
            
            return $mensagem;
        }
        
        return $dados = $conecta_db->deleta_perfil($this, $this->id);     
     }

    function admin() {
        if (is_null($this->admin_master)) {
            return false;
        }
        
        return $this->admin_master;
    }

    function cadastrador() {
        if (is_null($this->cadastrador)) {
            return false;
        }
        
        return $this->cadastrador;
    }

    function sindico() {
        if (is_null($this->sindico)) {
            return false;
        }
        
        return $this->sindico;
    }

    function pega_id() {
        return $this->id;
    }

    function pega_icone() {
        if ($this->admin()) {
            $icone_user = "fa fa-user-gear";
        } elseif ($this->sindico()) { 
            $icone_user = "fa fa-user-tie";
        } elseif ($this->cadastrador()) {
            $icone_user = "fa fa-user-pen";
        }else {
            $icone_user = "fa fa-user";
        }
        return $icone_user;
    }

    function pega_cor() {
        return "w3-teal";
    }

    function pega_todos_ids() {
        global $conecta_db;
        
        return $conecta_db->pega_todos_perfil_id($this);
    }

    function pega_nome() {
        return $this->nome;
    }

    function pega_ascendentes() {
        return "";
     }

    function pega_links_autorizado() {
        return $this->links_autorizado;
    }

    function pega_ids_links_autorizado() {
        $links_perfil = new links_perfil;
        $ids_links_autorizado = [];
        foreach ($links_perfil as $chave => $valor) {
            if (in_array($links_perfil->pega_link($chave),$this->links_autorizado)) {
                $ids_links_autorizado[] = $chave;
            }
        }

        return $ids_links_autorizado;
    }

    function autorizado($user, $acao, $id_condominio = 0, $id_unidade = 0) {
        if ($this->admin()) { 
            return true; 
        } elseif ($this->cadastrador() || $this->sindico()) { 
            if ($acao != "consumo_unidade" && $acao != "consumo_condominio") {
                return $this->link_autorizado($acao);
            }
            return $this->autorizado_id_condominio($id_condominio);
        } elseif ($acao == "consumo_unidade") {
            return $this->autorizado_id_unidade($user, $id_unidade);
        }
        return false;
    }

    function pega_ids_condominio() {
        return $this->ids_condominio;
    }

    function autorizado_id_condominio($id_condominio) {
        return in_array($id_condominio, $this->ids_condominio);
    }

    function autorizado_id_unidade($user, $id_unidade) {
        return in_array($id_unidade, $user->pega_ids_unidade());
    }
    
    function link_autorizado($link) {
        return in_array($link, $this->links_autorizado);
    }

    function lista_select($perfil, $id_perfil) {
        global $conecta_db;
  
        $options_perfil = "";
        $perfis = $conecta_db->pega_todos_perfil_id_nome($this);
         
        foreach ($perfis as $chave => $valor) {
            $seleciona = "";
            //Não libera um Perfil que seja de mesmo nível, ou maior, que o do Usuário atual
            if (($valor['admin_master'] == 1 || 
                ($valor['sindico'] == 1 && !$perfil->admin()) ||
                ($valor['cadastrador'] == 1 && !($perfil->sindico() || $perfil->admin())))
                && $id_perfil != $valor['id']) {
                continue;
            } 
            if ($id_perfil == $valor['id']) {
                $seleciona = "selected";
            }
            $options_perfil .= "<option value=\"{$valor['id']}\" {$seleciona}>{$valor['nome']}</option>";
        }

        return $options_perfil;
    }

    function exibe_html($user, $perfil) {
        $checked_admin = "";

        if ($this->admin()) {
            $checked_admin = "checked";
        }
        
        $checked_cadastrador = "";
        if ($this->cadastrador()) {
            $checked_cadastrador = "checked";
        }

        $checked_sindico = "";
        if ($this->sindico()) {
            $checked_sindico = "checked";
        }

        $disable_admin = "disabled";
        $disable_cadastrador = "disabled";
        $desabilita_edicao = "disabled";
        if ($perfil->admin()) {
            $disable_cadastrador = "";
            $disable_sindico = "";
            $desabilita_edicao = "";
        }
        
        $condominio = new condominio();
        $links_perfil = new links_perfil();
        $html_checkbox_condominio = $condominio->lista_checkbox($perfil, $this->ids_condominio, $desabilita_edicao);
        $html_checkbox_links_autorizado = $links_perfil->lista_checkbox($perfil, $this->links_autorizado, $desabilita_edicao);

        $html = "         
        <div class=\"w3-row-padding w3-margin-bottom\">
            <form class=\"my-form w3-container\" id=\"form_dados\">
                <div class=\"form_cadastro\">
                    <input type=\"hidden\" id=\"id\" value=\"{$this->id}\">
                    <input type=\"hidden\" id=\"objeto\" value=\"perfil\">
                    <label for=\"nome\">Nome</label>: <input type=\"text\" class=\"w3-input\" name=\"nome\" id=\"nome\" value=\"{$this->nome}\"><br>
                    <label for=\"descricao\">Descrição</label>: <input type=\"text\" class=\"w3-input\" name=\"descricao\" id=\"descricao\" value=\"{$this->descricao}\"><br>
                    <input type=\"checkbox\" name=\"admin_master\" id=\"admin_master\" value=\"1\" {$checked_admin} disabled> - <label for=\"admin_master\">Admin</label><br>
                    <input type=\"checkbox\" name=\"sindico\" id=\"sindico\" value=\"1\" {$checked_sindico} {$disable_sindico}> - <label for=\"sindico\">Síndico</label><br>
                    <input type=\"checkbox\" name=\"cadastrador\" id=\"cadastrador\" value=\"1\" {$checked_cadastrador} {$disable_cadastrador}> - <label for=\"cadastrador\">Cadastrador</label><br>
                </div>
                <div class=\"w3-half\" >
                <p class=\"p_titulo\">Condomínios</p>
                    <div id=\"div_condominios\">
                    {$html_checkbox_condominio}
                    </div>
                </div>
                <div class=\"w3-half\" >
                <p class=\"p_titulo\">Links Autorizados</p>
                    <div id=\"div_links_autorizado\">
                    {$html_checkbox_links_autorizado}
                    </div>
                </div>
            </form>
        </div>";
  
        return $html;
    }
}
?>