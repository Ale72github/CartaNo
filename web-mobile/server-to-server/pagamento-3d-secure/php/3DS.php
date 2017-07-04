<?php

// apiKey e chiave segreta
$apiKey = "payment_3444161"; // Sostituire con il valore fornito da CartaSi
$chiaveSegreta = "64LZ58895GB4181287LY9t7S2278Q8W1KV2sT6C7"; // Sostituire con il valore fornito da CartaSi

$requestUrl = "https://int-ecommerce.cartasi.it/ecomm/api/paga/autenticazione3DS";
$merchantServerUrl = "https://" . $_SERVER['HTTP_HOST'] . "/xpay/S2S/payment/";

$codTrans = "TESTPS_" . date('YmdHis');
$importo = "5000";
$divisa = "978";
$scadenza = '202012';
$timeStamp = (time()) * 1000;

// Calcolo MAC
$mac = sha1('apiKey=' . $apiKey . 'codiceTransazione=' . $codTrans  . "divisa=" . $divisa .  'importo=' . $importo . "timeStamp=" . $timeStamp . $chiaveSegreta);

$requestParams = array(
    'apiKey' => $apiKey, 
    'pan' => "4000000000000002",
    'scadenza' => $scadenza,
    'cvv' => "123",
    'importo' => $importo,
    'divisa' => $divisa,
    'codiceTransazione' => $codTrans,
    'urlRisposta' => $merchantServerUrl . "payment.php",
    'timeStamp' => (string) $timeStamp,
    'mac' => $mac
);

$json = json_encode($requestParams);

$connection = curl_init();
if ($connection == false) {
    echo "connessione fallita!";
    exit;
}
curl_setopt($connection, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
curl_setopt($connection, CURLOPT_URL, $requestUrl);
curl_setopt($connection, CURLOPT_POST, 1);
curl_setopt($connection, CURLOPT_POSTFIELDS, $json);
curl_setopt($connection, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($connection, CURLINFO_HEADER_OUT, true);

$response = curl_exec($connection);
curl_close($connection);

$data3DS = json_decode($response, true);

if($data3DS['esito'] == "OK"){
    echo $data3DS['html']; 
} else {
    echo "Errore durante la verifica 3D-Secure. " . $data3DS['errore']['messaggio'];
    exit;
}


