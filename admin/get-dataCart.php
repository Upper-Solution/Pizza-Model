<?php
// Recebe os dados JSON
$data = json_decode(file_get_contents('php://input'), true);

if ($data) {
    // Processa os dados como necessário
    foreach ($data as $item) {
        $id = $item['id'];
        $qtd = $item['qtd'];
        // Faça o que for necessário com $id e $qtd
    }

    // Retorna uma resposta JSON
    echo json_encode(['status' => 'success', 'message' => 'Dados recebidos com sucesso']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Nenhum dado recebido']);
}
?>
