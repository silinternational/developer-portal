FROM apiaxle/apiaxle:1.15.0
LABEL maintainer="matt_henderson@sil.org"

RUN apk update && apk add ca-certificates && rm -rf /var/cache/apk/*

RUN curl --remote-name --time-cond cacert.pem https://curl.se/ca/cacert.pem
RUN cp ./cacert.pem /usr/local/share/ca-certificates/cacert.pem

RUN update-ca-certificates
