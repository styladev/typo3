# TYPO3 Styla Extension Docker setup

## How to start

In project's root folder, start with

    docker-compose up --build

Access frontend:

    http://localhost

Access backend:

    http://localhost/typo3

Credentials are `admin` / `password` (set in [./run.sh])

## Example database

There is a set of configured pages within the docker setup to test the extension behavior.

### How to create a new database dump

Connect to docker instance:

    docker exec -it typo3_typo3_1 bash

Execute database export:

    php /var/www/html/Packages/Libraries/bin/typo3cms database:export -c Default -e 'cf_*' -e 'cache_*' -e '[bf]e_sessions' -e sys_log > dump.sql

Open dump.sql and copy to clipboard:

    cat dump.sql
    
Replace content of `docker/dump.sql` and rebuild instance.
