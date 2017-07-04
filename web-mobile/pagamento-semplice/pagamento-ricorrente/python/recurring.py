# -*- coding: utf-8 -*-
import sys
if sys.version_info >= (3,):
    from urllib.parse import urlencode
else:
    from urllib import urlencode
import hashlib
import datetime
import time
import requests

requestUrl = "https://int-ecommerce.cartasi.it/ecomm/api/recurring/pagamentoRicorrente";

# Alias e chiave segreta
APIKEY = "payment_3444168" # Sostituire con il valore fornito da CartaSi
CHIAVESEGRETA = "CsT830052L63QHNd1E351uh73272Q23h175650k9wU28T7EU1Hd6l156N5I2oBY6U7OW7kP34282C5965r8V0hpG72ojq5B58896G4Q6oXGc36a6z3Tn6J271B4N33p45C28369j7E025O2245GK7T5p1MNN5T25S05UJxCKH0TMc98fBQ66M2NxRDzrR66c7RG2K367D4xiV54X9kY592K5E3V1X1U01AO85P3n4z28eJIL13t8Ww3P28eg24y2" # Sostituire con il valore fornito da CartaSi

# Parametri della richiesta
numContratto = "TESTPS_" + datetime.datetime.today().strftime('%Y%m%d%H%M%s')

codTrans = "TESTPS_" + datetime.datetime.today().strftime('%Y%m%d%H%M%s')
importo = "5000"
divisa = "978"
scadenza = '202012'
timeStamp = (int(time.time())) * 1000

# Calcolo MAC
mac_str = 'apiKey=' + APIKEY + \
    'numeroContratto=' + numContratto + \
    'codiceTransazione=' + codTrans + \
    'importo=' + importo + \
    "divisa=" + divisa + \
    "scadenza=" + scadenza + \
    "timeStamp=" + str(timeStamp) + \
     CHIAVESEGRETA
mac =  hashlib.sha1(mac_str.encode('utf8')).hexdigest()

requestParams = {
    'apiKey': APIKEY,
    'numeroContratto': numContratto,
    'codiceTransazione': codTrans,
    'importo': importo,
    'divisa': divisa,
    'scadenza': scadenza,
    'codiceGruppo': 'GRUPPOTEST',
    'parametriAggiuntivi': {
        "mail": "cardHolder@mail.it",
        "nome": "nome",
        "cognome": "cognome"
    },
    'timeStamp': str(timeStamp),
    'mac': mac
}
import json
print(json.dumps(requestParams, sort_keys=True, indent=4, separators=(',', ': ')))

response  = requests.post(requestUrl,json=requestParams,headers={'Content-Type':'application/json'})
try:
    response_data = response.json()

    if response_data['esito'] == "OK": # Transazione andata a buon fine
        # calcolo MAC con i parametri di ritorno
        macResponse = 'esito=' + response_data['esito'] + 'idOperazione=' + response_data['idOperazione'] + 'timeStamp=' + response_data['timeStamp'] + CHIAVESEGRETA
        macCalculated =  hashlib.sha1(macResponse.encode('utf8')).hexdigest()

        if macCalculated != response_data['mac']:
            raise ValueError('Errore MAC: ' + macCalculated + ' non corrisponde a ' + response_data['mac'])

        print('La transazione ' + codTrans + " è avvenuta con successo; codice autorizzazione: " + response_data['codiceAutorizzazione'])
    else: # Transazione rifiutata
        print('La transazione ' + codTrans + " è stata rifiutata; descrizione errore: " + response_data['errore']['messaggio'])

except Exception as e:
    print(response)
    print(response.content)
