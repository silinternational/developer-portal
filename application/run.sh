#!/usr/bin/env bash

if [[ "x" == "x$LOGENTRIES_KEY" ]]; then
    echo "Missing LOGENTRIES_KEY environment variable";
else
    # Set logentries key based on environment variable
    sed -i /etc/rsyslog.conf -e "s/LOGENTRIESKEY/${LOGENTRIES_KEY}/"
    # Start syslog
    rsyslogd
    
    # Give syslog time to finish starting.
    sleep 10
fi

chown -R www-data:www-data /data/protected/runtime /data/public/assets

# If a THEME_COLOR was provided, use that for the website navbar.
if [[ "x" != "x$THEME_COLOR" ]]; then
    sed -i /data/public/css/styles.css -e "s/0068a6/${THEME_COLOR}/"
fi

mkdir -p -v /data/vendor/simplesamlphp/simplesamlphp/cert
cp /tmp/ssp-overrides/cert/* /data/vendor/simplesamlphp/simplesamlphp/cert
cp /tmp/ssp-overrides/config/* /data/vendor/simplesamlphp/simplesamlphp/config
cp /tmp/ssp-overrides/metadata/* /data/vendor/simplesamlphp/simplesamlphp/metadata

# Run database migrations (exiting with an error if they fail)
runny /data/protected/yiic migrate --interactive=0

# Run apache in foreground
apache2ctl -D FOREGROUND
