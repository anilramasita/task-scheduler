#!/bin/bash

# Get the full path to the cron.php file
CRON_PATH="$(pwd)/cron.php"
PHP_PATH=$(which php)

# Add the cron job if it doesn't exist
(crontab -l 2>/dev/null | grep -Fv "$CRON_PATH"; echo "0 * * * * $PHP_PATH $CRON_PATH") | crontab -

echo "Cron job set up successfully to run every hour."
