#!/usr/bin/env bash

DIR=/var/www/html

chown -R www-data:www-data $DIR

sudo -u www-data php $DIR/artisan migrate --force
sudo -u www-data php $DIR/artisan config:clear
sudo -u www-data php $DIR/artisan cache:clear
sudo -u www-data php $DIR/artisan route:clear
sudo -u www-data php $DIR/artisan optimize
