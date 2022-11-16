# WACTF Mellivora

✌✌ THIS REPO IS PUBLIC 🔐🔐

A WACTFified Mellivora

- WACTF theme
- No Mellivora registrations + No link to register page
- Start/End dates hardcoded into `general.inc.php`
- Start/End dates automatically fill in new/edited challenges
- Layout included Google Analytics + WACTF background
- Remove forgotten password capability (Mellivora can't email)
- Add 🎉 before eligible teams
- Order challenges by title 1./2./3. etc rather than points
- Remove country flags from scoreboard  
- Add a modern scoreboard with the "Dashboard" page
- Adding categories and challenges should use correct hardcoded defaults

## Timezone stuff
Make sure the server is in the correct timezone... `sudo timedatectl set-timezone Australia/Perth`
Make sure the db iis in the correct timezone! `/etc/mysql/conf.d/mysql.cnf`
```
[mysql]
default-time-zone = "+08:00"
```

## VHost
```
<VirtualHost *:80>

   ServerAdmin contact@yourdomain.com
   ServerName scoreboard.wac.tf
   DocumentRoot /var/www/mellivora/htdocs

   <Directory "/var/www/mellivora/htdocs">
      Options -Indexes +MultiViews
      AllowOverride None
      Order Deny,Allow
      Require all granted
      AddType application/x-httpd-php .php
   </Directory>

### Development

   # error log
   ErrorLog /var/log/apache2/mellivora-error.log
   LogLevel warn

   # access log
   CustomLog /var/log/apache2/mellivora-access.log combined

</VirtualHost>
```

## Increase file upload limit (for small artefacts)
```
sudo nano /etc/php/7.4/apache2/php.ini
upload_max_filesize = 20M
post_max_size = 18M
sudo systemctl reload apache2.service
```

## Certbot

`sudo certbot --apache` `1,2` `E`
