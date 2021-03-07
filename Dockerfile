FROM php:8.0

LABEL maintainer="8machy@seznam.cz"

ENV PATH "/composer/vendor/bin:$PATH"
ENV COMPOSER_ALLOW_SUPERUSER 1

RUN apt-get update && apt-get install -y curl curl git zip unzip \
	&& apt-get install -y libicu-dev \
	&& docker-php-ext-configure intl \
	&& docker-php-ext-install intl

RUN curl -s https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

CMD	[ "php", "-S", "[::]:80", "-t", "/var/www/html" ]

EXPOSE 80
