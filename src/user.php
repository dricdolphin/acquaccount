<?php
namespace acquaccount;
use mysqli;
use DateTime;
use DatePeriod;
use DateInterval;
use DateTimeImmutable;
use Exception;

/**************************
 * Classe user
 * ---------------
 * Objeto que contém os dados do usuário
 * 
 */

class user {
    private $id = 0;
    private $id_perfil;
    private $given_name;
    private $family_name;
    private $email;
    private $CPF;
    private $picture;
    private $ids_condominio = [];
    private $ids_unidade = [];    
    private $data_criado;
    private $data_modificado;

    function __construct() {
        $this->id = 0;
        $this->id_perfil = null;
        $this->given_name = null;
        $this->family_name = null;
        $this->email = null;
        $this->CPF = null;
        $this->picture = null;
        $this->ids_condominio = [];
        $this->ids_unidade = [];    
        $this->data_criado = null;
        $this->data_modificado = null;    
    }

    function classe_plural() {
        return "usuários";
    }
  
    function classe_singular() {
        return "usuário";
    }

    function pega_adjetivo_novo() {
        return "Novo";
    }

    function carrega_dados_usuario($dados_usuario) {
        $this->id = $dados_usuario['id'];
        $this->id_perfil = $dados_usuario['id_perfil'];
        $this->given_name = $dados_usuario['given_name'];
        $this->family_name = $dados_usuario['family_name'];
        $this->email = $dados_usuario['email'];
        $this->CPF = $dados_usuario['CPF'];
        $this->picture = $dados_usuario['picture'];
        $this->data_criado = $dados_usuario['data_criado'];
        $this->data_modificado = $dados_usuario['data_modificado'];

        $this->ids_condominio = unserialize($dados_usuario['ids_condominio']);
        $this->ids_unidade = unserialize($dados_usuario['ids_unidade']);
    }

    function pega_user_por_id($id_user) {
        global $conecta_db;
        
        $dados_usuario = $conecta_db->pega_user_por_id($this, $id_user);
        if (is_null($dados_usuario)) {
            $this->__construct();
            return false;
        }

        $this->carrega_dados_usuario($dados_usuario);
        return true;
    }

    function pega_user_por_email($email_user) {
        global $conecta_db;

        $dados_usuario = $conecta_db->pega_user_por_email($this, $email_user);
        if (is_null($dados_usuario)) {
            $this->__construct();
            return false;
        }

        $this->carrega_dados_usuario($dados_usuario);
        return true;    
    }

    function autenticado() {
        global $conecta_db;

        if($this->given_name == "") {
            return false;
        }

        return true;
    }

    function logado() {
        if (isset($_SESSION['dados_cliente'])) {
            return true;
        }

        return false;
    }

    function data_login_valido() {
        $agora = new DateTime("now");
        $login_oito_horas = $_SESSION['date_time_login']->add(new DateInterval('PT8H'));
        if ($login_oito_horas > $agora) {
            return true;
        }

        return false;
    }

    function salva_user($dados) {
        global $conecta_db;
      
        return  $dados_usuario = $conecta_db->salva_dados_user($this, $dados, $this->id);     
    }

    function deleta_user() {
        global $conecta_db;
        
        if ($this->id == 0) {
           return false;
        }

        return $dados = $conecta_db->deleta_user($this, $this->id);     
     }

    function pega_todos_user_id() {
        global $conecta_db;

        return $conecta_db->pega_todos_user_id($this);
    }
    
    function pega_perfil() {
        global $conecta_db;
        
        if (!isset($this->id_perfil) || is_null($this->id_perfil)) {
            return false;
        }

        return $conecta_db->pega_nome_perfil($this->id_perfil);
    }

    function pega_id_perfil() {
        return $this->id_perfil;
    }

    function pega_imagem() {
        return $this->picture;
    }

    function pega_nome() {
        return $this->given_name;
    }

    function pega_sobrenome() {
        return $this->family_name;
    }

    function pega_email() {
        return $this->email;
    }

    function pega_nome_completo() {
        if (!$this->autenticado()) { return $this->email; }
        return $this->given_name . " " . $this->family_name;
    }

    function pega_id() {
        return $this->id;
    }

    function pega_ids_unidade() {
        return $this->ids_unidade;
    }

    function pega_cor() {
        $cor_usuario = "w3-teal";
        if (!$this->autenticado()) {
            $cor_usuario = "w3-orange";
        }

        return $cor_usuario;
    }

    function pega_icone_perfil() {
        $perfil = new perfil();
        $perfil->pega_perfil_por_id($this->id_perfil);
        
        $icone = [];
        $icone['icone'] = $perfil->pega_icone();
        $icone['titulo_icone'] = $perfil->pega_nome();
        if (!$this->autenticado()) {
            $icone['icone'] = "fa fa-user-tag";
            $icone['titulo_icone'] = "Usuário não autenticado";
        }        
        
        return $icone;      
    }

    function pega_icone() {
        return "fa fa-user";
    }

    function pega_ascendentes() {
        return "";
     }

    function exibe_html($user, $perfil) {
        global $conecta_db;
  
        $condominio = new condominio();
        $bloco = new bloco();
        $unidade = new unidade();
           
        $desabilita_edicao = "disabled";
        $desabilita_edicao_email = "disabled";
        $desabilita_edicao_perfil = "disabled";
        if (($perfil->admin() || $perfil->link_autorizado('user')) && $this->id != $user->pega_id()) {
            if ($perfil->admin()) {
                $desabilita_edicao_perfil = "";
            }
            $desabilita_edicao = "";
            $desabilita_edicao_email = "";
        }
        
        if ($this->autenticado()) {
            $desabilita_edicao_email = "disabled";
        }

        $options_perfil = $perfil->lista_select($perfil, $this->pega_id_perfil());
        $html_checkbox_condominio = $condominio->lista_checkbox($perfil, $this->ids_condominio, $desabilita_edicao);

        $html_checkbox_unidade = "";
        foreach ($this->ids_condominio as $chave_cond => $id_condominio) {
            if ($perfil->sindico() && $perfil->link_autorizado('user') 
                && !$perfil->autorizado_id_condominio($id_condominio) && !in_array($id_condominio, $this->ids_condominio)) {
                continue;
            }
            $lista_checkbox_unidades = $unidade->pega_todos_ids($id_condominio);
            $condominio->pega_condominio_por_id($id_condominio);
            $html_checkbox_unidade .= "<div id=\"div_unidades_condominio_{$id_condominio}\">
            <p><b>".$condominio->pega_nome()."</b></p>
            ";
            foreach ($lista_checkbox_unidades as $chave => $valor) {
                $checkbox_unidade_checked = "";
                $desabilita_edicao = "";
                if (!($perfil->admin() || $perfil->autorizado_id_condominio($id_condominio))) {
                    $desabilita_edicao = "disabled";
                }
                
                $unidade->pega_unidade_por_id($valor['id']);
                $bloco->pega_bloco_por_id($unidade->pega_id_bloco());
                $nome_unidade = $unidade->pega_nome() . " (Bloco '" . $bloco->pega_nome() ."')";
                if (in_array($valor['id'], $this->ids_unidade)) {
                    $checkbox_unidade_checked = "checked";
                }
                $html_checkbox_unidade .= "<input type=\"checkbox\" name=\"ids_unidade\" id=\"ids_unidade[]\" value=\"{$valor['id']}\" {$checkbox_unidade_checked} {$desabilita_edicao}> - <label for=\"ids_unidade\">{$nome_unidade}</label><br>";
            }
            $html_checkbox_unidade .= "</div>";
        }
        
        $html = "         
        <div class=\"w3-row-padding w3-margin-bottom\">
            <form class=\"my-form w3-container\" id=\"form_dados\">
                <div class=\"form_cadastro\">
                    <input type=\"hidden\" id=\"id\" value=\"{$this->id}\">
                    <input type=\"hidden\" id=\"objeto\" value=\"user\">
                    <label for=\"id_perfil\">Perfil</label>: <select class=\"w3-input\" name=\"id_perfil\" id=\"id_perfil\" {$desabilita_edicao_perfil}> 
                    {$options_perfil}
                    </select><br>
                    <label for=\"given_name\">Nome</label>: <input type=\"text\" class=\"w3-input\" name=\"given_name\" id=\"given_name\" value=\"{$this->given_name}\" disabled><br>
                    <label for=\"family_name\">Sobrenome</label>: <input type=\"text\" class=\"w3-input\" name=\"family_name\" id=\"family_name\" value=\"{$this->family_name}\" disabled><br>
                    <label for=\"email\">e-mail</label>: <input type=\"text\" class=\"w3-input\" name=\"email\" id=\"email\" value=\"{$this->email}\" {$desabilita_edicao_email}><br>
                    <label for=\"CPF\">CPF</label>: <input type=\"text\" pattern=\"[0-9]*\" maxlength=\"11\" class=\"w3-input\" name=\"CPF\" id=\"CPF\" value=\"{$this->CPF}\" {$desabilita_edicao}><br>
                    <div class=\"w3-row-padding\">
                        <div class=\"w3-half\" >
                        <p class=\"p_titulo\">Condomínios</p>
                            <div id=\"div_condominios\">
                            {$html_checkbox_condominio}
                            </div>
                        </div>
                        <div class=\"w3-half\">
                        <p class=\"p_titulo\">Unidades</p>
                            <div id=\"div_unidades\">
                            {$html_checkbox_unidade}
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>";
        
        return $html;
    }
}
?>