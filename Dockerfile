FROM php:7.2-fpm-alpine as app

ARG jwt_passphrase

ENV COMPOSER_ALLOW_SUPERUSER=1

WORKDIR /var/www/html

COPY . /var/www/html

RUN NPROC=$(grep -c ^processor /proc/cpuinfo 2>/dev/null || 1) \
	&& apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
	&& apk add --update --no-cache --virtual .ext-deps \
		bash \
		openssl \
		mariadb-client \
	&& docker-php-ext-install -j${NPROC} \
		opcache \
		pdo_mysql \
	&& pecl install \
		xdebug \
	&& docker-php-ext-enable \
		xdebug \
    && openssl genrsa -aes256 -passout pass:${jwt_passphrase} -out config/jwt/private.pem 4096 \
    && openssl rsa -pubout -in config/jwt/private.pem \
    	-out config/jwt/public.pem -passin pass:${jwt_passphrase} \
	&& apk del .build-deps

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY ./docker/php.ini /usr/local/etc/php/conf.d/

CMD bash -c "bin/wait-for-db && bin/update && php-fpm"
