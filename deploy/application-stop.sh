#!/usr/bin/env bash

sudo -u www-data php /var/www/html/app/artisan down

rm -rf /etc/nginx/sites-enabled/default.conf
service nginx reload
service nginx stop
