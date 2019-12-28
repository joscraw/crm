#!/usr/bin/env bash

# set HOSTNAME
HOSTNAME=crm.dev

# make ssl directory
sudo mkdir /etc/apache2/ssl

# change to ssl directory
cd /etc/apache2/ssl/ || exit

# create config file
sudo touch localhost.conf
sudo bash -c 'cat > localhost.conf' << EOL
[req]
default_bits       = 2048
default_keyfile    = $HOSTNAME.key
distinguished_name = req_distinguished_name
req_extensions     = req_ext
x509_extensions    = v3_ca
prompt = no

[req_distinguished_name]
countryName                 = US
stateOrProvinceName         = Minnesota
localityName                = Minneapolis
organizationName            = NSCS
organizationalUnitName      = Development
commonName                  = $HOSTNAME

[req_ext]
subjectAltName = @alt_names

[v3_ca]
subjectAltName = @alt_names

[alt_names]
DNS.1   = localhost
DNS.2   = 127.0.0.1
DNS.3   = $HOSTNAME
DNS.4   = *.$HOSTNAME
EOL

# Generate the certificates
sudo openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout $HOSTNAME.key -out $HOSTNAME.crt -config localhost.conf -passin pass:TEST1234

# Copy to Puphet Generic Cert Path
sudo cp ./$HOSTNAME.key ./certificate.key
sudo cp ./$HOSTNAME.crt ./certificate.crt

# Restart Apache
sudo service apache2 restart

# Copy the certificate to the WordPress directory
sudo cp ./$HOSTNAME.crt /var/www/html/$HOSTNAME.crt