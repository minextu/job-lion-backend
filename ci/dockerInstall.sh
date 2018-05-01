#!/bin/bash

# install dependencies only for Docker
[[ ! -e /.dockerenv ]] && exit 0
set -xe

# Install dependencies
apt-get update -yqq
apt-get install git wget zlib1g-dev -yqq

# Install php extensions
if [ "$1" == "mysql" ]; then
  docker-php-ext-install pdo_mysql
fi
docker-php-ext-install zip
pecl install xdebug && docker-php-ext-enable xdebug

# set timezone
echo "[Date]" >> /usr/local/etc/php/php.ini
echo "date.timezone = UTC" >> /usr/local/etc/php/php.ini

# Install phpunit
curl --location --output /usr/local/bin/phpunit https://phar.phpunit.de/phpunit-6.4.phar
chmod +x /usr/local/bin/phpunit

# Install composer
wget https://composer.github.io/installer.sig -O - -q | tr -d '\n' > installer.sig
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('SHA384', 'composer-setup.php') === file_get_contents('installer.sig')) { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
php composer-setup.php
php -r "unlink('composer-setup.php'); unlink('installer.sig');"

# setup config file
cp conf/config.sample.php conf/config.php

if [ "$1" == "mysql" ]; then
  # production test db
  sed "s/'dbHost' => ''/'dbHost' => 'mariadb'/" -i conf/config.php
  sed "s/'dbUser' => ''/'dbUser' => 'root'/" -i conf/config.php
  sed "s/'dbPassword' => ''/'dbPassword' => 'Kaigilohgeifeph5huqu'/" -i conf/config.php
  sed "s/'dbDatabase' => ''/'dbDatabase' => 'joblion_tests'/" -i conf/config.php

  # phpunit test db
  sed "s/'testDbHost' => ':memory:'/'testDbHost' => 'mariadb'/" -i conf/config.php
  sed "s/'testDbUser' => ''/'testDbUser' => 'root'/" -i conf/config.php
  sed "s/'testDbPassword' => ''/'testDbPassword' => 'Kaigilohgeifeph5huqu'/" -i conf/config.php
  sed "s/'testDbDatabase' => ''/'testDbDatabase' => 'joblion_tests'/" -i conf/config.php
fi

# install composer dependencies
php composer.phar install
