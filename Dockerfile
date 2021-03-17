FROM php:7.2-apache

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

RUN curl https://bitbucket.org/silintl/docker-whenavail/raw/master/whenavail -o /usr/local/bin/whenavail
RUN chmod a+x /usr/local/bin/whenavail

# It is expected that /data is = application/ in project folder
COPY application/ /data/
WORKDIR /data

CMD ["/data/run.sh"]
