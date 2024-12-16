#!/usr/bin/env bash

set -e

# Install composer dependencies
cd /data
composer install --no-dev --no-progress --no-scripts --optimize-autoloader --no-interaction

# Note: Copying the SSP override files into place is no longer relevant, since
# we no longer support SAML as a login option for the Developer Portal.
