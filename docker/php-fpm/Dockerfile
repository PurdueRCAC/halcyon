FROM phpdockerio/php:8.2-fpm
WORKDIR "/var/www/html"

# Fix debconf warnings upon build
#ARG DEBIAN_FRONTEND=noninteractive
#ARG user
#ARG uid

# Install selected extensions and other stuff
RUN apt-get update; \
    apt-get -y --no-install-recommends install \
    php8.2-bcmath \ 
    php8.2-gd \ 
    php8.2-imagick \ 
    php8.2-ldap \ 
    php8.2-mysql \ 
    php8.2-redis \ 
    php8.2-sqlite3 \ 
    php8.2-ssh2 \ 
    php8.2-xdebug; \
    apt-get clean; \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create system user to run Composer and Artisan Commands
#RUN useradd -G www-data,root -u $uid -d /home/$user $user
#RUN mkdir -p /home/$user/.composer && \
#    chown -R $user:$user /home/$user
#
#USER $user

#CMD ["composer install", "php artisan migrate"]
