#!/usr/bin/env bash

# start cron in background for php session gc
cron

chown -R www-data:www-data /data/protected/runtime /data/public/assets

# If a THEME_COLOR was provided, use that for the website navbar.
if [[ "x" != "x$THEME_COLOR" ]]; then
    sed -i /data/public/css/styles.css -e "s/0068a6/${THEME_COLOR}/"
fi

# Install composer dependencies
runny composer install --no-dev --no-scripts --optimize-autoloader --no-interaction

mkdir -p -v /data/vendor/simplesamlphp/simplesamlphp/cert
cp /tmp/ssp-overrides/cert/* /data/vendor/simplesamlphp/simplesamlphp/cert
cp /tmp/ssp-overrides/config/* /data/vendor/simplesamlphp/simplesamlphp/config
cp /tmp/ssp-overrides/metadata/* /data/vendor/simplesamlphp/simplesamlphp/metadata

# Run database migrations (exiting with an error if they fail)
runny /data/protected/yiic migrate --interactive=0

# Run apache in foreground
apache2ctl -k start -D FOREGROUND
