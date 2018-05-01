#!/bin/bash
if [ $1 == "staging" ]; then
    server="deploy@job-lion.et.tc"
    folder="/var/www/et.tc/job-lion/staging/backend"
#elif [ $1 == "production" ]; then
#    server="deploy@et.tc"
#    folder="/var/www/et.tc/Root"
else
    echo "Not implemented, yet"
    exit
fi

# install dependencies
apt-get update -yqq
apt-get install git -yqq

# install ssh key
[[ -f /.dockerenv ]] && eval $(ssh-agent -s)
[[ -f /.dockerenv ]] && ssh-add <(echo "$SSH_PRIVATE_KEY")

# add server host keys
mkdir -p ~/.ssh
[[ -f /.dockerenv ]] && echo "$SSH_SERVER_HOSTKEYS" > ~/.ssh/known_hosts

# get current commit
commit=$(git rev-parse HEAD)

# ssh to server
ssh -T $server << EOSSH

cd $folder

# update git
git fetch
git checkout $commit

# upgrade packages
composer install

# migrate database
./vendor/bin/doctrine-migrations migration:migrate -n

EOSSH


# run phpdoc and apidoc if on staging
if [ $1 == "staging" ]; then
  ssh -T $server << EOSSH

cd $folder

# generate api documentation
apidoc -i src/ -o public/apidoc/

# generate phpdoc documentation
phpdoc -d src/ -t public/docs

EOSSH

fi
