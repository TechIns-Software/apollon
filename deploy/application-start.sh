#!/usr/bin/env bash

ln -s /etc/nginx/sites-available/default.conf /etc/nginx/sites-enabled/default.conf
sudo -u www-data php /var/www/html/artisan up

service nginx start
service nginx reload
