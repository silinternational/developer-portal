FROM silintl/php7:latest
MAINTAINER Phillip Shipley <phillip_shipley@sil.org>

ENV REFRESHED_AT 2016-12-16

# Make sure /data is available
RUN mkdir -p /data

# Copy in vhost configuration
COPY build/vhost.conf /etc/apache2/sites-enabled/

# Copy the SimpleSAMLphp configuration files to a temporary location
COPY build/ssp-overrides /tmp/ssp-overrides

# Copy in syslog config
RUN rm -f /etc/rsyslog.d/*
COPY build/rsyslog.conf /etc/rsyslog.conf

# Copy in any additional PHP ini files
COPY build/php/*.ini /etc/php/7.0/apache2/conf.d/
COPY build/php/*.ini /etc/php/7.0/cli/conf.d/

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

# Record now as the build date/time (in a friendly format).
RUN date -u +"%B %-d, %Y, %-I:%M%P (%Z)" > /data/protected/data/version.txt

CMD ["/data/run.sh"]
