FROM phpdockerio/php73-fpm:latest
WORKDIR "/var/www/html"

# Fix debconf warnings upon build
#ARG DEBIAN_FRONTEND=noninteractive
#ARG user
#ARG uid

# Install selected extensions and other stuff
RUN apt-get update; \
    apt-get -y --no-install-recommends install \
    php7.3-bcmath \ 
    php7.3-gd \ 
    php7.3-imagick \ 
    php7.3-ldap \ 
    php7.3-mysql \ 
    php7.3-redis \ 
    php7.3-sqlite3 \ 
    php7.3-ssh2 \ 
    php7.3-xdebug; \
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