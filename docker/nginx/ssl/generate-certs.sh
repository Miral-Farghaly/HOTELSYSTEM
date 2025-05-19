#!/bin/bash

# Generate SSL certificates for development
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
    -keyout server.key -out server.crt \
    -subj "/C=US/ST=State/L=City/O=Organization/CN=localhost"

# Set proper permissions
chmod 600 server.key
chmod 644 server.crt

echo "SSL certificates generated successfully" 