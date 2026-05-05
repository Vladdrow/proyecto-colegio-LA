FROM php:8.4-fpm-alpine

WORKDIR /var/www/html

# Paquetes del sistema
RUN apk add --no-cache \
    postgresql-client \
    msmtp \
    wget \
    procps \
    shadow \
    libzip \
    libpng \
    libjpeg-turbo \
    libwebp \
    freetype \
    icu \
    oniguruma \
    libxml2

# Dependencias de compilación
RUN apk add --no-cache --virtual .build-deps \
    $PHPIZE_DEPS \
    icu-dev \
    zlib-dev \
    libzip-dev \
    libpng-dev \
    libwebp-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    postgresql-dev \
    libxml2-dev \
    oniguruma-dev && \
    docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp && \
    docker-php-ext-install \
        gd \
        pgsql \
        pdo_pgsql \
        mysqli \
        pdo_mysql \
        dom \
        mbstring \
        intl \
        bcmath \
        opcache \
        exif \
        zip && \
    pecl install redis && \
    docker-php-ext-enable redis && \
    apk del .build-deps && \
    rm -rf /tmp/pear /usr/src/php*


# ============================================
# OPTIMIZACIONES DE RENDIMIENTO
# ============================================

# ESTO SOLO LO USAREMOS EN PRODUCCION, EN DESARROLLO NO DEJA CARGAR CAMBIOS HECHO EN LOS ARCHIVOS
# RUN echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/opcache.ini && \
#    echo "opcache.memory_consumption=256" >> /usr/local/etc/php/conf.d/opcache.ini && \
#    echo "opcache.interned_strings_buffer=16" >> /usr/local/etc/php/conf.d/opcache.ini && \
#    echo "opcache.max_accelerated_files=20000" >> /usr/local/etc/php/conf.d/opcache.ini && \
#    echo "opcache.revalidate_freq=0" >> /usr/local/etc/php/conf.d/opcache.ini && \
#    echo "opcache.validate_timestamps=0" >> /usr/local/etc/php/conf.d/opcache.ini && \
#    echo "opcache.fast_shutdown=1" >> /usr/local/etc/php/conf.d/opcache.ini

# Configurar PHP-FPM para más workers
RUN echo "pm = dynamic" >> /usr/local/etc/php-fpm.d/www.conf && \
    echo "pm.max_children = 20" >> /usr/local/etc/php-fpm.d/www.conf && \
    echo "pm.start_servers = 5" >> /usr/local/etc/php-fpm.d/www.conf && \
    echo "pm.min_spare_servers = 5" >> /usr/local/etc/php-fpm.d/www.conf && \
    echo "pm.max_spare_servers = 10" >> /usr/local/etc/php-fpm.d/www.conf && \
    echo "pm.max_requests = 500" >> /usr/local/etc/php-fpm.d/www.conf

# Configurar límites de PHP
RUN echo "memory_limit = 256M" >> /usr/local/etc/php/conf.d/laravel.ini && \
    echo "upload_max_filesize = 50M" >> /usr/local/etc/php/conf.d/laravel.ini && \
    echo "post_max_size = 50M" >> /usr/local/etc/php/conf.d/laravel.ini && \
    echo "max_execution_time = 60" >> /usr/local/etc/php/conf.d/laravel.ini

# Copiar proyecto
COPY src /var/www/html

# Crear usuario
RUN addgroup -g 1000 laravel && \
    adduser -D -G laravel -u 1000 -s /bin/sh laravel && \
    chown -R laravel:laravel /var/www/html

USER laravel

EXPOSE 9000
CMD ["php-fpm"]