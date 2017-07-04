<?php

// Chiave segreta
$chiaveSegreta = "TLGHTOWIZXQPTIZRALWKG"; // Sostituire con il valore fornito da CartaSi

// Controllo che si siano tutti i parametri di ritorno obbligatori per il calcolo del MAC
$requiredParams = array('codTrans', 'esito', 'importo', 'divisa', 'data', 'orario', 'codAut', 'mac');
foreach ($requiredParams as $param) {
    if (!isset($_REQUEST[$param])) {
        echo 'Paramentro mancante ' . $field;
        exit;
    }
}

// Calcolo MAC con parametri di ritorno
$macCalculated = sha1('codTrans=' . $_REQUEST['codTrans'] .
        'esito=' . $_REQUEST['esito'] .
        'importo=' . $_REQUEST['importo'] .
        'divisa=' . $_REQUEST['divisa'] .
        'data=' . $_REQUEST['data'] .
        'orario=' . $_REQUEST['orario'] .
        'codAut=' . $_REQUEST['codAut'] .
        $chiaveSegreta
);

// Verifico corrispondeza tra MAC calcolato e parametro mac di ritorno
if ($macCalculated != $_REQUEST['mac']) {
    echo 'Errore MAC: ' . $macCalculated . ' non corrisponde a ' . $_REQUEST['mac'];
    exit;
}

// nel caso in cui non ci siano errori gestisco il parametro esito
if($_REQUEST['esito'] == 'OK'){
    echo 'La transazione ' . $_REQUEST['codTrans'] . " è avvenuta con successo; codice autorizzazione: " . $_REQUEST['codAut']. "<br>Codice Contratto: " . $_REQUEST['num_contratto'];
} else {
    echo 'La transazione ' . $_REQUEST['codTrans'] . " è stata rifiutata; descrizione errore: " . $_REQUEST['messaggio'];
}

