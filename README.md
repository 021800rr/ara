## install

```shell
gh repo clone 021800rr/cf

cd cf

docker compose --file docker/docker-compose.yml --env-file BE/.env.dev build --no-cache --pull
docker compose --file docker/docker-compose.yml --env-file BE/.env.dev up -d

docker exec -it cf-php-dev bash
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

docker exec -it cf-postgres-dev bash 
    psql -U postgres -d postgres
        create database cf_dev_test;
        
docker exec -it cf-php-dev bash
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
docker compose --file docker/docker-compose.yml --env-file BE/.env.dev down --remove-orphans
```

## how to test

```shell
cd cf
docker compose --file docker/docker-compose.yml --env-file BE/.env.dev up -d

docker exec -it cf-php-dev bash
    cd /var/www/
    make tests
```

## how to dev

```shell
cd cf
docker compose --file docker/docker-compose.yml --env-file BE/.env.dev up -d
```

api: http://localhost/api  

## how to reset 

```shell
cd cf
docker compose --file docker/docker-compose.yml --env-file BE/.env.dev down --remove-orphans && \
docker compose --file docker/docker-compose.yml --env-file BE/.env.dev build --no-cache --pull && \
docker compose --file docker/docker-compose.yml --env-file BE/.env.dev up -d
```

## how to reset database

```shell
cd cf
docker exec -it cf-php-dev bash
    cd /var/www/
    php bin/console cache:pool:clear cache.global_clearer
    php bin/console --env=dev doctrine:database:drop --force
    php bin/console --env=dev doctrine:database:create
    php bin/console --env=dev --no-interaction doctrine:migrations:migrate
    php bin/console --env=dev doctrine:fixtures:load -q
    php bin/console cache:pool:clear cache.global_clearer
```
## ORG:

# Zadanie rekrutacyjne

Chcielibyśmy abyś, jako formę prezentacji Twoich umiejętności, zrefaktoryzował oraz rozbudował już istniejącą aplikację.
Jest ona już wstępnie skonfigurowana i można ją uruchomić za pomocą docker-a lub też w dowolny inny sposób,
który preferujesz (docker ma Ci ułatwić pracę, ale jeżeli tak nie będzie, nie musisz go używać).
Aplikacja uruchamiana jest w PHP 8.x + SF 7.x

UWAGA! Serwer www używany w aplikacji to Cady

### Uruchomienie apliakcji

1. If not already done, [install Docker Compose](https://docs.docker.com/compose/install/) (v2.10+)
2. Run `docker compose build --no-cache` to build fresh images
3. Run `docker compose up --pull always -d --wait` to set up and start a fresh Symfony project
4. Open `https://localhost` in your favorite web browser and [accept the auto-generated TLS certificate](https://stackoverflow.com/a/15076602/1352334)
5. Run `docker compose down --remove-orphans` to stop the Docker containers.

### Uruchomienie bazy danych

Inicjalizację tabeli i wypełnienie przykładowymi danymi można wykonać za pomocą DoctrineMigrations przez polecenie:
`./bin/console doctrine:migrations:migrate`

W aplikacji uruchomionej przez `docker-compose` należy wykonać to polecenie z wnętrza kontenera aplikacji.

### Użycie

Obecnie aplikacja posiada jeden endpoint,
który przyjmuje z GET-a 3 parametry: `id`, `name` oraz `price` i na ich podstawie tworzy nowy rekord w tabeli `product`.

## Zadania dla Ciebie
Po pierwsze kod jest brzydki i nie do końca działa zgodnie z oczekiwaniami, więc wymaga refaktoryzacji.

Po drugie, chcemy abyś dodał możliwość:
- podania cen w 2 różnych walutach (dowolnych)
- dodania długiego opisu (do 10000 znaków)
- kasowania produktów po id
- wyświetlania produktów po id
- listowania produktów (tutaj wystarczy jak dodasz limit, nie musisz robić stronicowania)
- możesz użyć formaterów, które pomogą Ci w generowaniu spójnej odpowiedzi
- możesz przygotować walidację danych wejściowych, w sposób jaki uważasz za słuszny

#### Dodatkowym plusem będzie, jeżeli wykonasz poniższe zadania, ale nie jest to wymagane:
- implementacja własnych błędów, które będą zwracane w odpowiedzi
- dodać użytkowników i uprawnienia, a także autoryzację do endpointów
- asynchroniczność, wraz z poprawną konfiguracją messenger-a
- możesz również wydzielić moduły w kodzie, co będzie dodatkowym plusem.

## Chętnie zobaczylibyśmy jak w praktyce stosujesz/używasz

- PHP 8.x+ (wszystko nowe co się tam pojawiło, mile widziane)
- SOLID
- wzorce projektowe:
    - fabryki
    - bulidery
    - fasady
    - repozytoria
    - inne też mile widziane
- Interface
- Dependency Injection
- Doctrine
- Doctrine Migrations
- PHPUnit
    - Unit testy
    - testy integracyjne
- REST
- Docker, lub możesz wykorzystać kubernetes

## Czego oczekujemy?

Tego, że pokażesz nam jak najwięcej swoich umiejętności programistycznych, znajomości wzorców oraz różnych bibliotek i narzędzi.
Jeżeli czegoś nie znasz, ale w trakcie robienia zadania o tym doczytasz i poznasz,
to śmiało możesz tego użyć, ale wiedz, że w późniejszym etapie możemy o to zapytać.

## Ile czasu powinno Ci to zająć?

Tutaj wszystko zależy od tego jak dużo umiesz i jak płynnie się w tym poruszasz. Jeżeli zrobisz to w dzień, super,
jeżeli będziesz potrzebował kilku dni, to też nie problem.

**Nie musisz zrobić wszystkiego** z tej listy, ani użyć wszystkich rzeczy które wymieniliśmy (nie musisz się też do niej ograniczać).
Jeżeli dasz radę zaprezentować wszystkie swoje umiejętności robiąc np. tylko listowanie produktów to nie widzimy w tym problemu.

Wiemy, że w niektórych miejscach zastosowanie niektórych technik będzie przerostem formy nad treścią, więc nie musisz na siłę tego robić wszędzie.
Wystarczy jak dane rozwiazanie zastosujesz tylko w jednym miejscu, a w pozostałych zostawisz komentarz, że tutaj też można używać takiego rozwiazania.
Nie musisz pisać testów do wszystkich klas - wystarczy tyle ile uznasz, że dobrze prezentuje Twój poziom wiedzy o nich.

Jeżeli na czymś utkniesz, napisz komentarz jak byś to widział, ale nie wyszło i idź dalej. Niedziałająca aplikacja nie będzie dyskwalifikowana.
Każdy popełnia błędy i każdemu zdażają się chwile pustki w głowie.

**Sam decydujesz** jak dużo fajnych rzeczy zrobisz w tym projekcie (i ile czasu na niego poświęcisz),
jednak pamiętaj, że my nie znamy Twoich umiejętności i będziemy chcieli je poznać właśnie na podstawie Twojej realizacji tego zadania.

### Co dalej?
Wrzuć swój kod do repozytorium; jeżeli będzie on dla nas interesujący zaprosimy Cię na spotkanie,
w którym zrobimy code review na żywo, i odbędzie się rozmowa techniczna, w czasie której możemy zadawać pytania związane z zastosowanymi rozwiązaniami.
Będziesz miał wtedy również okazję dodać coś od siebie i opowiedzieć o tym, czego nie udało Ci się przekazać za pomocą kodu.

**Powodzenia**
