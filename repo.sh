# Chiamare lo script come ./repo.sh nome-repository-iplus-da-cui-copiare-i-file messaggio-del-commit

echo Creo cartella temporanea
mkdir temporanea 

echo Mi porto al suo interno
cd temporanea

echo Copio la repository CartaSi
git clone git@github.com:Ale72github/CartaNo.git
# inserire se settata la password per la chiave ssh

echo Copio la nostra repository
git clone git@gitlab.iplusservice.it:cartasi/$1.git
# inserire se settata la password per la chiave ssh

echo Rimuovo git dalla nostra repository
rm -rf $1/.git

echo Copio e sovrascrivo il contenuto della nostra repository dentro quella CartaSi
yes | cp -rf $1/* CartaNo

echo Mi porto nella cartella della repository CartaSi
cd CartaNo

echo Setto username
git config user.name "CartaSi"

echo Setto email
git config user.email "tech.ecommerce@cartasi.it"

echo Aggiungo i file modificati all\'head
git add *

echo Faccio il commit
git commit -m $2

echo Faccio il push
git push origin master
# inserire se settata la password per la chiave ssh

echo Mi porto nella cartella di partenza
cd ../..

echo Elimino la cartella temporanea
rm -rf temporanea