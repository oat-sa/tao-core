
FROM php:7.2-cli-alpine as cg-php-base

RUN set -eux; \
        cd /; \
        apk add --no-cache --virtual .build-deps \
            zip \
            unzip \
            zlib-dev \
            icu-dev \
            zstd-dev \
            linux-headers \
            autoconf \
            make \
            g++ \
            openssh \
            git \
        ; \
        yes "" | pecl install igbinary apcu pcov \
        ;\
        docker-php-ext-install intl; \
        docker-php-ext-install mbstring; \
        docker-php-ext-install opcache; \
        docker-php-ext-install zip; \
        docker-php-ext-install pcntl; \
        docker-php-ext-install sysvshm \
        ; \
        docker-php-ext-enable igbinary; \
        docker-php-ext-enable apcu; \
        docker-php-ext-enable pcov; \
        runDeps="$( \
                scanelf --needed --nobanner --format '%n#p' --recursive /usr/local \
                | tr ',' '\n' \
                | sort -u \
                | awk 'system("[ -e /usr/local/lib/" $1 " ]") == 0 { next } { print "so:" $1 }' \
        )"; \
        apk add --no-cache $runDeps; \
        apk del --no-network .build-deps; \
        rm -rf /var/www

WORKDIR /var/www

FROM cg-php-base AS composer

WORKDIR /app
COPY . .
RUN set -eu; \
        EXPECTED_CHECKSUM="$(wget -q -O - https://composer.github.io/installer.sig)"; \
        php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"; \
        ACTUAL_CHECKSUM="$(php -r "echo hash_file('sha384', 'composer-setup.php');")"; \
        if [ "$EXPECTED_CHECKSUM" != "$ACTUAL_CHECKSUM" ]; \
        then \
            >&2 echo 'ERROR: Invalid installer checksum'; \
            rm composer-setup.php; \
            exit 1; \
        fi; \
        php composer-setup.php --quiet --install-dir=/usr/local/bin --filename=composer; \
        RESULT=$?; \
        rm composer-setup.php;