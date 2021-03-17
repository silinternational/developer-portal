#!/usr/bin/env bash

echo "Running run-tests.sh, started at: $CI_TIMESTAMP"

mkdir -p -v /data/vendor/simplesamlphp/simplesamlphp/cert
cp /tmp/ssp-overrides/cert/* /data/vendor/simplesamlphp/simplesamlphp/cert
cp /tmp/ssp-overrides/config/* /data/vendor/simplesamlphp/simplesamlphp/config
cp /tmp/ssp-overrides/metadata/* /data/vendor/simplesamlphp/simplesamlphp/metadata

# Run database migrations
echo -e "Waiting for db to run migrations...\n\n"
START=`date +%s`
whenavail db 3306 240 echo 'db ready'
runny /data/protected/yiic migrate --interactive=0
END=`date +%s`
TIME=$(($END-$START))
echo -e "Migrations should be done. Ran for $TIME seconds.\n\n"

# Setup ApiAxle with dev key/secret
echo -e "Creating api...\n\n"
curl -X POST -H 'Content-type: application/json' 'http://api/v1/api/apiaxle' -d '{"endPoint":"api", "endPointTimeout": 5, "tokenSkewProtectionCount": 5}'
echo -e "Creating key...\n\n"
curl -X POST -H 'Content-type: application/json' 'http://api/v1/key/developer-portal-dev-key' -d '{"sharedSecret":"developer-portal-dev-secret","qps":10000,"qpd":100000}'
echo -e "Linking key to api...\n\n"
curl -X PUT 'http://api/v1/api/apiaxle/linkkey/developer-portal-dev-key'

# Run phpunit tests
cd /data
echo "Installing dev dependencies..."
composer install --prefer-dist --no-interaction
cd /data/protected/tests
echo -e "Running tests\n\n"
/data/vendor/bin/phpunit unit/
