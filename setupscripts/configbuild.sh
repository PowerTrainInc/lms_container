#!/bin/bash

sed -i -e "s/{DB_HOSTIP}/$DB_HOSTIP/g" /var/www/html/config.php
sed -i -e "s/{DB_NAME}/$DB_NAME/g" /var/www/html/config.php
sed -i -e "s/{DB_USER}/$DB_USER/g" /var/www/html/config.php
sed -i -e "s/{DB_PASS}/$DB_PASS/g" /var/www/html/config.php

sed -i -e "s/{SITE_URL}/$SITE_URL/g" /var/www/html/config.php
sed -i -e "s/{SITE_MOODLEDATA}/$SITE_MOODLEDATA/g" /var/www/html/config.php

sed -i -e "s/{PASSWORD_POLICY}/$PASSWORD_POLICY/g" /var/www/html/config.php
sed -i -e "s/{PASSWORD_LENGTH}/$PASSWORD_LENGTH/g" /var/www/html/config.php
sed -i -e "s/{MIN_DIGITS}/$MIN_DIGITS/g" /var/www/html/config.php
sed -i -e "s/{MIN_LOWERCASE}/$MIN_LOWERCASE/g" /var/www/html/config.php
sed -i -e "s/{MIN_UPPERCASE}/$MIN_UPPERCASE/g" /var/www/html/config.php
sed -i -e "s/{MIN_NONALPHA}/$MIN_NONALPHA/g" /var/www/html/config.php
sed -i -e "s/{MAX_CONSECUTIVE}/$MAX_CONSECUTIVE/g" /var/www/html/config.php
sed -i -e "s/{MIN_ROTAIONREUSE}/$MIN_ROTAIONREUSE/g" /var/www/html/config.php
sed -i -e "s/{PASSWORD_FORCELOGOUT}/$PASSWORD_FORCELOGOUT/g" /var/www/html/config.php

sed -i -e "s/{LOCKOUT_THRESHOLD}/$LOCKOUT_THRESHOLD/g" /var/www/html/config.php
sed -i -e "s/{LOCKOUT_WINDOW}/$LOCKOUT_WINDOW/g" /var/www/html/config.php
sed -i -e "s/{LOCKOUT_DURATION}/$LOCKOUT_DURATION/g" /var/www/html/config.php

sed -i -e "s/{CRON_CLIONLY}/$CRON_CLIONLY/g" /var/www/html/config.php
sed -i -e "s/{GUESTLOGINBUTTON}/$GUESTLOGINBUTTON/g" /var/www/html/config.php

sed -i -e "s/{SECURE_COOKIES}/$SECURE_COOKIES/g" /var/www/html/config.php
sed -i -e "s/{HTTP_ONLY_COOKIES}/$HTTP_ONLY_COOKIES/g" /var/www/html/config.php