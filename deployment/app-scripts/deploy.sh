#!/bin/bash

git pull && \
composer install && \
php bin/console do:mi:mi && \
sudo supervisorctl restart bbqmessenger bbqscheduler && \

echo "Deployment completed successfully!"
