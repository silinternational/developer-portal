
api:
	docker-compose up -d api

apiaxle: redis api proxy axlesetup

axlesetup:
	docker-compose up -d axlesetup

bounce:
	docker-compose up -d web

clean:
	docker-compose kill
	docker-compose rm -f

composer:
	docker-compose run --rm composer

composerupdate:
	docker-compose run --rm composerupdate

phpunit:
	docker-compose run --rm phpunit

proxy:
	docker-compose up -d proxy

redis:
	docker-compose up -d redis

rmApiaxle:
	docker-compose kill redis api proxy axlesetup
	docker-compose rm -f redis api proxy axlesetup

rmDb:
	docker-compose kill db
	docker-compose rm -f db

rmTestDb:
	docker-compose kill testDb
	docker-compose rm -f testDb

start: web

test: testunit

testunit: composer rmTestDb upTestDb yiimigratetestDb rmApiaxle apiaxle web phpunit

upDb:
	docker-compose up -d db

upTestDb:
	docker-compose up -d testDb

web: apiaxle upDb composer yiimigrate
	docker-compose up -d web

yiimigrate:
	docker-compose run --rm yiimigrate

yiimigratetestDb:
	docker-compose run --rm yiimigratetestDb
