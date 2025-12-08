<?php

function base64UrlEncode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64UrlDecode($data) {
    return base64_decode(strtr($data, '-_', '+/'));
}

function generateJWT($payload, $secret = 'your-secret-key') {
    $header = ['alg' => 'HS256', 'typ' => 'JWT'];
    $headerEncoded = base64UrlEncode(json_encode($header));
    $payloadEncoded = base64UrlEncode(json_encode($payload));

    $signature = hash_hmac('sha256', "$headerEncoded.$payloadEncoded", $secret, true);
    $signatureEncoded = base64UrlEncode($signature);

    return "$headerEncoded.$payloadEncoded.$signatureEncoded";
}

function verifyJWT($jwt, $secret = 'your-secret-key') {
    $parts = explode('.', $jwt);

    if (count($parts) !== 3) return false;

    list($headerEncoded, $payloadEncoded, $signatureEncoded) = $parts;

    $validSignature = base64UrlEncode(hash_hmac('sha256', "$headerEncoded.$payloadEncoded", $secret, true));

    if ($validSignature !== $signatureEncoded) return false;

    $payload = json_decode(base64UrlDecode($payloadEncoded), true);

    if (isset($payload['exp']) && time() > $payload['exp']) {
        return false;
    }

    return $payload;
}
