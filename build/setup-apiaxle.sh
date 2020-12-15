#!/usr/bin/env bash

sleep 10

# Setup ApiAxle with dev key/secret
curl -X POST -H 'Content-type: application/json' 'http://api/v1/api/apiaxle' -d '{"endPoint":"api", "endPointTimeout": 5, "tokenSkewProtectionCount": 5}'
curl -X POST -H 'Content-type: application/json' "http://api/v1/key/${APIAXLE_KEY}" -d '{"sharedSecret":"'"${APIAXLE_SECRET}"'","qps":10000,"qpd":100000}'
curl -X PUT "http://api/v1/api/apiaxle/linkkey/${APIAXLE_KEY}"