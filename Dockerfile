FROM php:7.3

RUN apt-get update && \
    apt-get upgrade -y && \
    apt-get install -y git && \
    apt-get install -y zip unzip && \
    apt-get install -y gnupg && \
    apt-get install -y wget && \
    apt-get install -y vim

RUN wget -qO - https://www.mongodb.org/static/pgp/server-4.4.asc | apt-key add -
RUN echo "deb [ arch=amd64 ] https://repo.mongodb.org/apt/ubuntu bionic/mongodb-org/4.4 multiverse" | tee /etc/apt/sources.list.d/mongodb-org-4.4.list

RUN docker-php-ext-install pcntl sysvmsg
RUN pecl install mongodb && docker-php-ext-enable mongodb pcntl sysvmsg

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN pecl install xdebug && docker-php-ext-enable xdebug
COPY ./docker/xdebug/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini
