
import java.security.MessageDigest;
import java.text.SimpleDateFormat;
import java.util.Date;
import org.apache.http.HttpEntity;
import org.apache.http.HttpResponse;
import org.apache.http.client.methods.HttpPost;
import org.apache.http.entity.StringEntity;
import org.apache.http.impl.client.CloseableHttpClient;
import org.apache.http.impl.client.HttpClientBuilder;
import org.apache.http.util.EntityUtils;
import org.json.JSONObject;

public class recurring {

  public static void main(String[] a) throws Exception {

    // apiKey e chiave segreta
    String APIKEY = "payment_3444168"; // Sostituire con il valore fornito da CartaSi
    String CHIAVESEGRETA = "CsT830052L63QHNd1E351uh73272Q23h175650k9wU28T7EU1Hd6l156N5I2oBY6U7OW7kP34282C5965r8V0hpG72ojq5B58896G4Q6oXGc36a6z3Tn6J271B4N33p45C28369j7E025O2245GK7T5p1MNN5T25S05UJxCKH0TMc98fBQ66M2NxRDzrR66c7RG2K367D4xiV54X9kY592K5E3V1X1U01AO85P3n4z28eJIL13t8Ww3P28eg24y2"; // Sostituire con il valore fornito da CartaSi

    SimpleDateFormat dateFormat = new SimpleDateFormat("yyyyMMddHHmmss");
    Date date = new Date();

    String requestUrl = "https://int-ecommerce.cartasi.it/ecomm/api/recurring/pagamentoRicorrente";

    // Parametri della richiesta
    String numContratto = "TESTPS_" + dateFormat.format(date);

    String codTrans = "TESTPS_" + dateFormat.format(date);
    String importo = "5000";
    String divisa = "978";
    String scadenza = "202012";
    String timeStamp = "" + System.currentTimeMillis();

    // Calcolo MAC
    String stringaMac = "apiKey=" + APIKEY
            + "numeroContratto=" + numContratto
            + "codiceTransazione=" + codTrans
            + "importo=" + importo
            + "divisa=" + divisa
            + "scadenza=" + scadenza
            + "timeStamp=" + timeStamp
            + CHIAVESEGRETA;
    
    String macCalculated = hashMac(stringaMac);

    JSONObject jsonParametriAggiuntivi = new JSONObject();
    jsonParametriAggiuntivi.put("mail", "cardHolder@mail.it");
    jsonParametriAggiuntivi.put("nome", "nome");
    jsonParametriAggiuntivi.put("cognome", "cognome");

    JSONObject json = new JSONObject();
    json.put("apiKey", APIKEY);
    json.put("numeroContratto", numContratto);
    json.put("codiceTransazione", codTrans);
    json.put("importo", importo);
    json.put("divisa", divisa);
    json.put("scadenza", scadenza);
    json.put("codiceGruppo", "GRUPPOTEST");
    json.put("parametriAggiuntivi", jsonParametriAggiuntivi);
    json.put("timeStamp", timeStamp);
    json.put("mac", macCalculated);

    CloseableHttpClient httpClient = HttpClientBuilder.create().build();

    try {
      HttpPost request = new HttpPost(requestUrl);
      StringEntity params = new StringEntity(json.toString());
      request.addHeader("content-type", "application/json");
      request.setEntity(params);
      HttpResponse response = httpClient.execute(request);

      HttpEntity entity = response.getEntity();
      String responseString = EntityUtils.toString(entity, "UTF-8");
      JSONObject responseObj = new JSONObject(responseString);

      if ("OK".equals(responseObj.getString("esito"))) {
        // Calcolo MAC con parametri di ritorno
        String macResponse = "esito=" + responseObj.getString("esito") + "idOperazione=" + responseObj.getString("idOperazione") + "timeStamp=" + responseObj.getString("timeStamp") + CHIAVESEGRETA;
        String macResponseCalculated =  hashMac(macResponse);

        // Verifico corrispondenza tra MAC calcolato e parametro mac in ingresso  
        if(!macResponseCalculated.equals(responseObj.getString("mac"))) {                
          throw new Exception("Errore MAC: " + macResponseCalculated + " non corrisponde a " + responseObj.getString("mac"));
        }

        System.out.println("La transazione " + codTrans + " è avvenuta con successo; codice autorizzazione: " +  responseObj.getString("codiceAutorizzazione"));
      } else {
        System.out.println("La transazione " + codTrans + " è stata rifiutata; descrizione errore: " + responseObj.getJSONObject("errore").getString("messaggio"));
      }
    } catch (Exception ex) {
        System.err.println(ex.getMessage());
    } finally {
        httpClient.close();
    }
  }

  public static String hashMac(String stringaMac) throws Exception {
    MessageDigest digest = MessageDigest.getInstance("SHA-1");
    byte[] in = digest.digest(stringaMac.getBytes("UTF-8"));

    final StringBuilder builder = new StringBuilder();
    
    for (byte b : in) {
      builder.append(String.format("%02x", b)); 
    }

    return builder.toString();
  }
  
}
