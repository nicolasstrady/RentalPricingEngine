ARG PHP_VERSION=8.4

FROM php:${PHP_VERSION}-cli-bookworm

RUN apt-get update \
    && apt-get install --no-install-recommends --yes \
        $PHPIZE_DEPS \
        git \
        libicu-dev \
        libzip-dev \
        unzip \
    && docker-php-ext-install -j"$(nproc)" \
        intl \
        opcache \
        pdo_mysql \
        zip \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && rm -rf /var/lib/apt/lists/*

RUN { \
        echo 'opcache.enable=1'; \
        echo 'opcache.memory_consumption=256'; \
        echo 'opcache.max_accelerated_files=20000'; \
        echo 'opcache.validate_timestamps=1'; \
        echo 'opcache.revalidate_freq=0'; \
        echo 'realpath_cache_size=4096K'; \
        echo 'realpath_cache_ttl=600'; \
        echo 'memory_limit=512M'; \
        echo 'xdebug.mode=off'; \
    } > /usr/local/etc/php/conf.d/zz-local-performance.ini

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock symfony.lock ./
RUN composer install --no-interaction --no-progress --no-scripts --prefer-dist

COPY .php-cs-fixer.dist.php phpstan.neon phpunit.dist.xml ./
COPY bin ./bin
COPY config ./config
COPY migrations ./migrations
COPY public ./public
COPY src ./src
COPY tests ./tests

EXPOSE 8000

CMD ["php", "-d", "opcache.enable_cli=1", "-S", "0.0.0.0:8000", "-t", "public", "public/index.php"]
