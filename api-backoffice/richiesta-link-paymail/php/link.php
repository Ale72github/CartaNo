<?php

$connection = curl_init();

if ($connection) {

    $requestURL = "https://int-ecommerce.cartasi.it/"; // URL
    $requestURI = "ecomm/api/bo/richiestaPayMail"; // URI
    // Parametri calcolo MAC
    $apiKey = "<ALIAS>"; // Alias fornito da CartaSi
    $chiaveSegreta = "<CHIAVE SEGRETA PER CALCOLO MAC>"; // Chiave segreta fornita da CartaSi
    $codiceTransazione = "APIBO_" . date('YmdHis'); // Codice della transazione
    $importo = 5000; // 5000 = 50,00 EURO (indicare la cifra in centesimi)
    $timeout = 4; // Durata in ore del link di pagamento che verrà generato 
    $url = "https://" . filter_input(INPUT_SERVER, 'HTTP_HOST') . "/esito.php"; // URL dove viene rimandato il cliente al termine del pagamento
    $timeStamp = (time()) * 1000;

    // Calcolo MAC
    $mac = sha1('apikey ' . $apiKey . 'codiceTransazione=' . $codiceTransazione . 'utente=' . $utente . "importo=" . $importo . "timeStamp=" . $timeStamp . $chiaveSegreta);

    // Parametri
    $parametri = array(
        // Obbligatori
        'apikey ' => $apiKey,
        'importo' => $importo,
        'timeout ' => $timeout,
        'codiceTransazione ' => $codiceTransazione,
        'url' => $url,
        'mac' => $mac,
        'timestamp ' => $timeStamp,
            // Facoltativi
            /* 'parametriAggiuntivi' => array(
              'mail' => "mail@cliente.it",
              'languageId' => "ITA",
              'descrizione' => "Prova di pagamento",
              'session_id' => session_id(),
              'Note1' => "NOTA 1",
              'Note2' => "NOTA 2",
              'Note3' => "NOTA 3",
              'OPTION_CF' => "RSSMRA74D22A001Q",
              'selectedcard' => "VISA",
              'TCONTAB' => "D",
              'infoc' => "Info su pagamento per compagnia",
              'infob' => "Info su pagamento per banca",
              'modo_gestione_consegna' => "completo"
              ) */
    );

    curl_setopt_array($connection, array(
        CURLOPT_URL => $requestURL . $requestURI,
        CURLOPT_HTTPHEADER => array('Content-Type: application/json'),
        CURLOPT_POST => 1,
        CURLOPT_POSTFIELDS => json_encode($parametri),
        CURLOPT_RETURNTRANSFER => 1,
        CURLINFO_HEADER_OUT => true,
        CURLOPT_SSL_VERIFYPEER => 0
    ));

    $json = curl_exec($connection);

    curl_close($connection);

    // Decodifico risposta
    $risposta = json_decode($json, true);

    // Controllo JSON di risposta
    if (json_last_error() === JSON_ERROR_NONE) {

        $MACrisposta = sha1('esito=' . $risposta['esito'] . 'idOperazione=' . $risposta['idOperazione'] . 'timeStamp=' . $risposta['timeStamp'] . $chiaveSegreta);

        // Controllo MAC di risposta
        if ($risposta['mac'] == $MACrisposta) {

            // Controllo esito
            if ($risposta['esito'] == 'OK') {
                echo 'Operazione n. ' . $risposta['idOperazione'] . ' eseguita';
            } else {
                echo 'Operazione n. ' . $risposta['idOperazione'] . ' non eseguita. esito ' . $risposta['esito'] . '<br><br>' . json_encode($risposta['errore']);
            }
        } else {
            echo 'Errore nel calcolo del MAC di risposta';
        }
    } else {
        echo 'Errore nella lettura del JSON di risposta';
    }
} else {
    echo "Impossibile connettersi!";
}

// apiKey e chiave segreta
$apikey = "payment_3438910"; // Sostituire con il valore fornito da CartaSi
$chiaveSegreta = "b31SS2R72c7j6772J85E479FT6bIH3Ym387iHkNY"; // Sostituire con il valore fornito da CartaSi

$requestUrl = "https://coll-ecommerce.cartasi.it/ecomm/api/bo/richiestaPayMail";
$merchantServerUrl = "https://" . $_SERVER['HTTP_HOST'] . "/xpay/php/api_bo/link_paymail/";

$importo = "5000";
$utente = "";
$codTrans = "APIBO_" . date('YmdHis');
$timeStamp = (time()) * 1000;

// Calcolo MAC
$mac = sha1('apikey ' . $apikey . 'codiceTransazione=' . $codTrans . 'utente=' . $utente . "importo=" . $importo . "timeStamp=" . $timeStamp . $chiaveSegreta);

//Param Obbligatori
$requestParams = array(
    'apiKey ' => $apikey,
    'importo' => $importo,
    'timeout ' => 4,
    'codiceTransazione ' => $codTrans,
    'url' => $merchantServerUrl . "esito.php",
    'mac' => $mac,
    'timestamp ' => $timeStamp,
    /* FACOLTATIVI */
    'parametriAggiuntivi' => array(
    /* 'mail' => "mail@cliente.it",
      'languageId' => "ITA",
      'descrizione' => "Prova di pagamento",
      'session_id' => session_id(),
      'Note1' => "NOTA 1",
      'Note2' => "NOTA 2",
      'Note3' => "NOTA 3",
      'OPTION_CF' => "RSSMRA74D22A001Q",
      'selectedcard' => "VISA",
      'TCONTAB' => "D",
      'infoc' => "Info su pagamento per compagnia",
      'infob' => "Info su pagamento per banca",
      'modo_gestione_consegna' => "completo" */
    )
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

$dataPayMail = json_decode($response, true);

if ($dataPayMail['esito'] == "OK") {
    // Calcolo MAC con i parametri di ritorno
    $macCalculated2 = sha1('esito=' . $dataPayMail['esito'] . 'idOperazione=' . $dataPayMail['idOperazione'] . 'timeStamp=' . $dataPayMail['timeStamp'] . $chiaveSegreta);
    if ($macCalculated2 != $dataPayMail['mac']) {
        echo 'S2S errore MAC: ' . $macCalculated2 . ' NON CORRISPONDENTE A ' . $dataPayMail['mac'];
        exit;
    } else {
        echo "Link generato correttamente: " . $dataPayMail['payMailUrl'] . "<br>";
        echo "<a href ='" . $dataPayMail['payMailUrl'] . "'>VAI AL LINK</a>";
    }
} else {
    echo "Errore durante la richiesta Link. " . $dataPayMail['errore']['messaggio'];
    exit;
}