FROM nginx:alpine

WORKDIR "/var/www/html"

RUN apk update \
    && apk add openssl

# Generate SSL cert
RUN mkdir /tmp/certgen
RUN mkdir /etc/ssl/certs
WORKDIR /tmp/certgen
RUN openssl genrsa -des3 -passout pass:halcyon -out server.pass.key 2048 \
    && openssl rsa -passin pass:halcyon -in server.pass.key -out server.key \
    && rm server.pass.key \
    && openssl req -new -key server.key -out server.csr -subj "/CN=localhost" \
    && openssl x509 -req -days 365 -in server.csr -signkey server.key -out server.crt \
    && cp server.crt /etc/ssl/certs/ \
    && cp server.key /etc/ssl/private/ \
    && chmod -R 600 /etc/ssl/certs \
    && rm -rf /tmp/certgen
