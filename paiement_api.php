
<?php
// URL de base de l'API
$base_url = "https://api.orange.com"; // Remplacez par l'URL de l'API

// Merchant Key ou Token
// $header_token = ""; // Remplacez par votre Header Token
$merchant_key = "e9afd305"; // Remplacez par votre clé/token


header('Content-Type: application/json');

$headers = [
    "Authorization: Basic cXNJSlIxalVYUFZaVDhEZXZudGxDVjdncEpYUFQzMDY6YXNVeldGcUZqN3pZa0VjZQ==",
    "Content-Type: application/json"
];

$data = json_encode([
    'merchant_key' => 'e9afd305',
    'amount' => $_POST['amount'],
    'order_id' => $_POST['order_id'],
    'return_url' => 'https://api.orange.com',
    'cancel_url' => 'https://api.orange.com/webpaydev/cancel',
    'notif_url' => 'https://api.orange.com/webpaydev/notif'
]);

$ch = curl_init('https://api.api.orange.com/initiate-payment');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

echo $response;
