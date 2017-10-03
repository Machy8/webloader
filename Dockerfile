FROM php:7

MAINTAINER Machy8 <8machy@seznam.cz>

ENV PATH "/composer/vendor/bin:$PATH"
ENV COMPOSER_ALLOW_SUPERUSER 1

RUN apt-get update

RUN apt-get install -y curl curl git zip unzip

RUN apt-get install -y apt-transport-https lsb-release ca-certificates wget \
    && wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg \
    && sh -c 'echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/php.list' \
    && apt-get update \
    && apt-get install -y php7.1-cgi

RUN curl -s https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

CMD	[ "php", "-S", "[::]:82", "-t", "/var/www/html" ]

EXPOSE 82
