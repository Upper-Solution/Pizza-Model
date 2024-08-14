<?php
// Inicia a sessão
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['user_email'])) {
    echo json_encode(['success' => false, 'error' => 'Usuário não logado.']);
    exit;
}

// Recebe os dados do formulário
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $orderId = $_POST['order_id'];
    $message = $_POST['message'];

    // Validação dos campos
    if (empty($orderId) || empty($message)) {
        echo json_encode(['success' => false, 'error' => 'Todos os campos são obrigatórios.']);
        exit;
    }

    $userEmail = $_SESSION['user_email'];
    $to = "upperresolution@gmail.com"; // Substitua pelo endereço de e-mail de destino
    $subject = "Mensagem sobre o Pedido #$orderId";
    $headers = "From: $userEmail\r\n";
    $headers .= "Reply-To: $userEmail\r\n";

    // Envio do e-mail
    if (mail($to, $subject, $message, $headers)) {
        echo json_encode(['success' => true, 'message' => 'Email enviado com sucesso!']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Erro ao enviar o email.']);
    }
}
?>
