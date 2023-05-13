<?php
namespace acquaccount;
use mysqli;
use DateTime;
use DatePeriod;
use DateInterval;
use DateTimeImmutable;
use Exception;

/**************************
 * Classe dashboard
 * ------------------
 * 
 * Exibe o dashboard e os dados que o usuário pode observar
 * 
 */

 class dashboard {

    function __construct() {

    }

    private function dashboard_lista_objeto($objeto, $lista_objeto, $numero_quarters = 1) {
        global $conecta_db;
        
        $html = "";
        $classe_objeto = substr(strrchr(get_class($objeto), '\\'), 1);
        foreach ($lista_objeto as $chave => $valor) {
            $numero_quarters++;
            $metodo_pega_objeto = "pega_{$classe_objeto}_por_id";
            $objeto->$metodo_pega_objeto($valor['id']);
            $icone = $objeto->pega_icone();
            $cor = $objeto->pega_cor();
            $ascendentes = $objeto->pega_ascendentes();

            $html .= $this->dashboard_quarter("{$objeto->pega_nome()}",$ascendentes,$icone, $cor,
            "{$classe_objeto}&id={$objeto->pega_id()}", $icone_deletar = true);
            if ($numero_quarters == 4) {
                $html .= "<div class=\"w3-clear div_para_imprimir\"></div>";
                $numero_quarters = 0;
            }
        }
         
        return $html;
    }

    private function dashboard_novo_objeto($objeto) {
        global $conecta_db;

        $nome_objeto = substr(strrchr(get_class($objeto), '\\'), 1);
        $nome_objeto_maiuscula  = ucfirst($objeto->classe_singular());
        $novo = $objeto->pega_adjetivo_novo();
 
        $html = $this->dashboard_quarter("{$novo} {$nome_objeto_maiuscula}","","fa fa-plus","w3-green","$nome_objeto&id=0");

        return $html;
    } 

    private function dashboard_quarter($nome, $qtd, $icone, $w3_cor, $link='', $icone_deletar=false) {
        if ($icone_deletar) {
            $icone_deletar = "<a style='float: right;' href=\"#\" title=\"Deletar\" onclick=\"deleta_objeto('{$link}',event,this)\"><i class=\"fas fa-trash-can w3-large\"> &nbsp; </i></a>";
        } else {
            $icone_deletar = "";
        }

        if ($link != '') {
            $estilo = "";
            if (str_contains($nome, "@")) { $estilo = " line-break: anywhere;"; }
            $link = "<a style=\"text-decoration: none;{$estilo}\" href=\"?acao={$link}\" title=\"{$nome}\" >{$nome}</a>";
        } else {
            $link = $nome;
        }
        
        if (is_numeric($qtd)) {
            $qtd = "<div class=\"w3-right div_para_imprimir\"><h3>{$qtd}</h3></div>";
        } else {
            $qtd = "<div class=\"w3-clear no-margin-padding div_para_imprimir\"> &nbsp; </div><div class=\"w3-center no-margin-padding div_para_imprimir\"><p class=\"w3-small no-margin-padding\">{$qtd}</p></div>";
        }
        
        $titulo_icone = "";
        if (is_array($icone)) {
            $titulo_icone = "title=\"{$icone['titulo_icone']}\"";
            $icone = $icone['icone'];
        }

        $html = "          
        <div class=\"w3-quarter w3-padding-small div_para_imprimir\">
            <div class=\"w3-container {$w3_cor} w3-padding-16 div_para_imprimir\">
                {$icone_deletar}
                <div class=\"w3-left div_para_imprimir\"><i class=\"{$icone} w3-xxlarge\" {$titulo_icone}> &nbsp; </i></div>
                {$qtd}
                <div class=\"w3-clear div_para_imprimir\"></div>
                    <h4>{$link}</h4>
                </div>
            </div>";

        return $html;
    }

    private function select_meses_dashboard($user, $perfil, $objeto, $dados_get) {
        $html = "";
        $link = substr(strrchr(get_class($objeto), '\\'), 1);
        $objeto = __NAMESPACE__ . '\\' . $link;
        $objeto = new $objeto();
        $mes_ano = "";
        
        if (isset($dados_get['mes_ano'])) {
            $mes = substr($dados_get['mes_ano'], 0, 2);
            $ano = substr($dados_get['mes_ano'], 2);
            $mes_ano = "&mes_ano={$dados_get['mes_ano']}";
        } else {
            $interval = new DateTime("now");
            $mes = $interval->format('m');
            $ano = $interval->format('Y');
        }
        $datas_option = $objeto->select_meses($mes, $ano);
        
        if (!str_contains($datas_option,$mes.$ano)) {
            $interval = new DateTime("now");
            $mes = $interval->format('m');
            $ano = $interval->format('Y');
            $mes_ano = "";
        }
        
        $html = "<div class=\"w3-clear w3-margin div_para_imprimir\">            
        <form id=\"form_dados\">
        <input type=\"hidden\" id=\"mes\" value=\"{$mes}\">
        <input type=\"hidden\" id=\"ano\" value=\"{$ano}\">
        <label for=\"mes_ano\">Data das Medições: <select name=\"mes_ano\" id=\"mes_ano\"> 
        {$datas_option}      
        </select>
        </form>
        </div>";

        return array('html' => $html, 'mes_ano' => $mes_ano, 'mes' => $mes, 'ano' => $ano);
    }
    
    private function html_tabela_consumo_unidades($dados_consumo_unidades, $taxa_minima, $taxa_m3, $id_condominio) {
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
            $valor_consumo_m3_arredondado = number_format(round($valor_consumo_m3, 2), 2, ',', '');
            $html .= "<tr><td>{$valor['numero_unidade']}</td><td>{$valor['valor_m3']}</td><td>{$taxa_minima}</td><td>{$valor_consumo_m3_arredondado}</td><td>{$valor_total}</td></tr>";
        }

        $html .= "</tbody>
        </table>";
        return $html;
    }
    
    function dashboard_admin($user, $perfil) {
        global $conecta_db;
       
        $links_perfil = new links_perfil();
        $html = "";
        $numero_quarters = 0;
        foreach ($links_perfil as $chave => $valor) {
            if (!$perfil->admin() && $perfil->sindico() && !$perfil->link_autorizado($links_perfil->pega_link($chave))) {
                continue;
            }
            $numero_quarters++;
            $nome = $links_perfil->pega_nome($chave);
            $cor = $links_perfil->pega_cor($chave);
            $icone = $links_perfil->pega_icone($chave);
            $link = $links_perfil->pega_link($chave);
            $objeto = __NAMESPACE__ . '\\' . $link;
            if ($links_perfil->pega_dashboard($chave)) {
                $qtd = $conecta_db->pega_qtd_objeto(new $objeto);
                $html .= $this->dashboard_quarter($nome, $qtd, $icone, $cor, $link);
                if ($numero_quarters == 4) {
                    $html .= "<div class=\"w3-clear\"></div>";
                    $numero_quarters = 0;
                }
            }
        }
        
        return $html;
    }

    function dashboard_cadastrador($user, $perfil) {
        global $conecta_db;
       
        $html = "";
        $numero_quarters = 0;
        $interval = new DateTime("now");
        $mes = $interval->format('m');
        $ano = $interval->format('Y');

        //Serão 2 quarters, um para o Consumo do Condomíno e outro para o Consumo das Unidades
        $html .= "<div>Data das Medições: {$mes}/{$ano}</div><br>
        <div name=\"div_graficos\" id=\"consumo_condominio\" class=\"w3-half\"></div>
        <div name=\"div_graficos\" id=\"consumo_unidade\" class=\"w3-half\"></div>";

        return $html;
    }

    function dashboard_sindico($user, $perfil) {
        global $conecta_db;
       
        $html = "";
        $numero_quarters = 0;
        $interval = new DateTime("now");
        $mes = $interval->format('m');
        $ano = $interval->format('Y');
        $condominio = new condominio();
        $unidade = new unidade();
        $consumo_unidade = new consumo_unidade();

        $lista_condominios = $perfil->pega_ids_condominio();
        if ($perfil->admin()) {
            $lista_condominios = $condominio->pega_todos_ids();
        } 
        
        foreach ($lista_condominios as $chave => $valor) {
            $id_condominio = $valor;
            if (isset($valor['id'])) {
                $id_condominio = $valor['id'];
            }
            $condominio->pega_condominio_por_id($id_condominio);
            $nome_condominio = $condominio->pega_nome();
            $qtd_unidades_condominio = $condominio->pega_numero_unidades_condominio($id_condominio);
            
            $qtd_status_chart = $consumo_unidade->pega_dados_chart_unidades($user, $perfil, $id_condominio);
            foreach ($qtd_status_chart as $chave => $valor) {
                $qtd_status[$valor['nome']] = $valor['valor'];
            }

            $html .= "
            <div class=\"w3-container div_para_imprimir\">
            <div class=\"div_para_imprimir\"><h5><b>{$nome_condominio}</b></h4></div>
            <div class=\"div_para_imprimir\">{$mes}/{$ano}</div>
            <div class=\"w3-clear div_para_imprimir\">&nbsp;</div>";
            
            $html .= $quarter_aguardando_leitura = $this->dashboard_quarter($nome = "Aguardando Leitura", 
                $qtd_status['Aguardando Leitura'], $icone = "fa fa-droplet-slash",$cor = "w3-deep-orange");
            $html .= $quarter_aguardando_validacao = $this->dashboard_quarter($nome = "Aguardando Validação", 
                $qtd_status['Aguardando Validação'], $icone = "fa fa-hand-holding-droplet",$cor = "w3-khaki");
            $html .= $quarter_validado = $this->dashboard_quarter($nome = "Consumo Validado", 
                $qtd_status['Consumo Validado'], $icone = "fa fa-faucet-drip",$cor = "w3-green");
            $html .= "<div class=\"w3-clear div_para_imprimir\">&nbsp;</div>
            </div>";
        }
        
        return $html;
    }

    function dashboard_usuario($user, $perfil) {
        global $conecta_db;
       
        $html = "";
        $numero_quarters = 0;
        $interval = new DateTime("now");
        $interval = $interval->sub(new DateInterval('P1Y'));

        $ids_unidade = $user->pega_ids_unidade();
        $unidade = new unidade();

        foreach ($ids_unidade as $chave => $id_unidade) {
            $html_links_meses_consumo = "";
            $unidade->pega_unidade_por_id($id_unidade);
            $numero_unidade = $unidade->pega_nome();
            $nome_unidade = $unidade->pega_ascendentes($inline = true) ." - ". $unidade->pega_nome();
            $nome_unidade = str_replace("<b>","",$nome_unidade);
            $nome_unidade = str_replace("</b>","",$nome_unidade);
            $unidade->pega_unidade_por_id($id_unidade);
            $consumo_unidade = new consumo_unidade();
            $dados_consumo_unidade = $consumo_unidade->pega_consumos_unidade($perfil, $user, $id_unidade);

            $medicao = [];
            $calculo_consumo = 0;
            foreach ($dados_consumo_unidade as $chave => $valor) {
                if (count($medicao) > 0) {
                    $ultima_medicao = count($medicao) - 1;
                    $calculo_consumo = $valor['medicao'] - $medicao[$ultima_medicao];
                }
                $medicao[] = $valor['medicao'];
                if ($valor['id'] == null || $valor['id'] == 0) { continue; }
                $html_links_meses_consumo .= "<div class=\"w3-tag w3-padding w3-indigo w3-margin\">
                    <div class=\"w3-left div_para_imprimir\"><i class=\"fas fa-file-invoice-dollar w3-xlarge\"> &nbsp; </i></div><br>
                    <div class=\"div_para_imprimir\"><p>{$calculo_consumo} m³</p>
                    <a href=\"?acao=consumo_unidade&id_unidade={$id_unidade}&mes={$valor['mes']}&ano={$valor['ano']}&id={$valor['id']}\">{$valor['mes']}/{$valor['ano']}</a></div>
                </div>";
            }
            
            $html .= "<div class=\w3-container div_para_imprimir\">
            <h3>{$nome_unidade}</h3><br>
            {$html_links_meses_consumo}
            </div>
            <div class=\"w3-clear div_para_imprimir\"> &nbsp; </div>";
            
            $html .= "<div name=\"div_graficos div_para_imprimir\" id=\"consumo_unidade_{$id_unidade}\" class=\"w3-container\"></div>";
            $html .= "<div class=\"w3-clear div_para_imprimir\"> &nbsp; </div>";
        }

        return $html;
    }

    function dashboard_user($user, $perfil) {
        global $conecta_db;

        $html = "";
        if ($perfil->admin() || $perfil->link_autorizado('user')) {
            $user_edita = new user();
            
            $html = $this->dashboard_novo_objeto($user_edita);
            
            $todos_user = $conecta_db->pega_todos_user_id($user_edita);
            $numero_quarters = 1;
            foreach ($todos_user as $chave => $valor) {
                $numero_quarters++;
                $user_edita->pega_user_por_id($valor['id']);
                $deleta_user = true;
                if ($user_edita->autenticado() || $user_edita->pega_id() == $user->pega_id()) {
                    $deleta_user = false;
                } 
                $user_nome = $user_edita->pega_nome_completo();
                $html .= $this->dashboard_quarter("{$user_nome}","",$user_edita->pega_icone_perfil(),
                $user_edita->pega_cor(),"user&id={$user_edita->pega_id()}", $deleta_user);
                
                if ($numero_quarters == 4) {
                    $html .= "<div class=\"w3-clear div_para_imprimir\"></div>";
                    $numero_quarters = 0;
                }
            }
        }

        return $html;
    }

    function dashboard_objeto($user, $perfil, $objeto, $novo_objeto = true) {
        global $conecta_db;
        
        $html = "";
        $link = substr(strrchr(get_class($objeto), '\\'), 1);
        if ($perfil->admin() || ($perfil->cadastrador())) {
            $lista_objetos = $objeto->pega_todos_ids();
            
            $numero_quarters = 0;
            if ($novo_objeto) { $html = $this->dashboard_novo_objeto($objeto); $numero_quarters = 1;}   
            $html .= $this->dashboard_lista_objeto($objeto, $lista_objetos, $numero_quarters);
        }        

        return $html;
    }

    function dashboard_consumo($user, $perfil, $objeto, $dados_get) {
        global $conecta_db;
        
        $html = "";
        $link = substr(strrchr(get_class($objeto), '\\'), 1);
        $objeto = __NAMESPACE__ . '\\' . $link;
        $objeto = new $objeto();
        if ($perfil->admin() || $perfil->cadastrador()) {
            $dados_select_meses = $this->select_meses_dashboard($user, $perfil, $objeto, $dados_get);
            $html = $dados_select_meses['html'];
            $consumo_por_mes = "pega_{$link}_por_mes";
            $lista_objeto = $objeto->$consumo_por_mes($perfil, $dados_select_meses['mes'], $dados_select_meses['ano']);
            $numero_quarters = 0;

            foreach ($lista_objeto as $chave => $valor) {
                $numero_quarters++;
                $consumo_por_id = "pega_{$link}_por_id";
                $objeto->$consumo_por_id ($valor['id']);
                $id_objeto="&id={$valor['id']}";
                
                if (isset($valor['id_unidade'])) {
                    $objeto_consumo = new unidade();
                    $objeto_consumo->pega_unidade_por_id($valor['id_unidade']);
                    $id_objeto_consumo = "id_unidade";
                    $valor_id_objeto_consumo = $valor['id_unidade'];
                } elseif (isset($valor['id_condominio'])) {
                    $objeto_consumo = new condominio();
                    $objeto_consumo->pega_condominio_por_id($valor['id_condominio']);
                    $id_objeto_consumo = "id_condominio";
                    $valor_id_objeto_consumo = $valor['id_condominio'];
                }
                
                $nome = $objeto_consumo->pega_nome();
                $ascendentes = $objeto_consumo->pega_ascendentes();
                $icone = $objeto->pega_icone();
                $cor = $objeto->pega_cor();

                $html .= $this->dashboard_quarter($nome,$ascendentes, $icone, $cor,
                "{$link}&{$id_objeto_consumo}={$valor_id_objeto_consumo}{$dados_select_meses['mes_ano']}{$id_objeto}", $icone_deletar = false);
                if ($numero_quarters == 4) {
                    $html .= "<div class=\"w3-clear div_para_imprimir\"></div>";
                    $numero_quarters = 0;
                }
            }
        } 

        return $html;
    }

    function dashboard_relatorio($user, $perfil, $objeto, $dados_get) {
        global $conecta_db;
        
        $condominio = new condominio();
        $unidade = new unidade();
        $consumo_condominio = new consumo_condominio();
        $consumo_unidade = new consumo_unidade();
        $dados_select_meses = $this->select_meses_dashboard($user, $perfil, $consumo_condominio, $dados_get);
        $html = $dados_select_meses['html'];
        
        $ids_condominio = [];
        if ($perfil->admin()) {
            $ids_condominio = $condominio->pega_todos_ids();
        } else {
            $ids_condominio = $perfil->pega_ids_condominio();
        }

        foreach ($ids_condominio as $chave => $id_condominio) {
            if ($perfil->admin()) {
                $id_condominio = $id_condominio['id'];
            }
            $condominio->pega_condominio_por_id($id_condominio);
            $nome_condominio = $condominio->pega_nome();
            $numero_unidades = (int) $condominio->pega_numero_unidades_condominio();
            $id_consumo_condominio = $consumo_condominio->verifica_consumo_condominio($id_condominio, 
                $dados_select_meses['mes'], $dados_select_meses['ano']);
            $consumo_condominio->pega_consumo_condominio_por_id($id_consumo_condominio);
            $consumo_minimo = $consumo_condominio->pega_valor_minimo_reais();
            $consumo_total = $consumo_condominio->pega_valor_reais();
            
            //O Consumo do Condomínio em m³ é a soma dos consumo de TODAS as unidades
            //$consumo_condominio_m3 = (float) $consumo_condominio->pega_valor_m3();
            $dados_consumo_unidades_mes_atual = $consumo_unidade->pega_consumos_unidades_condominio_mes_ano($user, $perfil, $id_condominio, 
                $dados_select_meses['mes'], $dados_select_meses['ano']);
           
            $interval = new DateTime("{$dados_select_meses['ano']}-{$dados_select_meses['mes']}-01");
            $interval = $interval->sub(new DateInterval('P1M'));
            $mes_anterior = $interval->format('m');
            $ano_anterior = $interval->format('Y');

            $dados_consumo_unidades_mes_anterior = $consumo_unidade->pega_consumos_unidades_condominio_mes_ano($user, $perfil, $id_condominio, 
                $mes_anterior, $ano_anterior);
            
            $dados_consumo_unidades = [];
            foreach ($dados_consumo_unidades_mes_atual as $chave => $valor) {
                $dados_consumo_unidades[$chave]['valor_m3'] = $valor['valor_m3'] - $dados_consumo_unidades_mes_anterior[$chave]['valor_m3'];
                if ($dados_consumo_unidades[$chave]['valor_m3'] < 0) {
                    $dados_consumo_unidades[$chave]['valor_m3'] = 0;
                }
                $dados_consumo_unidades[$chave]['numero_unidade'] = $valor['numero_unidade'];
            }
            
            $consumo_condominio_m3 = array_sum(array_column($dados_consumo_unidades, 'valor_m3'));
            $taxa_minima = number_format(round($consumo_minimo/$numero_unidades, 2), 2, ',', '');
            if ($consumo_condominio_m3 == 0) {
                $taxa_m3_completa = "0,00";
                $taxa_m3 = "0,00";
            } else {
                $taxa_m3_completa = ($consumo_total - $consumo_minimo)/$consumo_condominio_m3;
                $taxa_m3 = number_format(round(($consumo_total - $consumo_minimo)/$consumo_condominio_m3, 2), 2, ',', '');
            }
            
            $html_tabela_consumo_unidades = $this->html_tabela_consumo_unidades($dados_consumo_unidades, $taxa_minima, $taxa_m3_completa, $id_condominio);
            $html .= "
            <div class=\"w3-container\" id=\"div_tabela_consumo_condominio_{$id_condominio}\">
                <h3>{$nome_condominio}</h3>";
            
            $html .= "
                <div name=\"div_graficos div_para_imprimir\" id=\"consumo_condominio_{$id_condominio}\" class=\"w3-container div_para_imprimir\"></div>
                <div class=\"w3-clear div_para_imprimir\"> &nbsp; </div>
                <div class=\"w3-container w3-center div_para_imprimir\">
                    <div class=\"w3-tag w3-indigo div_para_imprimir\">Unidades: {$numero_unidades}</div>
                    <div class=\"w3-tag w3-blue div_para_imprimir\">Consumo das Unidades: {$consumo_condominio_m3}m³</div><br>
                    <div class=\"w3-tag w3-green div_para_imprimir\">Taxa Mínima: R$ {$taxa_minima} </div>
                    <div class=\"w3-tag w3-teal div_para_imprimir\">Taxa por m³: R$ {$taxa_m3}</div>
                </div>
                <div class=\"w3-clear div_para_imprimir\"> &nbsp; </div>
                <div class=\"w3-container div_para_imprimir\">{$html_tabela_consumo_unidades}</div>
            </div>";            
        }
        //$lista_consumo_condominio = $consumo_condominio->pega_consumo_condominio_por_mes($perfil, $dados_select_meses['mes'], $dados_select_meses['ano']);

        return $html;
    }

    function exibe_html($html_dashboard, $titulo, $icone, $link_voltar = '') {
        $html = "
        <!-- Header -->
        <header class=\"w3-container\" style=\"padding-top:22px\">
          <h5><b><i class=\"{$icone}\"></i> {$titulo}</b>{$link_voltar}</h5>
        </header>
        <div class=\"hidden w3-green\" style=\"text-align: center; position: sticky; top: 72px;\" id=\"caixa_dados_salvos\">&nbsp;</div>
        <div class=\"w3-row-padding w3-margin-bottom div_para_imprimir\">
            {$html_dashboard}
        </div>
        ";
        
        $html .= "<div class=\"w3-clear div_para_imprimir\"></div>";
        return $html;
    }
 }
?>