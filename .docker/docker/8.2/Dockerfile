FROM php:8.2.8-fpm
ARG WORKDIR=/var/www/html
ENV DOCUMENT_ROOT=${WORKDIR}
ENV LARAVEL_PROCS_NUMBER=1
ENV DOMAIN=_
ENV CLIENT_MAX_BODY_SIZE=15M
ENV NODE_VERSION=17.x
ARG HOST_UID=1000
ENV USER=www-data
# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    htop \
    curl \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libmemcached-dev \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    librdkafka-dev \
    libpq-dev \
    openssh-server \
    zip \
    unzip \
    supervisor \
    sqlite3  \
    nano \
    cron

RUN curl -fsSL https://deb.nodesource.com/setup_${NODE_VERSION} | bash -
 # Install Node
RUN apt-get install -y nodejs
# Install nginx
RUN apt-get update && apt-get install -y nginx

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions zip, mbstring, exif, bcmath, intl
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install  zip mbstring exif pcntl bcmath -j$(nproc) gd intl

# Install Redis and enable it
RUN pecl install redis  && docker-php-ext-enable redis

# Install the php memcached extension
RUN pecl install memcached && docker-php-ext-enable memcached

# Install the PHP pdo_mysql extention
RUN docker-php-ext-install pdo_mysql

# Install the PHP pdo_pgsql extention
RUN docker-php-ext-install pdo_pgsql


# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set working directory
WORKDIR $WORKDIR

RUN rm -Rf /var/www/* && \
mkdir -p /var/www/html

ADD .docker/conf/nginx/default.conf /etc/nginx/sites-available/default
ADD .docker/php.ini $PHP_INI_DIR/conf.d/
ADD .docker/supervisor/supervisord.conf /etc/supervisor/supervisord.conf

COPY .docker/entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/entrypoint.sh
RUN ln -s /usr/local/bin/entrypoint.sh /

ENTRYPOINT ["entrypoint.sh"]

RUN usermod -u ${HOST_UID} www-data
RUN groupmod -g ${HOST_UID} www-data

RUN chmod -R 755 $WORKDIR
RUN chown -R www-data:www-data $WORKDIR
EXPOSE 80
CMD [ "entrypoint" ]

