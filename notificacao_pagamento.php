<?php
// Verificar se o arquivo está sendo acessado
echo "Webhook recebido";

// Verificar a assinatura da notificação
$input = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_MELI_SIGNATURE'];
define('MERCADOPAGO_SECRET_KEY', 'c7dd4646dc21e9b60c5cad196ef41f16d4163466f707d7714b42c305a6d5c686');

if (hash_equals($signature, hash_hmac('sha256', $input, MERCADOPAGO_SECRET_KEY))) {
    echo "Assinatura válida";
} else {
    http_response_code(400);
    echo "Assinatura inválida";
}
?>
