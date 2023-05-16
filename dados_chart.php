<?php
namespace acquaccount;
use mysqli;
use DateTime;
use DatePeriod;
use DateInterval;
use DateTimeImmutable;
use Exception;

/**************
 * Classe dados_chart
 * -----------------
 * 
 * Pega os dados para alimentar os Charts dos Dashboards
 * 
 */
//Chama os arquivos principais do programa
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';
require_once 'autoload.php';

class dados_chart {
    private $dados = [];
    
    function __construct() {

    }

    function pega_dados($user, $perfil, $dados_post) {
        global $conecta_db;
        
        $objeto = __NAMESPACE__ . '\\' . $dados_post['objeto'];
        $objeto = new $objeto();
        $rows_html = $objeto->pega_dados_chart($user, $perfil);
        $this->dados['dados_chart'] = "        
        {
            \"cols\": [{\"id\": \"leituras\", \"label\": \"Leituras\", \"type\": \"string\"},
                {\"id\": \"total_leituras\", \"label\": \"Total de Leituras\", \"type\": \"number\"}
            ],
            \"rows\": [
                {$rows_html}
            ]
        }";

        $colors_html = $objeto->pega_cores_chart();
        $nome_plural = $objeto->classe_plural();
        $this->dados['options_chart'] = "
        {
            \"title\": \"{$nome_plural}\",
            \"titleTextStyle\": {\"color\": \"#3f51b5\", \"fontName\": \"Raleway\", \"fontSize\": 16, \"bold\": true},
            \"pieSliceText\": \"value\",
            \"sliceVisibilityThreshold\": 0,
            \"height\": 500,
            \"backgroundColor\": {\"fill\": \"#FFF\"},
            \"chartArea\":{\"width\":\"85%\", \"height\":\"70%\"},
            \"vAxis\": {\"titleTextStyle\": {\"fontName\": \"Raleway\", \"fontSize\": 10, \"italic\":\"false\"}},
            \"pieHole\": 0.4,
            \"pieSliceTextStyle\": {\"color\": \"#3A3A3A\", \"fontName\": \"Raleway\"},
            {$colors_html}
            \"legend\": {\"position\": \"bottom\", \"maxLines\": 3,
                \"textStyle\": {\"fontName\": \"Raleway\", \"fontSize\": 10}
            },
            \"enableInteractivity\": false
        }";

        return $this->dados;
   }

   function pega_dados_usuario($user, $perfil, $dados_post) {
        global $conecta_db;
        
        $objeto = __NAMESPACE__ . '\\' . $dados_post['objeto'];
        $objeto = new $objeto();
        $rows_html = $objeto->pega_html_consumos_unidade($user, $perfil, $dados_post['id_unidade']);
        $this->dados['dados_chart'] = "        
        {
            \"cols\": [{\"id\": \"mes_ano\", \"label\": \"Data da Leitura\", \"type\": \"string\"},
                {\"id\": \"volume\", \"label\": \"Medição (m³)\", \"type\": \"number\"},
                {\"id\": \"consumo\", \"label\": \"Consumo (m³)\", \"type\": \"number\"}
            ],
            \"rows\": [
                {$rows_html}
            ]
        }";

        $unidade = new unidade();
        $unidade->pega_unidade_por_id($dados_post['id_unidade']);
        $nome_unidade = $unidade->pega_ascendentes($inline = true) ." - ". $unidade->pega_nome();
        $nome_unidade = str_replace("<b>","",$nome_unidade);
        $nome_unidade = str_replace("</b>","",$nome_unidade);
        $this->dados['options_chart'] = "
        {
            \"title\": \"{$nome_unidade}\",
            \"titleTextStyle\": {\"color\": \"#3f51b5\", \"fontName\": \"Raleway\", \"fontSize\": 16, \"bold\": true},
            \"height\": \"250\",
            \"backgroundColor\": {\"fill\": \"#FFF\"},
            \"chartArea\":{\"width\":\"85%\", \"height\":\"70%\"},
            \"hAxis\": {\"textStyle\": {\"fontName\": \"Raleway\", \"fontSize\": 10}},
            \"vAxis\": {\"titleTextStyle\": {\"fontName\": \"Raleway\", \"fontSize\": 10, \"italic\":\"false\"}},
            \"vAxes\": {\"0\": {\"title\": \"Leitura (m³)\"}, \"1\": {\"title\": \"Consumo (m³)\", \"minValue\": \"0\"}},
            \"legend\": {\"position\": \"bottom\", \"maxLines\": 3,
                \"textStyle\": {\"fontName\": \"Raleway\", \"fontSize\": 10}
            },
            \"colors\": [\"#3f51b5\",\"#ff5722\"],
            \"seriesType\": \"bars\",
            \"series\": {\"0\": {\"targetAxisIndex\": \"0\"}, \"1\": {\"type\": \"line\", \"targetAxisIndex\": \"1\"}},
            \"enableInteractivity\": true
        }";

        return $this->dados;
    }

    function pega_dados_condominio($user, $perfil, $dados_post) {
        global $conecta_db;
        
        $objeto = __NAMESPACE__ . '\\' . $dados_post['objeto'];
        $objeto = new $objeto();
        $rows_html = $objeto->pega_html_consumos_condominio($user, $perfil, $dados_post['id_condominio']);
        $this->dados['dados_chart'] = "        
        {
            \"cols\": [{\"id\": \"mes_ano\", \"label\": \"Data da Leitura\", \"type\": \"string\"},
                {\"id\": \"volume\", \"label\": \"Medição (m³)\", \"type\": \"number\"},
                {\"id\": \"consumo\", \"label\": \"Consumo (m³)\", \"type\": \"number\"}
            ],
            \"rows\": [
                {$rows_html}
            ]
        }";

        $condominio = new condominio();
        $condominio->pega_condominio_por_id($dados_post['id_condominio']);
        $nome_condominio = $condominio->pega_nome();
        $this->dados['options_chart'] = "
        {
            \"title\": \"{$nome_condominio}\",
            \"titleTextStyle\": {\"color\": \"#3f51b5\", \"fontName\": \"Raleway\", \"fontSize\": 16, \"bold\": true},
            \"height\": \"300\",
            \"backgroundColor\": {\"fill\": \"#FFF\"},
            \"chartArea\":{\"width\":\"85%\", \"height\":\"70%\"},
            \"hAxis\": {\"textStyle\": {\"fontName\": \"Raleway\", \"fontSize\": 10}},
            \"vAxis\": {\"titleTextStyle\": {\"fontName\": \"Raleway\", \"fontSize\": 10, \"italic\":\"false\"}},
            \"vAxes\": {\"0\": {\"title\": \"Leitura (m³)\"}, \"1\": {\"title\": \"Consumo (m³)\", \"minValue\": \"0\"}},
            \"legend\": {\"position\": \"bottom\", \"maxLines\": 3,
                \"textStyle\": {\"fontName\": \"Raleway\", \"fontSize\": 10}
            },
            \"colors\": [\"#3f51b5\",\"#ff5722\"],
            \"seriesType\": \"bars\",
            \"series\": {\"0\": {\"targetAxisIndex\": \"0\"}, \"1\": {\"type\": \"line\", \"targetAxisIndex\": \"1\"}},
            \"enableInteractivity\": true
        }";

        return $this->dados;
    }
}

session_start();
$conecta_db = new conecta_db(); //Variável GLOBAL de conexão com o banco de dados
$dados = [];
$dados['erro'] = true;

if (isset($_SESSION['dados_cliente']) && isset($_POST['objeto'])) {
    $user = new user();
    $user->pega_user_por_email($_SESSION['dados_cliente']['email']);
    $perfil = new perfil();
    $perfil->pega_perfil_por_id($user->pega_id_perfil());
    $dados_chart = new dados_chart();
}

$acao = "";
if (str_contains($_POST['objeto'], "consumo_condominio_")) {
    $acao = "consumo_condominio";
} else {
    $acao = $_POST['objeto'];
}

if ($perfil->admin() || $perfil->cadastrador() || $perfil->link_autorizado($acao)) {
    if ($_POST['objeto'] == "consumo_condominio" || $_POST['objeto'] == "consumo_unidade") {
        $valor_dados_chart = $dados_chart->pega_dados($user, $perfil, $_POST);
        $dados['erro'] = false;
    }  elseif (str_contains($_POST['objeto'], "consumo_condominio_")) {
        $id_condominio = substr($_POST['objeto'], 19);
        if ($perfil->admin() || $perfil->autorizado($user, $acao, $id_condominio,)) {
            $valor_dados_chart = $dados_chart->pega_dados_condominio($user, $perfil, 
            array("objeto" => "consumo_condominio", "id_condominio" => $id_condominio));
           $dados['erro'] = false;
        }
    }
} elseif (!($perfil->admin() || $perfil->cadastrador())) {
    if (str_contains($_POST['objeto'], "consumo_unidade_")) {
        $id_unidade = substr($_POST['objeto'], 16);
        if (in_array($id_unidade,$user->pega_ids_unidade())) {
            $valor_dados_chart = $dados_chart->pega_dados_usuario($user, $perfil, 
            array("objeto" => "consumo_unidade", "id_unidade" => $id_unidade));
           $dados['erro'] = false;
        }
    }
}

if ($dados['erro'] == false) {
    $dados['dados_chart'] = $valor_dados_chart['dados_chart'];
    $dados['options_chart'] = $valor_dados_chart['options_chart'];   
} else {
    $dados = [];
    $dados['erro'] = true;
    $dados['mensagem_erro'] = "ACESSO NEGADO!";
}

$json = json_encode($dados);
die($json);
?>