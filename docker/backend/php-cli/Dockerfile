FROM php:8.1-cli-alpine

RUN set -eux; \
    apk update; \
    apk add --no-cache make unzip autoconf g++ postgresql-dev bash coreutils git mpdecimal-dev libxml2-dev

RUN set -eux; \
# Download latest version of the mlocati/docker-php-extension-installer
    curl -sSLf \
      -o /usr/local/bin/install-php-extensions \
      https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions; \
    chmod +x /usr/local/bin/install-php-extensions; \
    install-php-extensions \
          amqp-1.11.0 \
          apcu \
          bcmath \
          bz2 \
          decimal \
          exif \
          gettext \
          gmp \
          intl \
          opcache \
          pcntl \
          mysqli \
          pdo \
          pdo_mysql \
          redis-stable \
          soap \
          sockets \
          ssh2-beta \
          xdebug-stable \
          zip-stable; \
    rm -rf /var/cache/apk/*; \
    rm -rf /tmp/*;

RUN mv $PHP_INI_DIR/php.ini-development $PHP_INI_DIR/php.ini

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/bin --filename=composer --quiet

#RUN addgroup -g 1000 app && adduser -u 1000 -G app -s /bin/sh -D app
#USER app

WORKDIR /app

RUN composer install
