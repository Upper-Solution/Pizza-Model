<?php
session_start();

// Verifica se o usuário é administrador
if (!isset($_SESSION['admin_id'])) {
    header('Location: adm-login.php');
    exit;
}

// Inclui o arquivo de configuração
require_once '../config.php';

// Obtém a conexão com o banco de dados
$pdo = connectToDatabase($hosts, $port, $dbname, $username, $password);

// Verifica se a conexão foi bem-sucedida
if (!$pdo) {
    die("Não foi possível conectar ao banco de dados.");
}

// Obtém os dados do POST
$id = isset($_POST['id']) ? $_POST['id'] : null;
$status = isset($_POST['status']) ? $_POST['status'] : null;

// Valida os dados
if ($id && $status) {
    try {
        // Atualiza o status do pedido
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
        $stmt->execute([$status, $id]);
        echo "Status atualizado com sucesso.";
    } catch (PDOException $e) {
        echo "Erro ao atualizar status: " . $e->getMessage();
    }
} else {
    echo "Dados inválidos.";
}

// Fechar conexão
$pdo = null;
?>
