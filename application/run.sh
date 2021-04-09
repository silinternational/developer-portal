#!/usr/bin/env bash

# start cron in background for php session gc
cron

chown -R www-data:www-data /data/protected/runtime /data/public/assets

# If a THEME_COLOR was provided, use that for the website navbar.
if [[ "x" != "x$THEME_COLOR" ]]; then
    sed -i /data/public/css/styles.css -e "s/0068a6/${THEME_COLOR}/"
fi

# Run database migrations (exiting with an error if they fail)
runny /data/protected/yiic migrate --interactive=0

# Run apache in foreground
apache2ctl -k start -D FOREGROUND
