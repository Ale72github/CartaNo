<?php

// apiKey e chiave segreta
$apiKey = "payment_3444161"; // Sostituire con il valore fornito da CartaSi
$chiaveSegreta = "64LZ58895GB4181287LY9t7S2278Q8W1KV2sT6C7"; // Sostituire con il valore fornito da CartaSi

if($_REQUEST['esito'] != "OK"){
    echo "Esito 3D-Secure:" . $_REQUEST['esito'] . "-" . $_REQUEST['messaggio'];
    exit;
}

// Controllo che si siano tutti i parametri obbligatori di ritorno
$requiredParams = array('esito', 'idOperazione', 'xpayNonce', 'timeStamp', 'mac');
foreach ($requiredParams as $param) {
    if (!isset($_REQUEST[$param])) {
        echo 'Paramentro mancante ' . $field;
        exit;
    }
}

// Calcolo MAC con i parametri di ritorno
$macCalculated = sha1('esito=' . $_REQUEST['esito'] .
        'idOperazione=' . $_REQUEST['idOperazione'] .
        'xpayNonce=' . $_REQUEST['xpayNonce'] .
        'timeStamp=' . $_REQUEST['timeStamp'] .
        $chiaveSegreta
);

// Verifico corrispondeza tra MAC calcolato e parametro mac di ritorno
if ($macCalculated != $_REQUEST['mac']) {
    echo 'Errore MAC: ' . $macCalculated . ' non corrisponde a ' . $_REQUEST['mac'];
    exit;
}

// Dopo i controlli il pagamento effettivo

$requestUrl = "https://int-ecommerce.cartasi.it/ecomm/api/paga/paga3DS";

$codTrans = "TESTPS_" . date('YmdHis');
$importo = "5000";
$divisa = "978";
$timeStamp = (time()) * 1000;

// Calcolo MAC
$mac = sha1('apiKey=' . $apiKey . 'codiceTransazione=' . $codTrans  . "importo=" . $importo .  'divisa=' . $divisa . "xpayNonce=" . $_REQUEST['xpayNonce'] . "timeStamp=" . $timeStamp . $chiaveSegreta);

$requestParams = array(
    'apiKey' => $apiKey,
    'codiceTransazione' => $codTrans,
    'importo' => $importo,
    'divisa' => $divisa,
    'xpayNonce' => $_REQUEST['xpayNonce'],
    'timeStamp' => $timeStamp,
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

$response = curl_exec($connection);
curl_close($connection);

$dataVerifica = json_decode($response, true);

print_r($dataVerifica);

if ($dataVerifica['esito'] == "OK") { // Transazione andata a buon fine
    // Calcolo MAC con parametri di ritorno
    $macCalculated2 = sha1('esito=' . $dataVerifica['esito'] . 'idOperazione=' . $dataVerifica['idOperazione'] . 'timeStamp=' . $dataVerifica['timeStamp'] . $chiaveSegreta);
    if ($macCalculated2 != $dataVerifica['mac']) {
        echo 'Errore MAC: ' . $macCalculated2 . ' non corrisponde a ' . $dataVerifica['mac'];
        exit;
    }
    
    echo 'La transazione ' . $codTrans . " è avvenuta con successo; codice autorizzazione: " . $dataVerifica['codiceAutorizzazione'];
} else { // Transazione rifiutata
    echo 'La transazione ' . $codTrans . " è stata rifiutata; descrizione errore: " . $dataVerifica['errore']['messaggio'];
}