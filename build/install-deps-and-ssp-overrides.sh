#!/usr/bin/env bash

set -e

# Install composer dependencies
cd /data
composer install --no-dev --no-scripts --optimize-autoloader --no-interaction

# Copy the SSP override files into place
mkdir -p -v /data/vendor/simplesamlphp/simplesamlphp/cert
cp /tmp/ssp-overrides/cert/* /data/vendor/simplesamlphp/simplesamlphp/cert
cp /tmp/ssp-overrides/config/* /data/vendor/simplesamlphp/simplesamlphp/config
cp /tmp/ssp-overrides/metadata/* /data/vendor/simplesamlphp/simplesamlphp/metadata
