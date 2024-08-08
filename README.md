I decided to write it from scratch.  
The superiority of API Platform and Swagger, etc., is clear.  
Unnecessary inclusion of queues working between local tables only adds extra load.  
In the spirit of 'enjoying a splendidly idle afternoon': I have just written two projects in API. 
From one (June 24), I can fetch Users. From the other (July 24), Products (requiring only light refactoring).

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

### it should be ready now!!

optional environment shutdown
```    
docker compose --file docker/docker-compose.yml --env-file BE/.env down --remove-orphans
```

## how to test

```shell
cd ara
docker compose --file docker/docker-compose.yml --env-file BE/.env up -d

docker exec -it ara-php-dev bash
    cd /var/www/
    make tests
```

## how to dev

```shell
cd ara
docker compose --file docker/docker-compose.yml --env-file BE/.env up -d
```

api: http://localhost/api  

## how to reset 

```shell
cd ara
docker compose --file docker/docker-compose.yml --env-file BE/.env down --remove-orphans && \
docker compose --file docker/docker-compose.yml --env-file BE/.env build --no-cache --pull && \
docker compose --file docker/docker-compose.yml --env-file BE/.env up -d
```

## how to reset database

```shell
cd ara
docker exec -it ara-php-dev bash
    cd /var/www/
    php bin/console cache:pool:clear cache.global_clearer
    php bin/console --env=dev doctrine:database:drop --force
    php bin/console --env=dev doctrine:database:create
    php bin/console --env=dev --no-interaction doctrine:migrations:migrate
    php bin/console --env=dev doctrine:fixtures:load -q
    php bin/console cache:pool:clear cache.global_clearer
```
## ORG:

# Audioteka: zadanie rekrutacyjne

## Instalacja

// ...

Przykładowe zapytania (jak komunikować się z serwisem) znajdziesz w `requests.http``./requests.http`.

// ...

## Oryginalne wymagania dotyczące serwisu

Serwis realizuje obsługę katalogu produktów oraz koszyka. Klient serwisu powinien móc:

* dodać produkt do katalogu,
* usunąć produkt z katalogu,
* wyświetlić produkty z katalogu jako stronicowaną listę o co najwyżej 3 produktach na stronie,
* utworzyć koszyk,
* dodać produkt do koszyka, przy czym koszyk może zawierać maksymalnie 3 produkty,
* usunąć produkt z koszyka,
* wyświetlić produkty w koszyku, wraz z ich całkowitą wartością.

Kod, który masz przed sobą, stara się implementować te wymagania z pomocą `Symfony 6.0`.

## Zadanie

Użytkownicy i testerzy serwisu zgłosili następujące problemy i prośby:

* Chcemy móc dodawać do koszyka ten sam produkt kilka razy, o ile nie zostanie przekroczony sumaryczny limit sztuk produktów. Teraz to nie działa.
* Limit koszyka nie zawsze działa. Wprawdzie, gdy podczas naszych testów dodajemy czwarty produkt do koszyka to dostajemy komunikat `Cart is full.`, ale pomimo tego i tak niektóre koszyki w bazie danych mają po cztery produkty.
* Najnowsze (ostatnio dodane) produkty powinny być dostępne na początkowych stronach listy produktów.
* Musimy mieć możliwość edycji produktów. Czasami w nazwach są literówki, innym razem cena jest nieaktualna.

Prosimy o naprawienie / implementację.

PS. Prawdziwym celem zadania jest oczywiście kawałek kodu, który możemy ocenić, a potem porozmawiać o nim w czasie interview "twarzą w twarz". Przy czym pamiętaj, że liczy się nie tylko napisany kod PHP, ale także umiejętność przedstawienia czytelnego rozwiązania, użycia odpowiednich narzędzi (chociażby systemu wersjonowania), udowodnienia poprawności rozwiązania (testy) itd.

To Twoja okazja na pokazanie umiejętności, więc jeśli uważasz, że w kodzie jest coś nie tak, widzisz więcej błędów, coś powinno być zaimplementowane inaczej, możesz do listy zadań dodać opcjonalny refactoring, albo krótko wynotować swoje spostrzeżenia (może przeprowadzić coś w rodzaju code review?).

Powodzenia!

