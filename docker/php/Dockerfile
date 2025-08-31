FROM php:8.2-fpm

COPY php.ini /usr/local/etc/php/

RUN apt update \
  && apt install -y default-mysql-client zlib1g-dev libzip-dev unzip \
  && docker-php-ext-install pdo_mysql zip bcmath

RUN curl -sS https://getcomposer.org/installer | php \
  && mv composer.phar /usr/local/bin/composer \
  && composer self-update

WORKDIR /var/www

# UID=1000 のユーザー tomo を作成
RUN useradd -m -u 1000 -s /bin/bash tomo

# 以降を tomo ユーザーで実行
USER tomo
