ARG PHP_VERSION=8.4

FROM php:${PHP_VERSION}-cli-bookworm

RUN apt-get update \
    && apt-get install --no-install-recommends --yes \
        git \
        libicu-dev \
        libzip-dev \
        unzip \
    && docker-php-ext-install -j"$(nproc)" \
        intl \
        opcache \
        pdo_mysql \
        zip \
    && rm -rf /var/lib/apt/lists/*

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

CMD ["php", "-S", "0.0.0.0:8000", "-t", "public", "public/index.php"]
