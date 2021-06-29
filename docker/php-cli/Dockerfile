FROM php:8.0-cli

LABEL maintainer="Vagif Rufullazada"

RUN apt-get update && apt-get install -y jpegoptim libpq-dev zlib1g-dev libicu-dev libzip-dev procps \
libfreetype6-dev libjpeg62-turbo-dev libpng-dev zip unzip git && rm -rf /var/lib/apt/lists/*

# Installing php-redis extension via pecl.
RUN pecl install -o -f redis && rm -rf /tmp/pear

# Configuring and enabling php extensions.
RUN docker-php-ext-enable redis
RUN docker-php-ext-configure gd --with-freetype --with-jpeg

# Installing php extensions.
RUN docker-php-ext-install pdo pdo_mysql mysqli zip gd exif

# Installing composer.
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer --quiet

# Creating user for container.
RUN groupadd -g 1000 appuser && useradd -r -u 1000 -g appuser appuser

# Creating user && composer-cache directories.
RUN mkdir -p /home/appuser/.composer/cache/repo/https---asset-packagist.org \
    && mkdir /home/appuser/.composer/cache/files

# Giving proper permissions to user.
RUN chown -R appuser:appuser /home/appuser && chmod -R 775 /home/appuser

# Running container as user
USER appuser

ENV COMPOSER_NO_INTERACTION=1

WORKDIR /app
