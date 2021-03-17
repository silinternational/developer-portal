FROM php:7.2-alpine

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# It is expected that /data is = application/ in project folder
COPY application/ /data/
WORKDIR /data

CMD ["/data/run.sh"]
