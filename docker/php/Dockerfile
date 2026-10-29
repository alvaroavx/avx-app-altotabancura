FROM php:8.5.12-apache-bookworm

ARG SQLSRV_VERSION=5.13.3

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        ca-certificates \
        curl \
        gnupg \
        libcurl4-openssl-dev \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libkrb5-3 \
        libonig-dev \
        libpng-dev \
        libzip-dev \
        unixodbc-dev \
    && curl -fsSLo /tmp/packages-microsoft-prod.deb \
        https://packages.microsoft.com/config/debian/12/packages-microsoft-prod.deb \
    && dpkg -i /tmp/packages-microsoft-prod.deb \
    && rm /tmp/packages-microsoft-prod.deb \
    && apt-get update \
    && ACCEPT_EULA=Y apt-get install -y --no-install-recommends msodbcsql18 \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" curl gd mbstring zip \
    && pecl install sqlsrv-${SQLSRV_VERSION} pdo_sqlsrv-${SQLSRV_VERSION} \
    && docker-php-ext-enable sqlsrv pdo_sqlsrv \
    && a2enmod rewrite headers \
    && apt-get purge -y --auto-remove gnupg \
    && rm -rf /var/lib/apt/lists/* /tmp/pear

COPY docker/php/apache-vhost.conf /etc/apache2/sites-available/000-default.conf
COPY docker/php/php-local.ini /usr/local/etc/php/conf.d/zz-local.ini
COPY docker/php/entrypoint.sh /usr/local/bin/alto-tabancura-entrypoint

RUN chmod +x /usr/local/bin/alto-tabancura-entrypoint

ENTRYPOINT ["alto-tabancura-entrypoint"]
CMD ["apache2-foreground"]
