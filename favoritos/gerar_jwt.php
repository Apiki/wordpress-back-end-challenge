<?php
function base64UrlEncode($data)
{
    $urlSafeData = strtr(base64_encode($data), '+/', '-_');
    return rtrim($urlSafeData, '=');
}

function base64UrlDecode($data)
{
    $base64Data = strtr($data, '-_', '+/');
    return base64_decode($base64Data);
}

function encodeJwt($payload, $secretKey)
{
    $header = base64UrlEncode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
    $payload = base64UrlEncode(json_encode($payload));
    $signature = base64UrlEncode(hash_hmac('sha256', "$header.$payload", $secretKey, true));

    return "$header.$payload.$signature";
}

function decodeJwt($jwt, $secretKey)
{
    list($header, $payload, $signature) = explode('.', $jwt);

    $expectedSignature = base64UrlEncode(hash_hmac('sha256', "$header.$payload", $secretKey, true));

    if ($signature !== $expectedSignature) {
        throw new Exception('Token inválido.');
    }

    return json_decode(base64UrlDecode($payload), true);
}

$secretKey = '12345678'; // Segredo JWT

$payload = [
    'ID' => 1,
    'user_login' => 'admin'
];

try {
    $token = encodeJwt($payload, $secretKey);
    echo 'Token codificado: ' . $token . '<br>';

    $decoded = decodeJwt($token, $secretKey);
    echo 'Token decodificado: ' . print_r($decoded, true);
} catch (Exception $e) {
    echo 'Erro: ' . $e->getMessage();
}
?>
