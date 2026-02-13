#!/bin/bash

php bin/console doctrine:schema:drop --env=test --force
php bin/console doctrine:schema:create --env=test --no-interaction
php bin/console doctrine:fixtures:load --env=test --no-interaction
