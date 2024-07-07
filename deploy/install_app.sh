#!/usr/bin/env bash

DIR=/var/www/html/app

chown -R www-data:www-data $DIR

chmod 755 $DIR
find ${DIR} -type f -exec chmod 644 {} \;
chmod -R 775 ${DIR}/storage
chmod -R 775 ${DIR}/bootstrap/cache

mv ${DIR}/deploy/default.conf /etc/nginx/sites-available/default

sudo -u www-data php $DIR/artisan migrate --force
sudo -u www-data php $DIR/artisan config:clear
sudo -u www-data php $DIR/artisan cache:clear
sudo -u www-data php $DIR/artisan route:clear
sudo -u www-data php $DIR/artisan optimize
