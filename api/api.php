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

try {
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

    // List of candidate endpoints for MonadLead API (auto-discovery)
    $endpoints = [
        'https://api.monadlead.com/v1/lead',
        'https://api.monadlead.com/lead',
        'https://api.monadlead.com/api/v1/lead',
        'https://api.monadlead.com/api/lead',
        'https://api.monadlead.com/api/lead/create',
        'https://api.monadlead.com/api/v1/lead/create',
        'https://api.monadlead.com/lead/create',
        'https://api.monadlead.com/v1/lead/create',
        'https://monadlead.com/api/lead/create',
        'https://monadlead.com/api/v1/lead/create',
        'https://monadlead.com/api/v1/lead',
        'https://monadlead.com/api/lead',
        'https://api.monadlead.com/ext/add.json',
        'https://monadlead.com/ext/add.json'
    ];

    $result = null;
    $successUrlUsed = '';
    $httpCodeUsed = 0;
    $logData = [];

    foreach ($endpoints as $url) {
        $options = [
            'http' => [
                'method'  => 'POST',
                'header'  => "Content-Type: application/json\r\n" .
                             "Accept: application/json\r\n",
                'content' => json_encode($payload),
                'ignore_errors' => true,
                'timeout' => 4 // Short timeout for faster diagnosis
            ]
        ];
        $context = stream_context_create($options);
        $res = @file_get_contents($url, false, $context);

        // Parse the HTTP response code
        $httpCode = 0;
        if (isset($http_response_header) && is_array($http_response_header)) {
            foreach ($http_response_header as $header) {
                if (preg_match('/^HTTP\/\d\.\d\s+(\d+)/i', $header, $matches)) {
                    $httpCode = intval($matches[1]);
                    break;
                }
            }
        }

        $logData[] = $url . ' => HTTP ' . $httpCode . ' (Len: ' . strlen($res ?? '') . ')';

        // Only accept REST API status codes (200, 201, 400, 401, 403, 422)
        if (in_array($httpCode, [200, 201, 400, 401, 403, 422])) {
            $result = $res;
            $successUrlUsed = $url;
            $httpCodeUsed = $httpCode;
            break;
        }
    }

    // Parse response
    $leadId = uniqid();
    $debugInfo = '';
    
    if ($result) {
        $response = json_decode($result, true);
        if (isset($response['lead_id'])) {
            $leadId = $response['lead_id'];
        } elseif (isset($response['id'])) {
            $leadId = $response['id'];
        }
        $debugInfo = 'Success on ' . $successUrlUsed . ' | HTTP: ' . $httpCodeUsed . ' | Resp: ' . $result;
    } else {
        $debugInfo = 'No valid API endpoint matched. Details: ' . implode('; ', $logData);
    }

    // Redirect to success page, appending the API debug response
    header('Location: ../success.html?id=' . urlencode($leadId) . '&api_res=' . urlencode($debugInfo));
    exit;

} catch (Throwable $e) {
    // If anything fails, redirect to success.html anyway so we don't break the user flow
    header('Location: ../success.html?id=' . urlencode(uniqid()) . '&err=1&api_res=' . urlencode($e->getMessage()));
    exit;
}
?>
