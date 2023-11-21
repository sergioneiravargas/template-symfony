FROM php:8.1-fpm-alpine

RUN apk add --update \
    make \
    curl \
    icu-dev \
    libxslt-dev

RUN docker-php-ext-install \
    mysqli \
    pdo_mysql \
    opcache \
    xsl \
    intl

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
    ln -s $(composer config --global home) /root/composer
ENV PATH=$PATH:/root/composer/vendor/bin COMPOSER_ALLOW_SUPERUSER=1

VOLUME ["/sys/fs/cgroup"]

WORKDIR /var/www/app

EXPOSE 9000

CMD ["php-fpm", "-F"]
