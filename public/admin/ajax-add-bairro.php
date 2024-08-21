<?php
require_once '../../config/config.php';

// Verificar se o admin está logado
session_start();
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
    exit;
}

// Conectar ao banco de dados
$pdo = connectToDatabase($hosts, $port, $dbname, $username, $password);

if (!$pdo) {
    echo json_encode(['success' => false, 'message' => 'Erro na conexão com o banco de dados.']);
    exit;
}

// Verificar se foi enviada uma solicitação POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nomeBairro = $_POST['nome_bairro'];
    $valorBairro = $_POST['valor_bairro'];

    // Verificar se o nome do bairro já existe
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM BairrosEntrega WHERE nome_bairros = :nome_bairros");
    $stmt->execute([':nome_bairros' => $nomeBairro]);

    if ($stmt->fetchColumn() > 0) {
        // Bairro já existe, então retorna um erro
        echo json_encode(['success' => false, 'message' => 'Bairro já existente.']);
    } else {
        // Inserir novo bairro
        $stmt = $pdo->prepare("INSERT INTO BairrosEntrega (nome_bairros, valor_entrega) VALUES (:nome_bairros, :valor_entrega)");
        $stmt->execute([':nome_bairros' => $nomeBairro, ':valor_entrega' => $valorBairro]);

        // Verificar se a inserção foi bem-sucedida
        if ($stmt->rowCount()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao adicionar bairro.']);
        }
    }
}

// Fechar conexão
$pdo = null;
?>
