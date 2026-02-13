#!/bin/bash

# Build php container
docker compose build;

# Generate dev secrets
php bin/console secrets:generate --quiet > /dev/null

# Start the containers
POSTGRES_PASSWORD=barbequeue \
POSTGRES_USER=barbequeue \
 POSTGRES_DB=barbequeue \
 docker compose up -d;
