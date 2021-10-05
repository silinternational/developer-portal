FROM alpine AS builder
RUN apk update && apk add curl && rm -rf /var/cache/apk/*
RUN curl --remote-name --time-cond cacert.pem https://curl.se/ca/cacert.pem

FROM apiaxle/apiaxle:1.15.0
LABEL maintainer="matt_henderson@sil.org"
COPY --from=builder cacert.pem /etc/ssl/certs/ca-certificates.crt
