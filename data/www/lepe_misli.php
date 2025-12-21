<?php
function fetchQuote()
{
    $url = "https://zenquotes.io/api/random";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode == 200 && $response) {
        $data = json_decode($response, true);

        if (isset($data[0]['q']) && isset($data[0]['a'])) {
            return [
                'quote' => $data[0]['q'],
                'author' => $data[0]['a']
            ];
        }
    }

    return null;
}

if (isset($_GET['ajax'])) {
    header('Content-Type: application/json');
    echo json_encode(fetchQuote());
    exit;
}
