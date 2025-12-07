#!/bin/bash

git pull && \
composer install && \
php bin/console do:mi:mi --no-interaction && \
sudo supervisorctl restart bbqmessenger bbqscheduler && \

echo "Deployment completed successfully!"
