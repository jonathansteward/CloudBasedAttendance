#!/bin/sh
set -ex

/usr/bin/mysql \
    -u $RDS_USERNAME \
    -p$RDS_PASSWORD \
    -h $RDS_HOSTNAME \
    $RDS_DB_NAME \
    -e 'CREATE TABLE IF NOT EXISTS attend(id INT UNSIGNED NOT NULL AUTO_INCREMENT, date1 VARCHAR(63) NOT NULL , name VARCHAR(63) NOT NULL, location TEXT, PRIMARY KEY (id))'
