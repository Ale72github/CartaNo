<?php

// Chiave segreta
$chiaveSegreta = "RMm54dd285dBIPsc703P628hgo72171K380g6in5"; // Sostituire con il valore fornito da CartaSi

$requestUrl = "https://int-ecommerce.cartasi.it/ecomm/api/hostedPayments/pagaNonce";

$requestParams = array(
    'apiKey' => $_REQUEST['alias'],
    'codiceTransazione' => $_REQUEST['codiceTransazione'],
    'importo' => $_REQUEST['importo'],
    'divisa' => $_REQUEST['divisa'],
    'xpayNonce' => $scadenza,
    'timeStamp' => $_REQUEST['timeStamp'],
    'mac' => $_REQUEST['mac']
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
    // Calcolo MAC con i parametri di ritorno
    $macCalculated = sha1('esito=' . $dataVerifica['esito'] . 'idOperazione=' . $dataVerifica['idOperazione'] . 'timeStamp=' . $dataVerifica['timeStamp'] . $chiaveSegreta);
    if ($macCalculated != $dataVerifica['mac']) {
        echo 'Errore MAC: ' . $macCalculated . ' non corrisponde a ' . $dataVerifica['mac'];
        exit;
    }
    
    echo 'La transazione ' . $codTrans . " è avvenuta con successo; codice autorizzazione: " . $dataVerifica['codiceAutorizzazione'];
} else { // Transazione rifiutata
    echo 'La transazione ' . $codTrans . " è stata rifiutata; descrizione errore: " . $dataVerifica['errore']['messaggio'];
}
