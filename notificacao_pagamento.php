<?php
// Insira aqui a assinatura secreta gerada pelo Mercado Pago
define('MERCADOPAGO_SECRET_KEY', 'sua_assinatura_secreta');

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
        require __DIR__ .  '/vendor/autoload.php';
        MercadoPago\SDK::setAccessToken('YOUR_ACCESS_TOKEN');
        
        $payment = MercadoPago\Payment::find_by_id($payment_id);
        
        // Processar o pagamento de acordo com o status
        switch ($payment->status) {
            case 'approved':
                // Pagamento aprovado
                break;
            case 'pending':
                // Pagamento pendente
                break;
            case 'rejected':
                // Pagamento rejeitado
                break;
            // Outros status
        }
    }
} else {
    // A assinatura não é válida
    http_response_code(400);
    echo "Assinatura inválida";
    exit();
}

http_response_code(200);
?>
