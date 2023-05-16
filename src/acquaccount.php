<?php
namespace acquaccount;
use mysqli;
use DateTime;
use DatePeriod;
use DateInterval;
use DateTimeImmutable;
use Exception;

/************
 * Classe acquaccount
 * ----------------------
 * Controla o fluxo de execução do sistema
 * 
 */
class acquaccount {
    private $html_body = "";
    private $html_top_container = "";
    private $titulo_dashboard = "";
    private $icone_dashboard = "";
    private $link_voltar = "";

    function __construct() {
      $this->html_body = "";
      $this->html_top_container = "";
      $this->titulo_dashboard = "";
      $this->icone_dashboard = "";
      $this->link_voltar = "";
    }

    function processa_login(&$dados_session, $dados_post, &$user) {
        global $conecta_db;

        $pagina_default = new pagina_default();
        $cliente = new processa_login_google();
        $html_top_container = "";
        $html_body = "";
        
        if(!isset($dados_session['token']) && !isset($dados_post['credential'])){
            $this->html_top_container = $cliente->botao_google();
            $this->html_body = $pagina_default->exibe_pagina_inicial();
        } elseif (isset($dados_post['credential'])) {
          $dados_cliente = $cliente->valida_token($dados_post['credential']);
          if (!$dados_cliente) {
            $this->html_body = "<meta http-equiv=\"refresh\" content=\"10\">
            <h4>Não foi possível validar o Token gerado pelo Google! Por favor, tente novamente mais tarde!</h4>\n";
          } else {
            $dados_session['token'] = $dados_post['credential'];
            if (!$user->pega_user_por_email($dados_cliente['email'])) {
              $this->html_body = "<meta http-equiv=\"refresh\" content=\"10\">
              <div class=\"w3-center\"><h3>Parece que você ainda não está cadastrado no sistema!</h3>
              <p>Caso seja proprietário de alguma unidade sob gestão do sistema Acquaccount,<br>
              por favor entre em contato com seu Síndico ou com o Administrador do Sistema!</p></div>\n";
              $cliente->revokeToken($dados_session['token']);
        
              unset($dados_session['token']);
              unset($dados_session['dados_cliente']);
              session_destroy();
            } else {
              if (!$user->autenticado()) { $user->salva_user($dados_cliente); }
              $dados_session['dados_cliente'] = $dados_cliente;
              $dados_session['date_time_login'] = new DateTimeImmutable("now");
            }
          }
        } elseif (!isset($dados_session['dados_cliente'])) {
          $this->html_body = "<meta http-equiv=\"refresh\" content=\"10\">
          <h4>OCORREU UM ERRO DESCONHECIDO! POR FAVOR, TENTE NOVAMENTE MAIS TARDE!<h4>";
        }

        if (isset($dados_session['dados_cliente'])) {
            if (isset($dados_session['dados_cliente']['email'])) {
                $user->pega_user_por_email($dados_session['dados_cliente']['email']);
            }
        }
        
        return array(
            'html_top_container' => $this->html_top_container,
            'html_body' => $this->html_body 
            );
    }

    function processa_user($user, $perfil, $dados_get) {
        $pagina_default = new pagina_default();
        $dashboard = new dashboard();

        if (isset($dados_get['id'])) { 
            $user_temp = new user();
            $user_temp->pega_user_por_id($dados_get['id']);
            $this->link_voltar = $pagina_default->link_voltar();
  
            if ($user->pega_id() != $dados_get['id'] && !$perfil->admin() && !$perfil->link_autorizado('user')) {
              $dados_get['id'] = $user->pega_id();
              $user_temp = $user;
            }          
            
            if ($perfil->admin() || $perfil->link_autorizado('user')) {
              $this->link_voltar = $pagina_default->link_voltar($dados_get['acao']);
            }
            $this->titulo_dashboard = "Usuário";
            $this->icone_dashboard = "fa fa-user";
            $this->html_body = $user_temp->exibe_html($user, $perfil);          
          } else {
            if ($perfil->admin() || $perfil->link_autorizado('user')) {
              $this->titulo_dashboard = "Usuários";
              $this->icone_dashboard = "fa fa-users";
              $this->html_body = $dashboard->dashboard_user($user, $perfil);
            } else {
              $pagina_default->link_pagina_principal();
            }
          }

          return array('titulo_dashboard' => $this->titulo_dashboard, 
            'icone_dashboard' => $this->icone_dashboard, 
            'html_body' => $this->html_body,
            'link_voltar' => $this->link_voltar);
    }

    function processa_acao($user, $perfil, $dados_get) {
        $pagina_default = new pagina_default();
        $dashboard = new dashboard();
        
        $objeto_com_namespace = __NAMESPACE__ . '\\' .$dados_get['acao'];
        if (class_exists($objeto_com_namespace)) {
          $objeto = new $objeto_com_namespace();
        } else {
          $this->html_body = "<h4>Erro! Ação inválida!</h4>";
          return array('erro' => true, 'html_body' => $this->html_body);
        }
          
          $this->icone_dashboard = $objeto->pega_icone();
          if (isset($dados_get['id'])) {
            $pega_objeto_por_id = "pega_{$dados_get['acao']}_por_id";
            $objeto->$pega_objeto_por_id($dados_get['id']);
  
            $this->titulo_dashboard = ucfirst($dados_get['acao']);
            $this->link_voltar = $pagina_default->link_voltar($dados_get['acao']);
            $this->html_body = $objeto->exibe_html($user, $perfil);          
          } else {
            if ($perfil->admin()) {
              $this->titulo_dashboard = ucfirst($objeto->classe_plural());
              $this->html_body = $dashboard->dashboard_objeto($user, $perfil, $objeto);
            } else {
              $pagina_default->link_pagina_principal();
            }
          } 

          return array('titulo_dashboard' => $this->titulo_dashboard, 
            'icone_dashboard' => $this->icone_dashboard, 
            'html_body' => $this->html_body,
            'link_voltar' => $this->link_voltar);
    }

    function processa_consumos($user, $perfil, $dados_get, $consumo_objeto) {
        $pagina_default = new pagina_default();
        $dashboard = new dashboard();

        $this->titulo_dashboard = $consumo_objeto->classe_singular();
        $this->icone_dashboard =  $consumo_objeto->pega_icone($padrao = true); 
        
        if (isset($dados_get['mes_ano'])) {
          $mes = substr($dados_get['mes_ano'],0,2);
          $ano = substr($dados_get['mes_ano'],2);
        } elseif (isset($dados_get['mes']) && isset($dados_get['ano'])) {
          $mes = $dados_get['mes'];
          $ano = $dados_get['ano'];
        }else { 
          $interval = new DateTime("now");
          $mes = $interval->format('m');
          $ano = $interval->format('Y');
        }

        $id_objeto = 0;
        $id_unidade = 0;
        $id_condominio = 0;
        if (substr(strrchr(get_class($consumo_objeto), '\\'), 1) == "consumo_unidade") {
            if (isset($dados_get['id_unidade'])) { 
              $id_objeto = $dados_get['id_unidade']; 
              $unidade = new unidade();
              $id_unidade = $dados_get['id_unidade'];
              $unidade->pega_unidade_por_id($id_unidade);
              $id_condominio = $unidade->pega_id_condominio(); 
            }    
        } else {
            if (isset($dados_get['id_condominio'])) { 
              $id_objeto = $dados_get['id_condominio']; 
              $id_condominio = $dados_get['id_condominio']; }
        }

        if (isset($dados_get['id_unidade']) || isset($dados_get['id_condominio'])) {
          if ($perfil->cadastrador()) {
            if (!$perfil->autorizado($user, $dados_get['acao'], $id_condominio, $id_unidade)) { $pagina_default->link_pagina_principal(); }
          } elseif (!($perfil->admin() || $perfil->cadastrador()) 
            && !$perfil->autorizado($user, $dados_get['acao'], $id_condominio, $id_unidade)) 
          { $pagina_default->link_pagina_principal(); }

          $verifica_consumo_objeto = "verifica_{$dados_get['acao']}";
          $id_consumo_objeto = $consumo_objeto->$verifica_consumo_objeto($id_objeto, $mes, $ano);
          if (!isset($dados_get['id'])) {
            $pega_consumo_objeto = "pega_{$dados_get['acao']}_por_id";
            $consumo_objeto->$pega_consumo_objeto($id_consumo_objeto);
          } else {
            if ($dados_get['id'] != $id_consumo_objeto) {
              if ($id_consumo_objeto == null) {
                $id_consumo_objeto = 0;
              }
                $dados_get['id'] = $id_consumo_objeto;
                $_SESSION['id'] = $dados_get['id'];
            }
            $pega_consumo_objeto_por_id = "pega_{$dados_get['acao']}_por_id";
            $consumo_objeto->$pega_consumo_objeto_por_id($dados_get['id']);
          }
          $texto_link_voltar = $dados_get['acao'];
          if (isset($dados_get['mes_ano'])) {
            $texto_link_voltar .= "&mes_ano={$dados_get['mes_ano']}";
          }
          $texto_link_voltar .= "#{$id_condominio}_{$id_unidade}";

          $this->link_voltar = $pagina_default->link_voltar($texto_link_voltar);
          $this->html_body = $consumo_objeto->exibe_html($user, $perfil, $id_objeto, $mes, $ano);
        } else {
          if ($perfil->admin() || $perfil->cadastrador() || $perfil->link_autorizado($dados_get['acao'])) {
            $this->titulo_dashboard = $consumo_objeto->classe_plural();
            $this->html_body = $dashboard->dashboard_consumo($user, $perfil, $consumo_objeto, $dados_get);
          } else {
            $pagina_default->link_pagina_principal();
          }
        }
        
        return array('titulo_dashboard' => $this->titulo_dashboard, 
        'icone_dashboard' => $this->icone_dashboard, 
        'html_body' => $this->html_body,
        'link_voltar' => $this->link_voltar);
    }

    function processa_relatorios($user, $perfil, $dados_get, $relatorios) {
      $pagina_default = new pagina_default();
      $dashboard = new dashboard();

      $this->titulo_dashboard = $relatorios->classe_plural();
      $this->icone_dashboard =  $relatorios->pega_icone($padrao = true); 
      
      if (isset($dados_get['mes_ano'])) {
        $mes = substr($dados_get['mes_ano'],0,2);
        $ano = substr($dados_get['mes_ano'],2);
      } else { 
        $interval = new DateTime("now");
        $mes = $interval->format('m');
        $ano = $interval->format('Y');
      }

      if (!($perfil->admin() || $perfil->cadastrador() || $perfil->link_autorizado($dados_get['acao']))) {
        $pagina_default->link_pagina_principal();
      }
      
      $texto_link_voltar = $dados_get['acao'];
      if (isset($dados_get['mes_ano'])) {
        $texto_link_voltar .= "&mes_ano={$dados_get['mes_ano']}";
      }
      $this->link_voltar = $pagina_default->link_voltar($texto_link_voltar);
      $this->html_body = $dashboard->dashboard_relatorio($user, $perfil, $relatorios, $dados_get);

      return array('titulo_dashboard' => $this->titulo_dashboard, 
      'icone_dashboard' => $this->icone_dashboard, 
      'html_body' => $this->html_body,
      'link_voltar' => $this->link_voltar);
    }

    function processa_contato($user, $perfil, $dados_get) {
      $contato = new contato();
      $pagina_default = new pagina_default();
      $dashboard = new dashboard();
      
      $this->titulo_dashboard = $contato->classe_singular();
      $this->icone_dashboard = $contato->pega_icone();
      $this->link_voltar = $pagina_default->link_voltar();
      $this->html_body = $contato->exibe_html($user, $perfil);
      
      return array('titulo_dashboard' => $this->titulo_dashboard, 
      'icone_dashboard' => $this->icone_dashboard, 
      'html_body' => $this->html_body,
      'link_voltar' => $this->link_voltar);     
    }
}
?>