`docker compose up`
`docker exec -ti ttt-php-gp composer install`
`docker exec -ti ttt-php-gp bin/console doctrine:migrations:migrate --no-interaction`

Inizio gioco:

`curl --location --request POST 'http://127.0.0.1:8080/api/game/start'
--header 'Content-Type: application/json'
--header 'Accept: application/json'`

Copiare id ricevuto ed utilizzarlo nelle richieste successive al posto di GAMEID:

`curl --location --request PUT 'http://127.0.0.1:8080/api/game/GAMEID/advance' --header 'Content-Type: application/json' --header 'Accept: application/json' --data-raw '{
"position": 8,
"player": 1
}'`

`curl --location --request PUT 'http://127.0.0.1:8080/api/game/GAMEID/advance' --header 'Accept: application/json' --data-raw '{
"position": 3,
"player": 2
}'`

`curl --location --request PUT 'http://127.0.0.1:8080/api/game/GAMEID/advance' --header 'Content-Type: application/json' --header 'Accept: application/json' --data-raw '{
"position": 4,
"player": 1
}'`

`curl --location --request PUT 'http://127.0.0.1:8080/api/game/GAMEID/advance' --header 'Content-Type: application/json' --header 'Accept: application/json' --data-raw '{
"position": 5,
"player": 2
}'`

`curl --location --request PUT 'http://127.0.0.1:8080/api/game/GAMEID/advance' --header 'Content-Type: application/json' --header 'Accept: application/json' --data-raw '{
"position": 0,
"player": 1
}'`

