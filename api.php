<?php
// Prevent direct access if name or phone are missing
if ( !isset($_POST['name']) || !isset($_POST['phone']) ){
    if (isset($_SERVER['HTTP_REFERER'])){
        header('Location: '.$_SERVER['HTTP_REFERER']);
    } else {
        header('Location: /');
    }
    exit;
}

// Get the user's real IP address
if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
    $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
} elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
    $ip = $_SERVER['HTTP_CLIENT_IP'];
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ip = trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
} else {
    $ip = $_SERVER['REMOTE_ADDR'];
}

// Determine current landing page base URL
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
$host = $_SERVER['HTTP_HOST'] ?? 'domain.com';
$baseUrl = $protocol . "://" . $host;

// Build payload for MonadLead API
$payload = [
    "api_key"      => "185bc954642a73cdb0d9b8508d22fd7a",
    "name"         => trim($_POST['name']),
    "phone"        => trim($_POST['phone']),
    "offer_id"     => "1579",
    "country_code" => "PH",
    "base_url"     => $baseUrl,
    "referrer"     => $_SERVER['HTTP_REFERER'] ?? $baseUrl,
    "user_ip"      => $ip,
    "sub_1"        => $_GET['sub_1'] ?? $_GET['sub1'] ?? '',
    "sub_2"        => $_GET['sub_2'] ?? $_GET['sub2'] ?? '',
    "sub_3"        => $_GET['sub_3'] ?? $_GET['sub3'] ?? '',
    "sub_4"        => $_GET['sub_4'] ?? $_GET['sub4'] ?? '',
    "utm_campaign" => $_GET['utm_campaign'] ?? '',
    "utm_source"   => $_GET['utm_source'] ?? '',
    "utm_medium"   => $_GET['utm_medium'] ?? '',
    "utm_term"     => $_GET['utm_term'] ?? '',
    "utm_content"  => $_GET['utm_content'] ?? ''
];

// Target API endpoint (Standard endpoint for MonadLead API)
$apiUrl = 'https://api.monadlead.com/v1/lead/create';

// Send the request via cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$result = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

// Log raw response for debugging purposes (optional - uncomment if needed)
// file_put_contents('api_debug.log', date('[Y-m-d H:i:s] ') . "Request: " . json_encode($payload) . " | Response (HTTP $httpCode): " . $result . " | Error: " . $curlError . PHP_EOL, FILE_APPEND);

// Redirect to success page
$response = json_decode($result, true);
$leadId = $response['lead_id'] ?? $response['id'] ?? uniqid();

header('Location: success.html?id=' . urlencode($leadId));
exit;
?>