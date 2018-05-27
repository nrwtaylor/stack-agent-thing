# vim /root/scripts/php_fatal_error.sh
#!/bin/sh

# Grep PHP Fatal Error
tail -n 100000 /var/log/apache2/error.log |awk -F'PHP Fatal error' '{print $2}'| grep -v '^$'| sort | uniq -c | sort -nr | head -n20| sed G > /tmp/php_fatal_error.log

# send the email with the unix/linux mail command
mail -s "PHP Fatal Error Report" nonnom < /tmp/php_fatal_error.log
