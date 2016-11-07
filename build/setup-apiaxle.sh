#!/usr/bin/env bash

# Setup ApiAxle with dev key/secret
curl -X POST -H 'Content-type: application/json' 'http://api/v1/api/apiaxle' -d '{"endPoint":"api", "endPointTimeout": 5}'
curl -X POST -H 'Content-type: application/json' 'http://api/v1/key/developer-portal-dev-key' -d '{"sharedSecret":"developer-portal-dev-secret","qps":1000,"qpd":10000}'
curl -X PUT 'http://api/v1/api/apiaxle/linkkey/developer-portal-dev-key'