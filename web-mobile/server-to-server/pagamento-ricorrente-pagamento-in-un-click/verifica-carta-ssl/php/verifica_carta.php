<?php

// apiKey e chiave segreta
$apiKey = "payment_3444161"; // Sostituire con il valore fornito da CartaSi
$chiaveSegreta = "64LZ58895GB4181287LY9t7S2278Q8W1KV2sT6C7"; // Sostituire con il valore fornito da CartaSi

$requestUrl = "https://int-ecommerce.cartasi.it/ecomm/api/recurring/verificaCartaSSL";

$pan = "4000000000000002"; 
$scadenza = '202012';
$cvv = "123";
$numeroContratto =  "TEST_" . date('YmdHis');
$timeStamp = (time()) * 1000;

// Calcolo MAC
$mac = sha1('apiKey=' . $apiKey . 'pan=' . $pan  . "scadenza=" . $scadenza .  'cvv=' . $cvv . "timeStamp=" . $timeStamp . $chiaveSegreta);

$requestParams = array(
    'apiKey' => $apiKey,
    'pan' => $pan,
    'scadenza' => $scadenza,
    'cvv' => $cvv,
    'numeroContratto' => $numeroContratto,
    'codiceGruppo' => "GRUPPOTEST",
    'timeStamp' => $timeStamp,
    'mac' => $mac,
    /* FACOLTATIVI */ 
    'scadenzaContratto' => "31/12/2020",
    'mail' => "cardHolder@mail.it",
    'descrizione' => "Descrizione Autorizzazione",
    'codiceFiscale' => "RSSNDR80A01H501L",
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

$dataSSL = json_decode($response, true);

if($dataSSL['esito'] == "OK"){
    // Calcolo MAC con i parametri di ritorno
    $macCalculated2 = sha1('esito=' . $dataSSL['esito'] . 'idOperazione=' . $dataSSL['idOperazione'] . 'timeStamp=' . $dataSSL['timeStamp'] . $chiaveSegreta);
    if ($macCalculated2 != $dataSSL['mac']) {
        echo 'Errore MAC: ' . $macCalculated2 . ' non corrisponde a ' . $dataVerifica['mac'];
        exit;
    } else {
        echo 'Contratto registrato correttamente; N.contratto: ' . $numeroContratto;
    }
} else {
    echo "Errore durante la verifica SSL. " . $dataSSL['errore']['messaggio'];
    exit;
}

