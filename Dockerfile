FROM php:7.2-alpine

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

CMD ["/data/run.sh"]
