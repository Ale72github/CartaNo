# Pagamento ricorrente - Pagamento in un click

L'integrazione di servizi Recurring e OneClickPay consentono al cliente finale di memorizzare i dati della propria carta di credito sui sistemi CartaSi, ed utilizzarli successivamente per effettuare gli acquisti con un solo click o l'invio da parte dell'esercente di ricorrenze (ad esempio per servizi in abbonamento o fatturazione). A livello tecnico, la gestione di questi servizi si divide principalmente in 2 fasi:

## 1. Attivazione e/o primo pagamento
Va generata una prima transazione, assegnando un codice contratto che consente a CartaSi di salvare l'abbinamento tra l'utente e la carta di pagamento utilizzata, per gli acquisti successivi. Questa prima transazione può essere un vero pagamento, oppure solo una verifica della carta senza nessun addebito all'utente.

Nella situazione di sola registrazione con verifica carta la sequenza di API da utilizzare è la seguente:

* creaNonceVerificaCarta - per gestire l'autenticazione 3D-Secure
* verificaCarta3DS - per gestire la verifica validità della carta

Nella situazione di primo pagamento effettivo la sequenza di API da utilizzare è la seguente:

* creaNonce - per gestire l'autenticazione 3D-Secure
* primoPagamento3DS - per gestire il pagamento

## 2. Gestione delle ricorrenze/pagamenti sucessivi
La gestione dei pagamenti successivi tra i OneClick e i recurring a livello a livello tecnico sono analoghe e in pratica l'applicazione/sito dell'esercente deve utilizzare l'API:

* PagamentoRicorrente