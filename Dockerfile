FROM silintl/php7-apache:{{.Branch}}
LABEL maintainer="matt_henderson@sil.org"

RUN apt-get update -y && \
    apt-get install -y \
# Needed to install s3cmd
        python-pip && \
    apt-get autoremove -y && \
    rm -rf /var/lib/apt/lists/*

RUN curl https://raw.githubusercontent.com/silinternational/runny/0.2/runny -o /usr/local/bin/runny
RUN chmod a+x /usr/local/bin/runny

# Copy in vhost configuration
COPY build/vhost.conf /etc/apache2/sites-enabled/

# Ensure the DocumentRoot folder exists
RUN mkdir -p /data/public/

# Make sure the Apache config is valid
RUN ["apache2ctl", "configtest"]

# Copy the SimpleSAMLphp configuration files to a temporary location
COPY build/ssp-overrides /tmp/ssp-overrides

# Copy in any additional PHP ini files
COPY build/php/*.ini "$PHP_INI_DIR/conf.d/"

# get s3cmd and s3-expand
RUN pip install s3cmd
RUN curl https://raw.githubusercontent.com/silinternational/s3-expand/1.5/s3-expand -o /usr/local/bin/s3-expand
RUN chmod a+x /usr/local/bin/s3-expand

# It is expected that /data is = application/ in project folder
COPY application/ /data/

WORKDIR /data

# Fix folder permissions
RUN chown -R www-data:www-data \
    protected/runtime/ \
    public/assets/

# Get s3-expand for ENTRYPOINT
RUN curl -o /usr/local/bin/s3-expand https://raw.githubusercontent.com/silinternational/s3-expand/master/s3-expand \
    && chmod a+x /usr/local/bin/s3-expand

EXPOSE 80
ENTRYPOINT ["/usr/local/bin/s3-expand"]

# Record now as the build date/time (in a friendly format).
RUN date -u +"%B %-d, %Y, %-I:%M%P (%Z)" > /data/protected/data/version.txt

CMD ["/data/run.sh"]
