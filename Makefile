
start: web

api:
	docker compose up -d api

apiaxle: redis api proxy axlesetup

axlesetup:
	docker compose up -d axlesetup

bounce:
	docker compose up -d web

clean:
	docker compose kill
	docker compose rm -f

composer:
	docker compose run --rm composer composer install --no-scripts

composerupdate:
	docker compose run --rm composer bash -c "composer update --no-scripts; composer show --direct > direct-dependencies.txt"

db:
	docker compose up -d db

httpbin:
	docker compose up -d httpbin

phpmyadmin:
	docker compose up -d phpmyadmin

phpunit:
	docker compose run --rm phpunit

proxy:
	docker compose up -d proxy

ps:
	docker compose ps

redis:
	docker compose up -d redis

rmapiaxle:
	docker compose kill redis api proxy axlesetup
	docker compose rm -f redis api proxy axlesetup

rmdb:
	docker compose kill db
	docker compose rm -f db

rmtestdb:
	docker compose kill testdb
	docker compose rm -f testdb

test: testunit

testdb:
	docker compose up -d testdb

testenv: composer rmtestdb testdb yiimigratetestdb rmapiaxle apiaxle web
	@echo "\n\n../../vendor/bin/phpunit --testsuite DeveloperPortal\n"
	docker compose run --rm phpunit bash

testunit: composer rmtestdb testdb yiimigratetestdb rmapiaxle apiaxle web phpunit

web: apiaxle db composer yiimigrate
	docker compose up -d web

yiimigrate:
	docker compose run --rm yiimigrate

yiimigratetestdb:
	docker compose run --rm yiimigratetestdb
