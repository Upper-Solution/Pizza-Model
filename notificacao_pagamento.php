<?php
// Assinatura secreta fornecida pelo Mercado Pago
define('MERCADOPAGO_SECRET_KEY', 'b1846d5d8f82bf7bafacb95d809a670643aa0f2b2598d0147b9c26c8fb1e6923');

// Receber dados da notificação
$input = file_get_contents('php://input');
$notification = json_decode($input, true);

// Verificar a assinatura da notificação
$signature = $_SERVER['HTTP_X_MELI_SIGNATURE'];

if (hash_equals($signature, hash_hmac('sha256', $input, MERCADOPAGO_SECRET_KEY))) {
    // A assinatura é válida, processar a notificação
    if ($notification['type'] == 'payment') {
        $payment_id = $notification['data']['id'];
        
        // Obter informações detalhadas sobre o pagamento
        require __DIR__ . '/vendor/autoload.php';
        MercadoPago\SDK::setAccessToken('YOUR_ACCESS_TOKEN');
        
        $payment = MercadoPago\Payment::find_by_id($payment_id);
        
        // Processar o pagamento de acordo com o status
        switch ($payment->status) {
            case 'approved':
                // Pagamento aprovado
                echo "Pagamento aprovado";
                break;
            case 'pending':
                // Pagamento pendente
                echo "Pagamento pendente";
                break;
            case 'rejected':
                // Pagamento rejeitado
                echo "Pagamento rejeitado";
                break;
            // Outros status
            default:
                echo "Status desconhecido";
                break;
        }
    } else {
        echo "Tipo de notificação desconhecido";
    }
} else {
    // A assinatura não é válida
    http_response_code(400);
    echo "Assinatura inválida";
    exit();
}

http_response_code(200);
?>
