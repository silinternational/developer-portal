
api:
	docker-compose up -d api

apiaxle: redis api proxy axlesetup

axlesetup:
	docker-compose up -d axlesetup

bounce:
	docker-compose up -d portal

clean:
	docker-compose kill
	docker-compose rm -f

composer:
	docker-compose run --rm composer

composerupdate:
	docker-compose run --rm composerupdate

db:
	docker-compose up -d db

phpunit:
	docker-compose run --rm phpunit

portal: apiaxle db composer yiimigrate
	docker-compose up -d portal

proxy:
	docker-compose up -d proxy

ps:
	docker-compose ps

redis:
	docker-compose up -d redis

rmapiaxle:
	docker-compose kill redis api proxy axlesetup
	docker-compose rm -f redis api proxy axlesetup

rmdb:
	docker-compose kill db
	docker-compose rm -f db

rmtestdb:
	docker-compose kill testdb
	docker-compose rm -f testdb

start: portal

test: testunit

testdb:
	docker-compose up -d testdb

testunit: composer rmtestdb testdb yiimigratetestdb rmapiaxle apiaxle portal phpunit

yiimigrate:
	docker-compose run --rm yiimigrate

yiimigratetestdb:
	docker-compose run --rm yiimigratetestdb
