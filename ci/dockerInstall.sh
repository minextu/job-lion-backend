#!/bin/bash

# docker only script
[[ ! -e /.dockerenv ]] && exit 0
set -xe

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
composer install
