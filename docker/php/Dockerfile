FROM php:8.2-fpm

COPY php.ini /usr/local/etc/php/

RUN apt-get update \
  && apt-get install -y \
    default-mysql-client \
    zlib1g-dev \ libzip-dev \
    libonig-dev \
    unzip \
  && rm -rf /var/lib/apt/lists/*
  
# 必要拡張 
RUN docker-php-ext-install pdo_mysql zip mbstring

# composerは公式からコピー 
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer 

WORKDIR /var/www

# UID=1000 のユーザー tomo を作成
RUN useradd -m -u 1000 -s /bin/bash tomo

# 以降を tomo ユーザーで実行
USER tomo
