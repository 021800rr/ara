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
cd cf
docker compose --file docker/docker-compose.yml --env-file BE/.env up -d

docker exec -it ara-php-dev bash
    cd /var/www/
    make tests
```

## how to dev

```shell
cd cf
docker compose --file docker/docker-compose.yml --env-file BE/.env up -d
```

api: http://localhost/api  

## how to reset 

```shell
cd cf
docker compose --file docker/docker-compose.yml --env-file BE/.env down --remove-orphans && \
docker compose --file docker/docker-compose.yml --env-file BE/.env build --no-cache --pull && \
docker compose --file docker/docker-compose.yml --env-file BE/.env up -d
```

## how to reset database

```shell
cd cf
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
