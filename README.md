## wymagania dotyczące serwisu

Serwis realizuje obsługę katalogu produktów oraz koszyka. Klient serwisu powinien móc:

* dodać produkt do katalogu,
* usunąć produkt z katalogu,
* wyświetlić produkty z katalogu jako stronicowaną listę o co najwyżej 3 produktach na stronie,
* utworzyć koszyk,
* dodać produkt do koszyka, przy czym koszyk może zawierać maksymalnie 3 produkty,
* usunąć produkt z koszyka,
* wyświetlić produkty w koszyku, wraz z ich całkowitą wartością.

## Zadanie

Użytkownicy i testerzy serwisu zgłosili następujące problemy i prośby:

* Chcemy móc dodawać do koszyka ten sam produkt kilka razy, o ile nie zostanie przekroczony sumaryczny limit sztuk
  produktów.
* Najnowsze (ostatnio dodane) produkty powinny być dostępne na początkowych stronach listy produktów.
* Musimy mieć możliwość edycji produktów. Czasami w nazwach są literówki, innym razem cena jest nieaktualna.

## install

```shell
gh repo clone 021800rr/ara

cd ara

docker compose --file docker/docker-compose.yml --env-file BE/.env build --no-cache --pull
docker compose --file docker/docker-compose.yml --env-file BE/.env up -d

docker exec -it ara-php-dev bash
    cd /var/www/
    composer install
    
    symfony console cache:clear -n --env=dev
    symfony console doctrine:database:drop --force --env=dev || true
    symfony console doctrine:database:create
    symfony console doctrine:migrations:migrate -n --env=dev
    symfony console doctrine:fixtures:load -n --env=dev
    symfony console cache:clear -n --env=dev
    
    mkdir --parents tools/php-cs-fixer
    composer require --working-dir=tools/php-cs-fixer friendsofphp/php-cs-fixer

    php bin/console lexik:jwt:generate-keypair
    setfacl -R -m u:www-data:rX -m u:"$(whoami)":rwX config/jwt
    setfacl -dR -m u:www-data:rX -m u:"$(whoami)":rwX config/jwt

docker exec -it ara-postgres-dev bash 
    psql -U postgres -d postgres
        create database ara_dev_test;
        
docker exec -it ara-php-dev bash
    cd /var/www/
    make tests
```

api: http://localhost/api  
  
user: admin@example.com  pass: test  
user: editor@example.com pass: test  
user: user@example.com   pass: test  

### it should be ready now

optional environment shutdown
```    
docker compose --file docker/docker-compose.yml --env-file BE/.env down --remove-orphans
```
