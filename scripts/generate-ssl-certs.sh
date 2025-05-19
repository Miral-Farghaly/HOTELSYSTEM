#!/bin/bash

# Create directories if they don't exist
sudo mkdir -p /etc/ssl/private
sudo mkdir -p /etc/ssl/certs

# Generate private key
sudo openssl genrsa -out /etc/ssl/private/hotel_system.key 2048

# Generate CSR
sudo openssl req -new -key /etc/ssl/private/hotel_system.key -out /tmp/hotel_system.csr -subj "/C=US/ST=State/L=City/O=Organization/CN=localhost"

# Generate self-signed certificate
sudo openssl x509 -req -days 365 -in /tmp/hotel_system.csr -signkey /etc/ssl/private/hotel_system.key -out /etc/ssl/certs/hotel_system.crt

# Set proper permissions
sudo chmod 600 /etc/ssl/private/hotel_system.key
sudo chmod 644 /etc/ssl/certs/hotel_system.crt

# Clean up
rm /tmp/hotel_system.csr

echo "SSL certificates generated successfully!"
echo "Certificate: /etc/ssl/certs/hotel_system.crt"
echo "Private Key: /etc/ssl/private/hotel_system.key" 