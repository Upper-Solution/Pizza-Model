<?php
// Iniciar a sessão
session_start();

// Inclui o arquivo de configuração
require_once '../../config/config.php';

// Verificar se o admin está logado
if (!isset($_SESSION['admin_id'])) {
    header('Location: adm-login.php');
    exit;
}

// Função para encerrar a sessão
if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: adm-login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel de Administração</title>
    <link rel="stylesheet" href="../css/painel.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>Bem-vindo ao Painel de Administração</h1>
            <form method="POST" action="">
                <button type="submit" name="logout" class="exit-button">Sair</button>
            </form>
        </header>
        <div class="dashboard">
            <div class="card" onclick="window.location.href='adm-item.php'">
                <h2>Cardápio</h2>
                <p>Aqui você pode cadastrar, editar e excluir os itens do seu cardápio.</p>
            </div>
            <div class="card" onclick="window.location.href='adm-sobre.php'">
                <h2>Configurações Gerais</h2>
                <p>Alterar as informações da página sobre.</p>
            </div>
            <div class="card" onclick="window.location.href='admin-orders.php'">
                <h2>Pedidos</h2>
                <p>Gerenciar os pedidos realizados pelos clientes.</p>
            </div>
            <div class="card" onclick="window.location.href='adm-clientes.php'">
                <h2>Sistema de Pontuação</h2>
                <p>(AINDA ESTA EM DESENVOLVIMENTO MEU COPINXA)</p>
            </div>
            <div class="card" onclick="window.location.href='adm-relatorio.php'">
                <h2>Gerar Relatórios</h2>
                <p>Visualizar relatórios de vendas e desempenho.</p>
            </div>
        </div>
    </div>
</body>
</html>
