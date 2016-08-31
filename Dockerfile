FROM silintl/php-web:latest
MAINTAINER Phillip Shipley <phillip_shipley@sil.org>

ENV REFRESHED_AT 2015-07-24

# Make sure /data is available
RUN mkdir -p /data

# Copy in vhost configuration
COPY build/developer-portal.conf /etc/apache2/sites-enabled/

# Copy in syslog config
RUN rm -f /etc/rsyslog.d/*
COPY build/rsyslog.conf /etc/rsyslog.conf
RUN mkdir -p /opt/ssl
COPY build/logentries.all.crt /opt/ssl/logentries.all.crt

# Copy in updated php.ini
COPY build/php.ini /etc/php5/apache2/
COPY build/php.ini /etc/php5/cli/

# It is expected that /data is = application/ in project folder
COPY application/ /data/

WORKDIR /data

# Install/cleanup composer dependencies
RUN composer install --prefer-dist --no-interaction --no-dev --optimize-autoloader

EXPOSE 80
CMD ["/data/run.sh"]
