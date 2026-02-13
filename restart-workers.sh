#!/bin/bash

docker compose down scheduler messenger

docker compose up scheduler messenger --no-deps -d
