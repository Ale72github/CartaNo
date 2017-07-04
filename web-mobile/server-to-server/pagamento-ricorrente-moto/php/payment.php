<?php

// apiKey e chave segreta
$apiKey = "payment_3438904"; // Sostituire con il valore fornito da CartaSi
$chiaveSegreta = "5T00eBviT16m80D2bW11bPx196K36Y562b16YEHC"; // Sostituire con il valore fornito da CartaSi

$requestUrl = "https://int-ecommerce.cartasi.it/ecomm/api/recurring/primoPagamentoMOTO";

$codTrans = "TESTPS_" . date('YmdHis');
$pan = "4000000000000002";
$cvv = "123";
$importo = "5000";
$divisa = "978";
$scadenza = '202012';
$numeroContratto =  "TEST_" . date('YmdHis');
$timeStamp = (time()) * 1000;

// Calcolo MAC
$mac = sha1('apiKey=' . $apiKey . 'numeroContratto=' . $numeroContratto . 'codiceTransazione=' . $codTrans . "importo=" . $importo . "divisa=" . $divisa . "pan=" . $pan . "cvv=" . $cvv . "scadenza=" . $scadenza . "timeStamp=" . $timeStamp . $chiaveSegreta);

$requestParams = array(
    'apiKey' => $apiKey,
    'numeroContratto' => $numeroContratto,
    'codiceGruppo' => "GRUPPOTEST",
    'codiceTransazione' => $codTrans,
    'importo' => $importo,
    'divisa' => $divisa,
    'pan' => $pan,
    'scadenza' => $scadenza,
    'cvv' => $cvv,
    'timeStamp' => (string) $timeStamp,
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

$dataEsito = json_decode($response, true);

if ($dataEsito['esito'] == "OK") { // Transazione andata a buon fine
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

