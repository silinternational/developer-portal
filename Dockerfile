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

# Copy in updated php.ini
COPY build/php.ini /etc/php5/apache2/
COPY build/php.ini /etc/php5/cli/

# It is expected that /data is = application/ in project folder
COPY application/ /data/

WORKDIR /data

# Install/cleanup composer dependencies
RUN composer install --prefer-dist --no-interaction --no-dev --optimize-autoloader

# Get s3-expand for ENTRYPOINT
RUN curl -o /usr/local/bin/s3-expand https://raw.githubusercontent.com/silinternational/s3-expand/master/s3-expand \
    && chmod a+x /usr/local/bin/s3-expand

EXPOSE 80
ENTRYPOINT ["/usr/local/bin/s3-expand"]
CMD ["/data/run.sh"]
