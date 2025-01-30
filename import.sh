#!/bin/bash

# Запускаємо команду для імпорту через docker compose
docker compose -f ./docker/docker-compose.yml run --rm php-cli php cron/import.php