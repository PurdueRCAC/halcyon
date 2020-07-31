FROM nginx:alpine
WORKDIR "/var/www/html"

RUN apt-get update \
    && apt-get install -y openssl

# Generate SSL cert
RUN mkdir /tmp/certgen
WORKDIR /tmp/certgen
RUN openssl genrsa -des3 -passout pass:halcyon -out server.pass.key 2048 \
    && openssl rsa -passin pass:halcyon -in server.pass.key -out server.key \
    && rm server.pass.key \
    && openssl req -new -key server.key -out server.csr -subj "/CN=halcyon.rcac.purdue.edu" \
    && openssl x509 -req -days 365 -in server.csr -signkey server.key -out server.crt \
    && cp server.crt /etc/ssl/certs/ \
    && cp server.key /etc/ssl/private/ \
    && sudo chmod -R 600 /etc/ssl/certs \
    && rm -rf /tmp/certgen
