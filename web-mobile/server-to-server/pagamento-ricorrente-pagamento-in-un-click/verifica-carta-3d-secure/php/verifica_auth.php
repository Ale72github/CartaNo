<?php

// apiKey e chiave segreta
$apiKey = "payment_3444161";
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

// Calcolo MAC
$macCalculated = sha1('esito=' . $_REQUEST['esito'] .
        'idOperazione=' . $_REQUEST['idOperazione'] .
        'xpayNonce=' . $_REQUEST['xpayNonce'] .
        'timeStamp=' . $_REQUEST['timeStamp'] .
        $chiaveSegreta
);

// Verifico corrispondenza tra MAC calcolato e parametro mac di ritorno
if ($macCalculated != $_REQUEST['mac']) {
    echo '3DS errore MAC: ' . $macCalculated . ' NON CORRISPONDENTE A ' . $_REQUEST['mac'];
    exit;
}

// Dopo i controlli inizio il pagamento effettivo

$requestUrl = "https://int-ecommerce.cartasi.it/ecomm/api/recurring/verificaCarta3DS";

$xpayNonce = $_REQUEST['xpayNonce'];
$timeStamp = (time()) * 1000;

// Calcolo MAC
$mac = sha1('apiKey=' . $apiKey . 'xpayNonce=' . $xpayNonce . 'timeStamp=' . $timeStamp . $chiaveSegreta);

$requestParams = array(
    'apiKey' => $apiKey,
    'xpayNonce' => $xpayNonce,
    'numeroContratto' => "TEST_" . date('YmdHis'),
    'codiceGruppo' => "GRUPPOTEST",
    'timeStamp' => $timeStamp,
    'mac' => $mac,
    /* FACOLTATIVI */
    'scadenzaContratto' => "31/12/2020",
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

$response = curl_exec($connection);
curl_close($connection);

$dataVerifica = json_decode($response, true);

if ($dataVerifica['esito'] == "OK") { // Transazione andata a buon fine
    // Calcolo MAC con i parametri di ritorno
    $macCalculated2 = sha1('esito=' . $dataVerifica['esito'] . 'idOperazione=' . $dataVerifica['idOperazione'] . 'timeStamp=' . $dataVerifica['timeStamp'] . $chiaveSegreta);
    if ($macCalculated2 != $dataVerifica['mac']) {
        echo 'Errore MAC: ' . $macCalculated2 . ' non corrisponde a ' . $dataVerifica['mac'];
        exit;
    }
    
    echo "La verifica è avvenuta con successo; codice operazione: " . $dataVerifica['idOperazione'];
} else { // Transazione rifiutata
    echo "La verifica è fallita; descrizione errore: " . $dataVerifica['errore']['messaggio'];
}