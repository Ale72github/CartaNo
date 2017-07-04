<?php

// apiKey e chiave segreta - sostituire con i valori forniti da CartaSi
$apiKey = "payment_3444168";
$chiaveSegreta = "CsT830052L63QHNd1E351uh73272Q23h175650k9wU28T7EU1Hd6l156N5I2oBY6U7OW7kP34282C5965r8V0hpG72ojq5B58896G4Q6oXGc36a6z3Tn6J271B4N33p45C28369j7E025O2245GK7T5p1MNN5T25S05UJxCKH0TMc98fBQ66M2NxRDzrR66c7RG2K367D4xiV54X9kY592K5E3V1X1U01AO85P3n4z28eJIL13t8Ww3P28eg24y2";

$requestUrl = "https://int-ecommerce.cartasi.it/ecomm/api/recurring/pagamentoRicorrente";

// Parametri della richiesta
$numContratto = $_REQUEST['numContratto'];
if (!$numContratto) {
    echo "Inviare numContratto!";
    exit;
}

$codTrans = "TESTPS_" . date('YmdHis');
$importo = "5000";
$divisa = "978";
$scadenza = '202012';
$timeStamp = (time()) * 1000;

// Calcolo MAC
$mac = sha1('apiKey=' . $apiKey . 'numeroContratto=' . $numContratto . 'codiceTransazione=' . $codTrans . 'importo=' . $importo . "divisa=" . $divisa . "scadenza=" . $scadenza . "timeStamp=" . $timeStamp . $chiaveSegreta);

$requestParams = array(
    'apiKey' => $apiKey,
    'numeroContratto' => $numContratto,
    'codiceTransazione' => $codTrans,
    'importo' => $importo,
    'divisa' => $divisa,
    'scadenza' => $scadenza,
    'codiceGruppo' => 'GRUPPOTEST',
    'timeStamp' => (string) $timeStamp,
    'mac' => $mac,
    /* FACOLTATIVI */ 
    'mail' => "cardHolder@mail.it",
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

$dataEsito = json_decode($response, true);

if ($dataEsito['esito'] == "OK") { // Transazine andata a buon fine
    // Calcolo MAC con i parametri di ritorno
    $macCalculated = sha1('esito=' . $dataEsito['esito'] . 'idOperazione=' . $dataEsito['idOperazione'] . 'timeStamp=' . $dataEsito['timeStamp'] . $chiaveSegreta);
    if ($macCalculated != $dataEsito['mac']) {
        echo 'Errore MAC: ' . $macCalculated . ' non corrisponde a ' . $dataEsito['mac'];
        exit;
    }
    
    echo 'La transazione ' . $codTrans . " è avvenuta con successo; codice autorizzazione: " . $dataEsito['codiceAutorizzazione'];
} else { // Transazione rifiutata
    echo 'La transazione ' . $codTrans . " è stata rifiutata; descrizione errore: " . $dataEsito['errore']['messaggio'];
}