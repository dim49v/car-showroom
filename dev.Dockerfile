ARG PHP_VERSION_ARG=8.0
ARG NGINX_VERSION_ARG=1.20.1

FROM php:${PHP_VERSION_ARG}-fpm AS app
ENV COMPOSER_ALLOW_SUPERUSER 1
RUN cp /usr/local/etc/php/php.ini-development /usr/local/etc/php/php.ini
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN apt-get update
RUN apt-get install -y git zip unzip wget
RUN docker-php-ext-install pdo pdo_mysql \
    && yes '' | pecl install redis \
    && docker-php-ext-enable redis
RUN wget https://get.symfony.com/cli/installer -O - | bash
COPY ./docker/php/dev.docker-command.sh /bin/docker-command.sh
WORKDIR /var/www/app/
RUN sed -i ':a;N;$!ba;s/\r//g' /bin/docker-command.sh \
    && chmod +x /bin/docker-command.sh
CMD ["/bin/docker-command.sh"]


FROM nginx:${NGINX_VERSION_ARG} AS web
COPY ./docker/web/docker-command.sh /bin/docker-command.sh
RUN apt-get update && apt-get install -y nginx-extras
RUN sed -i ':a;N;$!ba;s/\r//g' /bin/docker-command.sh \
    && chmod +x /bin/docker-command.sh
CMD ["/bin/docker-command.sh"]