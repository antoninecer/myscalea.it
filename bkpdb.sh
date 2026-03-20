#!/bin/bash

DB="myscalea"
USER="myscalea_user"
PASS="superheslo"
# Získání všech tabulek s prefixem piwigo_
IGNORED=$(mysql -u "$USER" -p"$PASS" -N -e "SELECT CONCAT('--ignore-table=$DB.', table_name) FROM information_schema.tables WHERE table_schema='$DB' AND table_name LIKE 'piwigo_%';")

# Vytvoření zálohy bez piwigo_ tabulek
mysqldump -u "$USER" -p"$PASS" $IGNORED "$DB" > /tmp/myscalea.sql

