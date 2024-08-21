<?php
///////////////////////////////////////////////////////////////////////////////
//     ESSE CODIGO É PRA INSERIR NO BANCO A NOVA SENHA E O LOGIN DO ADM    ////
///////////////////////////////////////////////////////////////////////////////

// Inclui o arquivo de configuração
require_once '../../config/config.php';

// Obtém a conexão com o banco de dados
$pdo = connectToDatabase($hosts, $port, $dbname, $username, $password);

// Verifica se a conexão foi bem-sucedida
if (!$pdo) {
    die("Não foi possível conectar ao banco de dados.");
}

// Username e senha para o novo administrador
$newAdminUsername = 'admin';
$newAdminPassword = '123456';

// Gerar o hash da senha
$hashedPassword = password_hash($newAdminPassword, PASSWORD_DEFAULT);

try {
    // Inserir o novo administrador no banco de dados
    $stmt = $pdo->prepare('INSERT INTO Admin (username, password) VALUES (?, ?)');
    $stmt->execute([$newAdminUsername, $hashedPassword]);

    echo "Novo administrador inserido com sucesso!";
} catch (PDOException $e) {
    echo "Erro ao inserir o administrador: " . $e->getMessage();
}

// Fechar conexão
$pdo = null;
?>
