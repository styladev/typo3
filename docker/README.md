# Typo3 Plugin

## How to start

Start with

    docker-compose up --build

Access frontend:

    http://localhost

Access backend:

    http://localhost/typo3

## Example database

There is a set of configurated pages within the docker setup to test the plugin behavior.

### Create new database dump

Connect to docker instance:

    docker exec -it typo3_typo3_1 bash

Execute database export:

    php /var/www/html/Packages/Libraries/bin/typo3cms database:export -c Default -e 'cf_*' -e 'cache_*' -e '[bf]e_sessions' -e sys_log > dump.sql

Open dump.sql and copy to clipboard:

    cat dump.sql
    
Replace content of `docker/dump.sql` and rebuild instance.
