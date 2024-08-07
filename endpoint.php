<?php
require_once 'config.php';
require_once 'pix_api.php';

$data = json_decode(file_get_contents('php://input'), true);
$amount = $data['amount'];

// Chame a função da API do Pix para gerar o QR code
$qrCode = generatePixQRCode($amount);

echo json_encode(['qrCode' => $qrCode]);
?>
