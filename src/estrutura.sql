/* --------------------
-- ESTRUTURA.SQL
-- --------------------
-- Definições das tabelas do sistema
*/

-- Tabela com os dados do Usuário
CREATE TABLE user (
id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
id_perfil INT(10) DEFAULT NULL,
given_name VARCHAR(255) NOT NULL,
family_name VARCHAR(255) NOT NULL,
email TEXT NOT NULL,
CPF VARCHAR(11) NOT NULL,
picture VARCHAR(255) DEFAULT NULL,
ids_condominio TEXT NOT NULL DEFAULT 'a:0:{}',
ids_unidade TEXT NOT NULL DEFAULT 'a:0:{}',
autorizado_lgpd BOOLEAN DEFAULT false,
data_criado TIMESTAMP NOT NULL DEFAULT current_TIMESTAMP(),
data_modificado TIMESTAMP NOT NULL DEFAULT current_TIMESTAMP() ON UPDATE current_TIMESTAMP(),
UNIQUE(email), UNIQUE(CPF)
);

-- Tabela com os dados do perfil
CREATE TABLE perfil (
id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
nome TEXT NOT NULL,
descricao TEXT NOT NULL,
admin_master BOOLEAN DEFAULT false,
sindico BOOLEAN DEFAULT false,
cadastrador BOOLEAN DEFAULT false,
links_autorizado TEXT NOT NULL DEFAULT 'a:0:{}',
ids_condominio TEXT NOT NULL DEFAULT 'a:0:{}',
data_criado TIMESTAMP NOT NULL DEFAULT current_TIMESTAMP(),
data_modificado TIMESTAMP NOT NULL DEFAULT current_TIMESTAMP() ON UPDATE current_TIMESTAMP()
);

-- Tabela com os dados do Condomínio
CREATE TABLE condominio (
id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
nome VARCHAR(255) NOT NULL,
descricao TEXT NOT NULL,
CEP VARCHAR(8) NOT NULL,
endereco TEXT NOT NULL,
numero VARCHAR(255) NOT NULL,
bairro TEXT NOT NULL,
cidade VARCHAR(255) NOT NULL,
estado VARCHAR(2) NOT NULL,
data_criado TIMESTAMP NOT NULL DEFAULT current_TIMESTAMP(),
data_modificado TIMESTAMP NOT NULL DEFAULT current_TIMESTAMP() ON UPDATE current_TIMESTAMP()
);

-- Tabela com os dados do Bloco
CREATE TABLE bloco (
id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
id_condominio INT(6) NOT NULL DEFAULT 0,
nome VARCHAR(255) NOT NULL,
data_criado TIMESTAMP NOT NULL DEFAULT current_TIMESTAMP(),
data_modificado TIMESTAMP NOT NULL DEFAULT current_TIMESTAMP() ON UPDATE current_TIMESTAMP(),
CONSTRAINT bloco_condominio UNIQUE(nome, id_condominio)
);

-- Tabela com os dados da Unidade
CREATE TABLE unidade (
id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
id_condominio INT(6) NOT NULL DEFAULT 0,
numero VARCHAR(6) NOT NULL,
id_bloco INT(6) NOT NULL,
hidrometro VARCHAR(255) NOT NULL,
data_criado TIMESTAMP NOT NULL DEFAULT current_TIMESTAMP(),
data_modificado TIMESTAMP NOT NULL DEFAULT current_TIMESTAMP() ON UPDATE current_TIMESTAMP(),
CONSTRAINT numero_bloco_condominio UNIQUE(numero, id_bloco, id_condominio)
);

-- Tabela com as unidades que o usuário é responsável
CREATE TABLE unidades_user (
id_user INT(6) NOT NULL DEFAULT 0 PRIMARY KEY,
ids_condominio TEXT NOT NULL DEFAULT 0,
ids_unidade TEXT NOT NULL DEFAULT 0,
data_criado TIMESTAMP NOT NULL DEFAULT current_TIMESTAMP(),
data_modificado TIMESTAMP NOT NULL DEFAULT current_TIMESTAMP() ON UPDATE current_TIMESTAMP()
);

-- Tabela com os dados de consumo do Condomínio
CREATE TABLE consumo_condominio (
id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
id_condominio INT(6) NOT NULL,
valor_m3 INT(20) NOT NULL,
consumo INT(20) NOT NULL,
valor_reais VARCHAR(20) NOT NULL,
valor_minimo_reais VARCHAR(20) NOT NULL,
imagem_consumo TEXT NOT NULL,
mes INT(2) NOT NULL,
ano INT(4) NOT NULL,
data_criado TIMESTAMP NOT NULL DEFAULT current_TIMESTAMP(),
data_modificado TIMESTAMP NOT NULL DEFAULT current_TIMESTAMP() ON UPDATE current_TIMESTAMP(),
CONSTRAINT consumo_mes_ano UNIQUE(id_condominio, mes, ano)
);

-- Tabela com os dados de consumo da Unidade
CREATE TABLE consumo_unidade (
id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
id_unidade INT(6) NOT NULL,
valor_m3 INT(20) NOT NULL,
imagem_consumo TEXT NOT NULL,
mes INT(2) UNSIGNED ZEROFILL NOT NULL,
ano INT(4) NOT NULL,
validado BOOLEAN DEFAULT false,
id_validador INT(6) NOT NULL,
data_validado TIMESTAMP NULL,
id_leiturista INT(6) NOT NULL,
data_criado TIMESTAMP NOT NULL DEFAULT current_TIMESTAMP(),
data_modificado TIMESTAMP NOT NULL DEFAULT current_TIMESTAMP() ON UPDATE current_TIMESTAMP(),
CONSTRAINT consumo_mes_ano UNIQUE(id_unidade, mes, ano)
);

-- Trigger ao deletar uma Unidade
DELIMITER $$
CREATE TRIGGER deleta_unidade
    AFTER DELETE
    ON unidade FOR EACH ROW
BEGIN
    DELETE FROM consumo_unidade WHERE consumo_unidade.id_unidade = old.id;
END$$    
DELIMITER ;

-- Trigger ao deletar um Condomínio
DELIMITER $$
CREATE TRIGGER deleta_condominio
    AFTER DELETE
    ON condominio FOR EACH ROW
BEGIN
    DELETE FROM consumo_condominio WHERE consumo_condominio.id_condominio = old.id;
END$$    
DELIMITER ;