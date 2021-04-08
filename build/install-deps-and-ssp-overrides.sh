#!/usr/bin/env bash

set -e

# Install composer dependencies
cd /data
composer install --no-dev --no-scripts --optimize-autoloader --no-interaction
