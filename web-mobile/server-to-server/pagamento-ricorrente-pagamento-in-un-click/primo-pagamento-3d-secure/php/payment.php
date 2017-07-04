<?php

// apiKey e chiave segreta
$apiKey = "payment_3438913"; // Sostituire con i valori forniti da CartaSi
$chiaveSegreta = "g8X8XJ3S75ekL9mi613F6346La0oPm27C8Jn4YG1"; // Sostituire con i valori forniti da CartaSi 

if($_REQUEST['esito'] != "OK"){
    echo "Esito 3D-Secure:" . $_REQUEST['esito'] . "-" . $_REQUEST['messaggio'];
    exit;
}

// Controllo che ci siano tutti i parametri obbligatori di ritorno
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

// Verifico corrispondenza tra MAC calcolato e parametro mac di ritorno
if ($macCalculated != $_REQUEST['mac']) {
    echo 'Errore MAC: ' . $macCalculated . ' non corrisponde a ' . $_REQUEST['mac'];
    exit;
}

// Dopo i controlli inizio il pagamento effettivo

$requestUrl = "https://int-ecommerce.cartasi.it/ecomm/api/recurring/primoPagamento3DS";

$codTrans = "TESTPS_" . date('YmdHis');
$importo = "5000";
$divisa = "978";
$numeroContratto =  "TEST_" . date('YmdHis');
$timeStamp = (time()) * 1000;

// Calcolo MAC
$mac = sha1('apiKey=' . $apiKey . 'numeroContratto=' . $numeroContratto . "codiceTransazione=" . $codTrans . "importo=" . $importo .  'divisa=' . $divisa . "xpayNonce=" . $_REQUEST['xpayNonce'] . "timeStamp=" . $timeStamp . $chiaveSegreta);

$requestParams = array(
    'apiKey' => $apiKey,
    'numeroContratto' => $numeroContratto,
    'codiceGruppo' => "GRUPPOTEST",
    'codiceTransazione' => $codTrans,
    'importo' => $importo,
    'divisa' => $divisa,
    'xpayNonce' => $_REQUEST['xpayNonce'],
    'timeStamp' => $timeStamp,
    'mac' => $mac,
    /* FACOLTATIVI */ 
    'scadenzaContratto' => "31/12/2020",
    'mail' => "cardHolder@mail.it",
    'descrizione' => "Descrizione Autorizzazione",
    'codiceFiscale' => "RSSNDR80A01H501L",
);

$json = json_encode($requestParams);
print_r($json);
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

$dataEsito = json_decode($response, true);

if ($dataEsito['esito'] == "OK") { // Transazione andata a buon fine
    // Calcolo MAC con i parametri di ritorno
    $macCalculated2 = sha1('esito=' . $dataEsito['esito'] . 'idOperazione=' . $dataEsito['idOperazione'] . 'timeStamp=' . $dataEsito['timeStamp'] . $chiaveSegreta);
    if ($macCalculated2 != $dataEsito['mac']) {
        echo 'Errore MAC: ' . $macCalculated2 . ' non corrisponde a ' . $dataEsito['mac'];
        exit;
    }
    echo 'La transazione ' . $codTrans . " è avvenuta con successo; codice autorizzazione: " . $dataEsito['codiceAutorizzazione'];
} else { // Transazione rifiutata
    echo 'La transazione ' . $codTrans . " è stata rifiutata; descrizione errore: " . $dataEsito['errore']['messaggio'];
}