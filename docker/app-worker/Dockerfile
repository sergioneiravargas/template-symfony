FROM php:8.1-cli-alpine

RUN apk add --update \
    make \
    curl \
    supervisor \
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

RUN mkdir -p /var/log/supervisor

VOLUME ["/sys/fs/cgroup"]

WORKDIR /var/www/app

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/supervisord.conf"]
