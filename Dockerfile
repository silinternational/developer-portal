FROM php:7.4-apache
LABEL maintainer="matt_henderson@sil.org"

RUN apt-get update -y && \
    apt-get install -y \
        unzip \
        zip \
        cron \
# Needed to get various scripts
        curl \
# Needed for whenavail
        netcat \
# Needed to install s3cmd
        python-pip \
# Needed to build php extensions
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libonig-dev \
        libxml2-dev \
        libcurl4-openssl-dev

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
RUN curl https://bitbucket.org/silintl/docker-whenavail/raw/master/whenavail -o /usr/local/bin/whenavail
RUN chmod a+x /usr/local/bin/whenavail
RUN curl https://raw.githubusercontent.com/silinternational/runny/0.2/runny -o /usr/local/bin/runny
RUN chmod a+x /usr/local/bin/runny

# Install and enable, see the README on the docker hub for the image
RUN docker-php-ext-configure gd --with-freetype=/usr/include --with-jpeg=/usr/include && \
    docker-php-ext-install -j$(nproc) gd && \
    docker-php-ext-install pdo pdo_mysql mbstring xml curl && \
    docker-php-ext-enable gd pdo pdo_mysql mbstring xml curl

# Make sure /data is available
RUN mkdir -p /data

# Copy in vhost configuration
COPY build/vhost.conf /etc/apache2/sites-enabled/

# .htaccess file needs Rewrite and Headers modules
RUN a2enmod rewrite
RUN a2enmod headers

# Ensure the DocumentRoot folder exists
RUN mkdir -p /data/public/

# If apache2 hasn't run there is no config file.
RUN ["apache2ctl", "configtest"]

# ErrorLog inside a VirtualHost block is ineffective for unknown reasons
RUN sed -i -E 's@ErrorLog .*@ErrorLog /proc/self/fd/2@i' /etc/apache2/apache2.conf

# Copy the SimpleSAMLphp configuration files to a temporary location
COPY build/ssp-overrides /tmp/ssp-overrides

# Use the default production configuration
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

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

# Make sure the default site is disabled
RUN a2dissite 000-default

CMD ["/data/run.sh"]
