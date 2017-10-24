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
vendor/bin/doctrine orm:schema-tool:update --dump-sql --force

# generate documentation
apidoc -i src/ -o public/apidoc/

EOSSH
