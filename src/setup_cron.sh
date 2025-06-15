#!/bin/bash
CRON_JOB="0 * * * * /c/PHP/php $(pwd)/cron.php"
(crontab -l 2>/dev/null | grep -v 'cron.php'; echo "$CRON_JOB") | crontab -
echo "CRON job installed to run every hour."