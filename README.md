 # Bodegapi
 
 ![Travis CI build status](https://travis-ci.com/andrewmy/bodegapi.svg?branch=master)
 
 This is a sample JSON REST API project implemented using [API Platform](https://api-platform.com).
 
 ## Running
 
 1. `docker-compose up` — containers with all the dev dependencies.
 2. Visit http://127.0.0.1:1080/docs for API documentation
 
 ## Preparing data from CLI
 
 `docker-compose exec ./bin/console doctrine:fixtures:load -n`
 
 You're getting `api_user:api_ipa` and `admin_user:admin_nidma` users (typo intentional).
 
 ## API
 
 Visit http://127.0.0.1:1080/docs for API documentation with sample requests and responses.
 
 - Available to anonymous users:
   - Login:
   	 ```
     POST /api/login
     {"username": "api_user", "password": "api_ipa"}
     ```
     Response: a JWT.
 - Available only to users with a `Bearer` authorization:
   - With ROLE_USER:
     - `GET /api/docs.json` (or other formats)
     - `GET /api/products`
     - `GET /api/products/{id}`
     - `GET /api/cart`
     - `POST /api/cart/add`
     - `POST /api/cart/remove`
     - `GET /api/cart_items/{id}`
   - With ROLE_ADMIN:
     - `POST /api/products`
     - `PUT /api/products/{id}`
     - `DELETE /api/products/{id}`
 
 ## Testing
 
 1. `cp phpunit.xml.dist phpunit.xml` and edit the result if needed
2. `docker-compose exec php_app bin/phpunit`

## Static analysis

`docker run --rm -v $(pwd):/project -w /project jakzal/phpqa:alpine phpstan analyse`

## Running without Docker

1. Pre-requisites:
  - PHP 7.2+
    - ext-json
    - ext-pdo_mysql
    - ext-zip
    - [Recommended performance options for php.ini](./docker/php.ini)
  - MariaDB / MySQL
  - Nginx or Apache. [Sample Nginx config](./docker/etc/nginx/conf.d/default.conf)
  - [Composer](https://getcomposer.org)
  - The rest from [Symfony requirements](https://symfony.com/doc/4.0/reference/requirements.html)
2. Configure your web server to serve files from `<project_dir>/public`
3. Create a database
4. ```bash
    cd <project_dir>
    composer install
    cp .env .env.local
    ```
5. Edit `.env.local`, particularly `DATABASE_URL` and `JWT_PASSPHRASE`
6. ```
    ./bin/console doctrine:migrations:migrate -n
   openssl genrsa -aes256 -passout pass:<JWT_passphrase> -out config/jwt/private.pem 4096
   openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem -passin pass:<JWT_passphrase>
   ```

