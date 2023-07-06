<?php
namespace acquaccount;
use mysqli;
use DateTime;
use DatePeriod;
use DateInterval;
use DateTimeImmutable;
use Exception;

/*********************
 * Classe conecta_db
 * ----------------------
 * Conecta ao banco de dados e realiza uma série de operações pré-programadas
 * 
 */

class conecta_db {
    private $dbHost     = DB_HOST; 
    private $dbUsername = DB_USERNAME; 
    private $dbPassword = DB_PASSWORD; 
    private $dbName     = DB_NAME;
    private $db;
    
    function __construct(){ 
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        date_default_timezone_set('America/Sao_Paulo');
        
        if(!isset($this->db)){            
            try {
                $conn = new mysqli($this->dbHost, $this->dbUsername, $this->dbPassword, $this->dbName);
            } catch (err) {
                die("Falha ao conectar com o Banco de Dados MySQL. Por favor, entre em contato com o Administrador!");
            }
             
            
            if($conn->connect_error){ 
                die("Falha ao conectar com o Banco de Dados MySQL: " . $conn->connect_error); 
            }else{ 
                $this->db = $conn; 
            } 
        } 
    } 

    function pega_erro() {
        return $this->db->error;
    }

    function pega_insert_id() {
        return $this->db->insert_id;
    }

    private function pega_um_valor($objeto, $coluna, $chave, $valor_chave) {
        $tabela = substr(strrchr(get_class($objeto), '\\'), 1);
        if (!property_exists($objeto, $coluna)) {
            throw new Exception("Query inválida! A coluna '{$coluna}' não existe na tabela {$tabela}");
        }
        
        if (!property_exists($objeto, $chave)) {
            throw new Exception("Query inválida! A coluna '{$chave}' não existe na tabela {$tabela}");
        }
        
        $stmt = $this->db->prepare("SELECT $coluna FROM $tabela WHERE $chave=?");
        $stmt->bind_param("s", $valor_chave);
        $stmt->execute();
        $stmt->bind_result($valor_coluna);
        $stmt->fetch();

        return $valor_coluna;
    }

    private function pega_objeto($objeto, $chave, $valor_chave) {
        $tabela = substr(strrchr(get_class($objeto), '\\'), 1);
        if (!property_exists($objeto, $chave)) {
            throw new Exception("Query inválida! A coluna '{$chave}' não existe na tabela {$tabela}");
        }

        $stmt = $this->db->prepare("SELECT * FROM $tabela WHERE $chave=?");
        $stmt->bind_param("s", $valor_chave);
        $stmt->execute();
        $linha = $stmt->get_result()->fetch_assoc();

        return $linha;
    }
    
    private function salva_objeto($objeto, $dados, $chave, $valor_chave='') {
        $tabela = substr(strrchr(get_class($objeto), '\\'), 1);
        $colunas = "";
        $string_param = ""; 
        $array_param = [];
        foreach ($dados as $coluna => $valor) {
            if ($coluna == $chave) { continue; }
            if (property_exists($objeto, $coluna)) {
                $colunas .= "$coluna=?, ";
                $string_param .= "s";
                if (is_array($valor)) {
                    $valor = array_values(array_filter($valor, fn($value) => $value !== ""));
                    $valor = json_decode($valor[0]);
                    $valor = serialize($valor);
                }
                $array_param[] = $valor;
            }
        }
        
        if ($colunas == "") {
            return false;
        } else {
            $colunas = substr($colunas,0,-2);
        }

        if ($valor_chave == "" || $valor_chave == 0) {
            $stmt = $this->db->prepare("INSERT INTO $tabela SET $colunas");
            $stmt->bind_param($string_param, ...$array_param);
        } else {
            $stmt = $this->db->prepare("UPDATE $tabela SET $colunas WHERE $chave=?");
            $string_param .= "s";
            $array_param[] = $valor_chave;
            $stmt->bind_param($string_param, ...$array_param);
        }

        try {
            $resposta = $stmt->execute();
        } catch (Exception $e) {
            $resposta = $stmt->error;
        }
        
        return $resposta;
    }

    private function deleta_objeto($objeto, $chave, $chave_valor) {
        $tabela = substr(strrchr(get_class($objeto), '\\'), 1);
        if (!property_exists($objeto, $chave)) {
            return false;
        }
        
        $stmt = $this->db->prepare("DELETE FROM $tabela WHERE $chave=?");
        $stmt->bind_param("s", $chave_valor);
        
        try {
            $resposta = $stmt->execute();
        } catch (Exception $e) {
            $resposta = $stmt->error;
        }
        
        return $resposta;
    }

    private function pega_chaves_objeto($objeto, $chaves_array, $ordem = [], $chave_where = '', $chave_valor = '') {
        $tabela = substr(strrchr(get_class($objeto), '\\'), 1);
        $lista_chaves = "";
        foreach ($chaves_array as $chave => $valor) {
            if (!property_exists($objeto, $valor)) {
                throw new Exception("Query inválida! A coluna '{$valor}' não existe na tabela {$tabela}");
            }
            $lista_chaves .= "{$valor},";
        }
        $lista_chaves = substr($lista_chaves, 0, -1);
        
        $order_by = "";
        if (isset($ordem)) {
            $order_by = "ORDER BY ";
            foreach ($ordem as $chave => $valor) {
                if (property_exists($objeto, $valor)) {
                    if (!is_numeric($chave)) {
                        $order_by .= "{$chave}($valor), $valor, ";
                    } else {
                        $order_by .= "$valor, ";
                    }
                }
            }

            if ($order_by == "ORDER BY ") {
                $order_by = "";
            } else {
                $order_by = substr($order_by, 0, -2);
            }
        }

        if ($chave_where == "") { 
            $stmt = $this->db->prepare("SELECT $lista_chaves FROM $tabela $order_by"); 
        } else {
            if (!property_exists($objeto, $chave_where)) {
                throw new Exception("Query inválida! A coluna '{$chave_where}' não existe na tabela {$tabela}");
            }
            $stmt = $this->db->prepare("SELECT $lista_chaves FROM $tabela WHERE $chave_where=? $order_by");
            $stmt->bind_param("s", $chave_valor);
        }
         
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }   

    private function pega_ids_objeto($objeto, $ordem = [], $chave_where = '', $chave_valor = '') {
        return $this->pega_chaves_objeto($objeto, array('id'), $ordem, $chave_where, $chave_valor);
    }

    private function query_ultimos_doze_meses($mes = "", $ano = "") {
        if ($mes == "" && $ano == "") {
            $interval = new DateTime("now");
        } else {
            $interval = new DateTime("{$ano}-{$mes}-01 00:00:00");
        }
        
        $mes = $interval->format('m');
        $ano = $interval->format('Y');
        $datas_temp_query = "SELECT '{$mes}' AS mes, '{$ano}' AS ano FROM DUAL";
        for ($index = 1; $index < 12; $index++) {
            $interval = $interval->sub(new DateInterval('P1M'));
            $mes = $interval->format('m');
            $ano = $interval->format('Y');
            $datas_temp_query .= " 
            UNION
            SELECT '{$mes}' AS mes, '{$ano}' AS ano FROM DUAL";
        }

        return $datas_temp_query;
    }

    function pega_qtd_objeto($objeto, $chave_where = '', $chave_valor = '') {
        $tabela = substr(strrchr(get_class($objeto), '\\'), 1);
        if ($chave_where == '' ) {
            $stmt = $this->db->prepare("SELECT id FROM $tabela");
        } else {
            if (!property_exists($objeto, $chave_where)) {
                throw new Exception("Query inválida! A coluna '{$chave_where}' não existe na tabela {$tabela}");
            }
            $stmt = $this->db->prepare("SELECT id FROM $tabela WHERE $chave_where=?");
            $stmt->bind_param("s", $chave_valor);   
        }
        
        $stmt->execute();
        $stmt->store_result();

        return $stmt->num_rows();
    }

    
    function verifica_perfil_em_uso($objeto, $id) {
        return $this->pega_qtd_objeto($objeto, "id_perfil", $id);
    }

    function verifica_bloco_em_uso($objeto, $id) {
        return $this->pega_qtd_objeto($objeto, "id_bloco", $id);
    }

    function verifica_condominio_em_uso($objeto, $id) {
        return $this->pega_qtd_objeto($objeto, "id_condominio", $id);
    }

    function verifica_unidade_em_uso($objeto, $id) {
        return $this->pega_qtd_objeto($objeto, "id_unidade", $id);
    }

    function pega_nome_perfil($objeto, $id) {
        return $this->pega_um_valor($objeto,"nome","id",$id);
    }

    function pega_user_por_id($objeto, $id) {
        return $this->pega_objeto($objeto,"id",$id);
    }

    function pega_user_por_email($objeto, $email_user) {
        return $this->pega_objeto($objeto,"email",$email_user);
    }

    function pega_perfil_por_id($objeto, $id) {
        return $this->pega_objeto($objeto,"id",$id);
    }


    function pega_unidade_por_id($objeto, $id) {
        return $this->pega_objeto($objeto,"id",$id);
    }

    function pega_bloco_por_id($objeto, $id) {
        return $this->pega_objeto($objeto,"id",$id);
    }

    function pega_condominio_por_id($objeto, $id) {
        return $this->pega_objeto($objeto,"id",$id);
    }

    function pega_unidades_user_por_id_user($objeto, $id) {
        return $this->pega_objeto($objeto,"id_user",$id);
    }

    function pega_todos_user_id($objeto) {
        $ordem = array("first_name","given_name","data_criado");
        return $this->pega_ids_objeto($objeto, $ordem);
    }

    function pega_todos_perfil_id($objeto) {
        $ordem = array("cadastrador","admin_master","nome");
        return $this->pega_ids_objeto($objeto, $ordem);
    }

    function pega_todos_perfil_id_nome($objeto) {
        $ordem = array("cadastrador","sindico","admin_master","nome");
        return $this->pega_chaves_objeto($objeto, array("id","nome","cadastrador","sindico","admin_master"),$ordem);           
    }

    function pega_todos_condominio_id($objeto) {
        $ordem = array("nome");
        return $this->pega_ids_objeto($objeto, $ordem);      
    }

    function pega_todos_condominio_id_nome($objeto) {
        $ordem = array("nome");
        return $this->pega_chaves_objeto($objeto, array("id","nome"),$ordem);      
    }

    function pega_todos_bloco_id($objeto) {
        $ordem = array("id_condominio","nome");
        return $this->pega_ids_objeto($objeto, $ordem);  
    }

    function pega_todos_bloco_id_nome($objeto, $id_condominio) {
        $ordem = array("id_condominio","nome");
        return $this->pega_chaves_objeto($objeto, array("id","nome"), $ordem, "id_condominio", $id_condominio);         
    }

    function pega_todos_ids_unidade($objeto, $id_condominio = 0) {
        $ordem = array("id_condominio","id_bloco","LENGTH" => "numero");
        if ($id_condominio == 0) { return $this->pega_ids_objeto($objeto, $ordem); }
        return $this->pega_ids_objeto($objeto, $ordem, $chave_where = "id_condominio", $id_condominio);
    }

    function pega_todos_consumo_unidade_ids($user, $perfil, $objeto, $id_unidade) {
        if (!($perfil->admin() || $perfil->cadastrador())) {
            in_array($id_unidade, $user->pega_ids_unidade());
        } 

        $chaves_array = array("id", "mes", "ano", "valor_m3", "id_unidade");
        $ordem = array("ano", "LENGTH" => "mes");
        $chave_where = "id_unidade";
        $chave_valor = $id_unidade;
        return $this->pega_chaves_objeto($objeto, $chaves_array, $ordem, $chave_where, $chave_valor);
    }

    function salva_dados_condominio ($objeto, $dados, $id = '') {
        return $this->salva_objeto($objeto, $dados, "id",$id);
    }

    function salva_dados_bloco ($objeto, $dados, $id = '') {
        return $this->salva_objeto($objeto, $dados, "id", $id);
    }

    function salva_dados_unidade ($objeto, $dados, $id = '') {
        return $this->salva_objeto($objeto, $dados, "id", $id);
    }

    function salva_dados_consumo_unidade ($objeto, $dados, $id = '') {
        return $this->salva_objeto($objeto, $dados, "id", $id);
    }

    function salva_dados_consumo_condominio ($objeto, $dados, $id = '') {
        return $this->salva_objeto($objeto, $dados, "id", $id);
    }

    function salva_dados_perfil ($objeto, $dados, $id = '') {
        return $this->salva_objeto($objeto, $dados, "id", $id);
    }

    function salva_dados_user ($objeto, $dados, $id = '') {
        return $this->salva_objeto($objeto, $dados, "id", $id);
    }
   
    function salva_dados_cache_relatorio_condominio($objeto, $dados) {
        return $this->salva_objeto($objeto, $dados, "id");
    }

    function salva_dados_cache_relatorio_unidades($objeto, $dados) {
        return $this->salva_objeto($objeto, $dados, "id");
    }

    function deleta_bloco($objeto, $id) {
        return $this->deleta_objeto($objeto,"id",$id);
    }

    function deleta_condominio($objeto, $id) {
        return $this->deleta_objeto($objeto,"id",$id);
    }

    function deleta_perfil($objeto, $id) {
        return $this->deleta_objeto($objeto,"id",$id);
    }

    function deleta_unidade($objeto, $id) {
        return $this->deleta_objeto($objeto,"id",$id);
    }

    function deleta_unidades_user($objeto, $id_user) {
        return $this->deleta_objeto($objeto,"id_user",$id_user);
    }

    function deleta_user($objeto, $id) {
        return $this->deleta_objeto($objeto,"id",$id);
    }

    function verifica_consumo_unidade($id_unidade, $mes, $ano) {
        $stmt = $this->db->prepare("SELECT id FROM consumo_unidade WHERE id_unidade=? AND mes=? AND ano=?");
        $stmt->bind_param("sss", $id_unidade, $mes, $ano);
        $stmt->execute();
        $stmt->bind_result($valor_coluna);
        $stmt->fetch();

        return $valor_coluna;
    }

    function verifica_consumo_condominio($id_condominio, $mes, $ano) {
        $stmt = $this->db->prepare("SELECT id FROM consumo_condominio WHERE id_condominio=? AND mes=? AND ano=?");
        $stmt->bind_param("sss", $id_condominio, $mes, $ano);
        $stmt->execute();
        $stmt->bind_result($valor_coluna);
        $stmt->fetch();

        return $valor_coluna;
    }

    function pega_meses_consumo_unidade($id_unidade) {
        $stmt = $this->db->prepare("SELECT DISTINCT consumo_unidade.mes, consumo_unidade.ano 
        FROM consumo_unidade 
        LEFT JOIN (
            SELECT * FROM unidade
            WHERE unidade.id_condominio = (SELECT id_condominio FROM unidade WHERE unidade.id=?)
        ) AS unidade
        ON unidade.id = consumo_unidade.id_unidade 
        ORDER BY consumo_unidade.ano DESC, consumo_unidade.mes DESC");
        
        $stmt->bind_param("i", $id_unidade);
        $stmt->execute();

        $meses_consumo_unidade = [];
        $resultado = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        foreach($resultado as $chave => $valor) {
            $mes_ano = $valor['ano'] . "-" . str_pad($valor['mes'], 2, '0', STR_PAD_LEFT) . "-01 00:00:00";
            $interval = new DateTime($mes_ano);
            $meses_consumo_unidade[] = $interval->format('m/Y');
        }
        $interval = new DateTime("now");
        $data_atual = $interval->format('m/Y');
        if (!in_array($data_atual, $meses_consumo_unidade)) {
            $interval = new DateTime("now");
            $meses_consumo_unidade = array_merge(array($data_atual), $meses_consumo_unidade);
        } 

        return $meses_consumo_unidade;
    }

    function pega_consumo_unidade_por_mes ($perfil, $mes, $ano) {
        $limite_perfil = "";
        if (!$perfil->admin()) {
            $ids_condominio_autorizados = implode(",",$perfil->pega_ids_condominio());
            $limite_perfil = "WHERE unidade_por_mes.id_condominio IN ({$ids_condominio_autorizados})";
        }
        
        $stmt = $this->db->prepare("SELECT unidade_por_mes.id_unidade, unidade_por_mes.id FROM 
        (SELECT unidade.id AS id_unidade, condominio.id AS id_condominio,
        condominio.nome AS condominio_nome, bloco.nome As bloco_nome, unidade.numero,
        (CASE WHEN consumo_unidade.id IS NULL THEN 0 ELSE consumo_unidade.id END) AS id
        FROM unidade 
        JOIN condominio
        ON condominio.id = unidade.id_condominio
        JOIN bloco
        ON bloco.id = unidade.id_bloco
        LEFT JOIN 
        (SELECT consumo_unidade.id, consumo_unidade.id_unidade 
        FROM consumo_unidade
        WHERE consumo_unidade.mes=? AND consumo_unidade.ano=?
        ) AS consumo_unidade 
        ON unidade.id = consumo_unidade.id_unidade) AS unidade_por_mes
        {$limite_perfil}
        ORDER BY unidade_por_mes.condominio_nome, unidade_por_mes.bloco_nome, LENGTH(unidade_por_mes.numero), unidade_por_mes.numero
        ");
        $stmt->bind_param("ss", $mes, $ano);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    function pega_meses_consumo_condominio($id_condominio) {
        $stmt = $this->db->prepare("SELECT DISTINCT consumo_condominio.mes, consumo_condominio.ano 
        FROM consumo_condominio 
        ORDER BY consumo_condominio.ano DESC, consumo_condominio.mes DESC");
        
        //$stmt->bind_param("i", $id_condominio);
        $stmt->execute();

        $meses_consumo_condominio = [];
        $resultado = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        foreach($resultado as $chave => $valor) {
            $mes_ano = $valor['ano'] . "-" . str_pad($valor['mes'], 2, '0', STR_PAD_LEFT) . "-01 00:00:00";
            $interval = new DateTime($mes_ano);
            $meses_consumo_condominio[] = $interval->format('m/Y');
        }
        $interval = new DateTime("now");
        $data_atual = $interval->format('m/Y');
        if (!in_array($data_atual, $meses_consumo_condominio)) {
            $interval = new DateTime("now");
            $meses_consumo_condominio = array_merge(array($data_atual), $meses_consumo_condominio);
        } 

        return $meses_consumo_condominio;
    }

    function pega_consumo_condominio_por_mes ($perfil, $mes, $ano) {
        $limite_perfil = "";
        if (!$perfil->admin()) {
            $ids_condominio_autorizados = implode(",",$perfil->pega_ids_condominio());
            $limite_perfil = "WHERE condominio_por_mes.id_condominio IN ({$ids_condominio_autorizados})";
        }

        $stmt = $this->db->prepare("SELECT condominio_por_mes.id_condominio, condominio_por_mes.id FROM 
        (SELECT condominio.id AS id_condominio, condominio.nome AS condominio_nome,
        (CASE WHEN consumo_condominio.id IS NULL THEN 0 ELSE consumo_condominio.id END) AS id
        FROM condominio
        LEFT JOIN 
        (SELECT consumo_condominio.id, consumo_condominio.id_condominio 
        FROM consumo_condominio
        WHERE consumo_condominio.mes=? AND consumo_condominio.ano=?
        ) AS consumo_condominio 
        ON condominio.id = consumo_condominio.id_condominio ) AS condominio_por_mes
        {$limite_perfil}
        ORDER BY condominio_por_mes.condominio_nome");
        $stmt->bind_param("ss", $mes, $ano);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    function pega_dados_chart_unidades($objeto, $user, $perfil, $id_condominio = 0) {
        $interval = new DateTime("now");
        $mes = $interval->format('m');
        $ano = $interval->format('Y');

        $limite_perfil = "";
        $limite_id_condominio = "";
        if ($id_condominio != 0) {
            $limite_id_condominio = " AND dados_unidades.uidcon={$id_condominio}";
        }

        if (!$perfil->admin()) {
            $ids_condominio_autorizados = implode(",",$perfil->pega_ids_condominio());
            if ($ids_condominio_autorizados == "") { $ids_condominio_autorizados = "''"; }
            $limite_perfil = "AND dados_unidades.uidcon IN ({$ids_condominio_autorizados})";
        }
        
        $stmt = $this->db->prepare("SELECT unidade_por_mes.nome, unidade_por_mes.valor FROM
        (SELECT 'Aguardando Leitura' AS nome, COUNT(dados_unidades.uid) AS valor, dados_unidades.uidcon AS id_condominio
        FROM
        (SELECT unidade.id AS uid, unidade.id_condominio AS uidcon, 
        consumo_unidade.id AS cuid, consumo_unidade.validado AS cuval
        FROM unidade
        LEFT JOIN consumo_unidade
        ON consumo_unidade.id_unidade = unidade.id
        AND consumo_unidade.mes=? AND consumo_unidade.ano=?) AS dados_unidades
        WHERE dados_unidades.cuid IS NULL
        {$limite_perfil}
        {$limite_id_condominio}
        
        UNION
        SELECT 'Aguardando Validação' AS nome, COUNT(dados_unidades.uid) AS valor, dados_unidades.uidcon AS id_condominio
        FROM
        (SELECT unidade.id AS uid, unidade.id_condominio AS uidcon, 
        consumo_unidade.id AS cuid, consumo_unidade.validado AS cuval
        FROM unidade
        LEFT JOIN consumo_unidade
        ON consumo_unidade.id_unidade = unidade.id
        AND consumo_unidade.mes=? AND consumo_unidade.ano=?) AS dados_unidades
        WHERE dados_unidades.cuval = 0
        {$limite_perfil}
        {$limite_id_condominio}
        
        UNION
        SELECT 'Consumo Validado' AS nome, COUNT(dados_unidades.uid) AS valor, dados_unidades.uidcon AS id_condominio
        FROM
        (SELECT unidade.id AS uid, unidade.id_condominio AS uidcon, 
        consumo_unidade.id AS cuid, consumo_unidade.validado AS cuval
        FROM unidade
        LEFT JOIN consumo_unidade
        ON consumo_unidade.id_unidade = unidade.id
        AND consumo_unidade.mes=? AND consumo_unidade.ano=?) AS dados_unidades
        WHERE dados_unidades.cuval = 1
        {$limite_perfil}
        {$limite_id_condominio}
        ) AS unidade_por_mes
        ");

        $stmt->bind_param("ssssss", $mes, $ano, $mes, $ano, $mes, $ano);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    function pega_dados_chart_condominios($objeto, $user, $perfil) {
        $interval = new DateTime("now");
        $mes = $interval->format('m');
        $ano = $interval->format('Y');
    
        $limite_perfil = "";
        if (!$perfil->admin()) {
            $ids_condominio_autorizados = implode(",",$perfil->pega_ids_condominio());
            if ($ids_condominio_autorizados == "") { $ids_condominio_autorizados = "''"; }
            $limite_perfil = "AND dados_condominios.cid IN ({$ids_condominio_autorizados})";
        }

        $stmt = $this->db->prepare("SELECT 'Aguardando Leitura' AS nome, COUNT(dados_condominios.cid) AS valor 
        FROM
        (SELECT condominio.id AS cid, 
        consumo_condominio.id AS ccid
        FROM condominio
        LEFT JOIN consumo_condominio
        ON consumo_condominio.id_condominio = condominio.id
        AND consumo_condominio.mes=? AND consumo_condominio.ano=?) AS dados_condominios
        WHERE dados_condominios.ccid IS NULL
        {$limite_perfil}
        
        UNION
        SELECT 'Consumo Lançado' AS nome, COUNT(dados_condominios.cid) AS valor
        FROM
        (SELECT condominio.id AS cid, 
        consumo_condominio.id AS ccid
        FROM condominio
        LEFT JOIN consumo_condominio
        ON consumo_condominio.id_condominio = condominio.id
        AND consumo_condominio.mes=? AND consumo_condominio.ano=?) AS dados_condominios
        WHERE dados_condominios.ccid IS NOT NULL
        {$limite_perfil}");

        $stmt->bind_param("ssss", $mes, $ano, $mes, $ano);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }



    private function pega_consumos_objeto($user, $perfil, $id_objeto, $objeto) {
        if (isset($_SESSION['mes'])) {
            $datas_temp_query = $this->query_ultimos_doze_meses($_SESSION['mes'],$_SESSION['ano']);
        } else {
            $datas_temp_query = $this->query_ultimos_doze_meses();
        }
        $tabela = substr(strrchr(get_class($objeto), '\\'), 1);
        $coluna_consumo = "";
        if ($tabela == "condominio") {
            $coluna_consumo = "(CASE WHEN consumo_{$tabela}.consumo IS NULL THEN 0 ELSE consumo_{$tabela}.consumo END) AS consumo,
            (CASE WHEN consumo_{$tabela}.valor_reais IS NULL THEN 0 ELSE consumo_{$tabela}.valor_reais END) AS valor_reais,";
        }
        $stmt = $this->db->prepare("SELECT consumo_{$tabela}.id AS id, datas_temp.mes AS mes, datas_temp.ano AS ano,
        CONCAT(datas_temp.mes,'/',  datas_temp.ano) AS mes_ano, 
        {$coluna_consumo}
        (CASE WHEN consumo_{$tabela}.valor_m3 IS NULL THEN 0 ELSE consumo_{$tabela}.valor_m3 END) AS medicao
        FROM (
            {$datas_temp_query}
        ) AS datas_temp
        LEFT JOIN consumo_{$tabela}
        ON datas_temp.mes = consumo_{$tabela}.mes
        AND datas_temp.ano = consumo_{$tabela}.ano
        AND consumo_{$tabela}.id_{$tabela}=?
        ORDER BY datas_temp.ano, datas_temp.mes
        ");

        $stmt->bind_param("s", $id_objeto);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    function pega_consumos_unidade($user, $perfil, $id_unidade) {
        return $this->pega_consumos_objeto($user, $perfil, $id_unidade, new unidade());
    }

    function pega_consumos_condominio($user, $perfil, $id_condominio) {
        return $this->pega_consumos_objeto($user, $perfil, $id_condominio, new condominio());
    }

    function pega_consumos_unidades_condominio_mes_ano($user, $perfil, $id_condominio, $mes, $ano) {
        $stmt = $this->db->prepare("SELECT consumo_unidade.id, consumo_unidade.id_unidade, unidade.numero AS numero_unidade,
        (CASE WHEN consumo_unidade.valor_m3 IS NULL THEN 0 ELSE consumo_unidade.valor_m3 END) AS valor_m3
        FROM unidade
        LEFT JOIN consumo_unidade
        ON consumo_unidade.id_unidade = unidade.id
        AND consumo_unidade.mes=? AND consumo_unidade.ano=?
        WHERE unidade.id_condominio=?
        ORDER BY LENGTH(unidade.id), unidade.id");
        $stmt->bind_param("sss", $mes, $ano, $id_condominio);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    function pega_cache_consumos_unidade($user, $perfil, $id_unidade) {
        if (isset($_SESSION['mes'])) {
            $datas_temp_query = $this->query_ultimos_doze_meses($_SESSION['mes'],$_SESSION['ano']);
        } else {
            $datas_temp_query = $this->query_ultimos_doze_meses();
        }
        
        $stmt = $this->db->prepare("SELECT relatorio_unidade.id, relatorio_unidade.id_unidade, unidade.numero AS numero_unidade,
        relatorio_unidade.valor_m3 AS valor_m3, relatorio_unidade.valor_reais AS valor_reais, consumo_unidade.valor_m3 AS medicao, 
        datas_temp.mes AS mes, datas_temp.ano AS ano, CONCAT(datas_temp.mes, datas_temp.ano) AS mes_ano
        FROM (
            {$datas_temp_query}
        ) AS datas_temp
        LEFT JOIN relatorio_unidade
        ON relatorio_unidade.mes = datas_temp.mes AND relatorio_unidade.ano = datas_temp.ano
        AND relatorio_unidade.id_unidade=?
        LEFT JOIN consumo_unidade 
        ON consumo_unidade.mes = relatorio_unidade.mes AND consumo_unidade.ano = relatorio_unidade.ano
        AND consumo_unidade.id_unidade=relatorio_unidade.id_unidade
        LEFT JOIN unidade
        ON unidade.id = relatorio_unidade.id_unidade
        ORDER BY datas_temp.ano, datas_temp.mes");
        $stmt->bind_param("s", $id_unidade);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    }

    function pega_cache_consumos_unidades_condominio_mes_ano($user, $perfil, $id_condominio, $mes, $ano) {
        $stmt = $this->db->prepare("SELECT relatorio_unidade.id, relatorio_unidade.id_unidade, unidade.numero AS numero_unidade,
        relatorio_unidade.valor_m3 AS valor_m3, relatorio_unidade.valor_reais AS valor_reais
        FROM relatorio_unidade
        LEFT JOIN unidade
        ON relatorio_unidade.id_unidade = unidade.id
        WHERE unidade.id_condominio=? AND relatorio_unidade.mes=? AND relatorio_unidade.ano=?
        ORDER BY LENGTH(unidade.id), unidade.id");
        $stmt->bind_param("sss", $mes, $ano, $id_condominio);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    function pega_cache_consumo_condominio_mes_ano($user, $perfil, $id_condominio, $mes, $ano) {
        $stmt = $this->db->prepare("SELECT relatorio_condominio.id, relatorio_condominio.taxa_minima AS taxa_minima, 
        relatorio_condominio.taxa_m3_completa AS taxa_m3_completa, relatorio_condominio.taxa_m3 AS taxa_m3,
        relatorio_condominio.consumo_condominio_m3 AS consumo_condominio_m3
        FROM relatorio_condominio
        WHERE relatorio_condominio.id_condominio=? AND relatorio_condominio.mes=? AND relatorio_condominio.ano=?");
        $stmt->bind_param("sss", $mes, $ano, $id_condominio);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    function pega_consumo_unidade_por_id($objeto, $id) {
        return $this->pega_objeto($objeto,"id",$id);
    }

    function pega_consumo_condominio_por_id($objeto, $id) {
        return $this->pega_objeto($objeto,"id",$id);
    }
}
?>