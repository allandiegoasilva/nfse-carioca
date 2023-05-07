FROM php:7.4-rc-apache

RUN apt-get update && apt-get install zip unzip -y
RUN apt-get install vim -y

RUN apt-get -y update \
  && apt-get install -y libicu-dev\
  && docker-php-ext-configure intl \
  && docker-php-ext-install intl


# PDO
RUN docker-php-ext-install pdo_mysql

# Enable apache modules
RUN a2enmod rewrite headers && a2enmod rewrite

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN mkdir /var/www/nfse 

WORKDIR /var/www/nfse 

RUN composer install --no-interaction 

# Change files permissions
RUN chown -R www-data:www-data /var/www/nfse


