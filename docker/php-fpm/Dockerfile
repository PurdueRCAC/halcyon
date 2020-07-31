FROM phpdockerio/php72-fpm:latest
WORKDIR "/var/www/html"

# Fix debconf warnings upon build
ARG DEBIAN_FRONTEND=noninteractive
#ARG user
#ARG uid

# Install selected extensions and other stuff
RUN apt-get update \
    && apt-get -y --no-install-recommends install \
        php7.2-mysql \
        php7.2-sqlite3 \
        php7.2-bcmath \
        php7.2-gd \
        php7.2-ldap \
        php-imagick \
        php-redis \
        php-xdebug \
        php-ssh2 \
    && apt-get clean; rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Generate SSL cert
#RUN mkdir /tmp/certgen
#WORKDIR /tmp/certgen
#RUN openssl genrsa -des3 -passout pass:halcyon -out server.pass.key 2048 \
#    && openssl rsa -passin pass:halcyon -in server.pass.key -out server.key \
#    && rm server.pass.key \
#    && openssl req -new -key server.key -out server.csr -subj "/CN=halcyon.rcac.purdue.edu" \
#    && openssl x509 -req -days 365 -in server.csr -signkey server.key -out server.crt \
#    && cp server.crt /etc/ssl/certs/ \
#    && cp server.key /etc/ssl/private/ \
#    && sudo chmod -R 600 /etc/ssl/certs \
#    && rm -rf /tmp/certgen

# Create system user to run Composer and Artisan Commands
#RUN useradd -G www-data,root -u $uid -d /home/$user $user
#RUN mkdir -p /home/$user/.composer && \
#    chown -R $user:$user /home/$user
#
#USER $user

#CMD ["composer install", "php artisan migrate"]