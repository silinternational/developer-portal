#!/usr/bin/env bash

echo "Running run-tests.sh, started at: $CI_TIMESTAMP"

# Run database migrations
echo -e "Waiting for db to run migrations...\n\n"
START=`date +%s`
whenavail db 3306 240 echo 'db ready'
/data/protected/yiic migrate --interactive=0
END=`date +%s`
TIME=$(($END-$START))
echo -e "Migrations should be done. Ran for $TIME seconds.\n\n"

# Setup ApiAxle with dev key/secret
whenavail api 80 100 echo 'api ready'
echo -e "Creating api...\n\n"
curl -X POST -H 'Content-type: application/json' 'http://api/v1/api/apiaxle' -d '{"endPoint":"api"}'
echo -e "Creating key...\n\n"
curl -X POST -H 'Content-type: application/json' 'http://api/v1/key/developer-portal-dev-key' -d '{"sharedSecret":"developer-portal-dev-secret","qps":1000,"qpd":10000}'
echo -e "Linking key to api...\n\n"
curl -X PUT 'http://api/v1/api/apiaxle/linkkey/developer-portal-dev-key'

# Run phpunit tests
cd /data
echo "Installing dev dependencies..."
composer install --prefer-dist --no-interaction
cd /data/protected/tests
echo -e "Running tests\n\n"
/data/vendor/bin/phpunit unit/
