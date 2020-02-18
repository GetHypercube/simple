#!/bin/bash
set -e


# Update the application name
sed -i "s/newrelic.appname = \"PHP Application\"/newrelic.appname = \"${NR_APP_NAME}\"/" /usr/local/etc/php/conf.d/newrelic.ini
sed -i "s/newrelic.license = \"REPLACE_WITH_REAL_KEY\"/newrelic.license = \"${NR_INSTALL_KEY}\"/" /usr/local/etc/php/conf.d/newrelic.ini

sed -i "s/newrelic.appname = \"\"/newrelic.appname = \"${NR_APP_NAME}\"/" /usr/local/etc/php/conf.d/newrelic.ini
sed -i "s/newrelic.license = \"\"/newrelic.license = \"${NR_INSTALL_KEY}\"/" /usr/local/etc/php/conf.d/newrelic.ini

### se desactivando modulo browser
sed -i "s/;newrelic.browser_monitoring.auto_instrument = true/newrelic.browser_monitoring.auto_instrument = false" /usr/local/etc/php/conf.d/newrelic.ini

echo "Start deamon New Relic"
/etc/init.d/newrelic-daemon start
 
echo "Launch fpm-php"
php-fpm
