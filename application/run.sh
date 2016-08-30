#!/usr/bin/env bash

if [[ "x" == "x$LOGENTRIES_KEY" ]]; then
    echo "Missing LOGENTRIES_KEY environment variable";
else
    # Set logentries key based on environment variable
    sed -i /etc/rsyslog.conf -e "s/LOGENTRIESKEY/${LOGENTRIES_KEY}/"
    # Start syslog
    rsyslogd
fi

chown -R www-data:www-data /data/protected/runtime /data/public/assets

# Run database migrations
/data/protected/yiic migrate --interactive=0

# Run apache in foreground
apache2ctl -D FOREGROUND
