<?php
// Realiza o Logout do Usuário, revogando o Token e destruindo os dados de sessão
require_once 'config.php';
require_once 'autoload.php';
session_start();

$cliente = new processa_login_google();
$cliente->revokeToken($_SESSION['token']);

unset($_SESSION['token']);
unset($_SESSION['dados_cliente']);
session_destroy();

// Volta para a página inicial
header("Location:".GOOGLE_REDIRECT_URL."?logout=1");
?>