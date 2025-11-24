FROM php:8.2-fpm

# -------- PHP 拡張など --------
COPY php.ini /usr/local/etc/php/

RUN apt update \
  && apt install -y \
  default-mysql-client \
  zlib1g-dev \
  libzip-dev \
  unzip \
  libfreetype6-dev \
  libjpeg62-turbo-dev \
  libpng-dev \
  libwebp-dev \
  # gd の設定
  && docker-php-ext-configure gd \
  --with-freetype \
  --with-jpeg \
  --with-webp \
  # PHP拡張インストール
  && docker-php-ext-install \
  pdo_mysql \
  zip \
  bcmath \
  gd \
  && apt clean \
  && rm -rf /var/lib/apt/lists/*

# -------- Composer --------
RUN curl -sS https://getcomposer.org/installer | php \
  && mv composer.phar /usr/local/bin/composer \
  && composer self-update

# -------- ホストの UID/GID でユーザーを作る --------
ARG UID=1000
ARG GID=1000
RUN groupadd -g ${GID} app && useradd -ms /bin/bash -u ${UID} -g ${GID} app

WORKDIR /var/www

# vendor/.composer キャッシュ等の書き込み権限
ENV COMPOSER_HOME=/var/www/.composer
RUN mkdir -p ${COMPOSER_HOME} \
  && chown -R ${UID}:${GID} /var/www

USER app
WORKDIR /var/www
