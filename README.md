# Munchy PHP + MySQL deployment
Requires PHP 8.1+ with pdo_mysql, MySQL/MariaDB, HTTPS. No Node.js.
1. Back up the existing site. Extract all ZIP contents into /www/wwwroot/munchy.news/ (or actual document root). Preserve .user.ini, .well-known and hosting settings. Replace index.html and 404.html; merge .htaccess if needed.
2. Edit db-config.php: database name, username, password, host, port. localhost is default for same-server database. 194.238.19.86 was described as remote access permission, not confirmed database hostname. Use actual database host from panel. Never share credentials.
3. Select existing database in phpMyAdmin and import schema.sql, or allow first signup to create table if account has CREATE permission. If an existing waitlist table has different columns, back up and reconcile its schema first. No existing data is deleted.
4. Visit https://munchy.news/waitlist.html and submit a test email. Check waitlist table in phpMyAdmin. Repeat and confirm only one row.
5. Enable PHP handler and disable display_errors. For Apache, included .htaccess requires AllowOverride. Nginx ignores .htaccess: add the following to your existing server block without replacing PHP handler:
```
index index.html index.php;
error_page 404 /404.html;
location = /db-config.php { deny all; }
location = /schema.sql { deny all; }
location = /README.md { deny all; }
```
Use restricted database grants SELECT, INSERT and CREATE only if needed. Restrict remote access to web server IP, not Everyone. For remote DB configure verified encrypted database transport with your host before production use. Consider server-level rate limiting/CAPTCHA.
Design, images, loader and scroll animations preserved from Munchy-Final-Website; uploaded older hosting backup had no waitlist and different design. Live database and browser visuals not tested; CLI lacks pdo_mysql. No real credentials bundled. Launch emails are not sent automatically.

## Updated edition
News-reading phone mockups replace video-style mockups in the hero, loader, feature cards and scroll story. The mockups are generated illustrations, not actual news or final app screenshots. Only the Stay curious feature icon has been replaced (lightbulb, retaining spark.webp filename). Other feature icons are unchanged. Both landing-page and dedicated waitlist forms submit to join.php. PHP and JS syntax and local image references verified; browser navigation blocked by environment policy and live MySQL not tested. Set database credentials before deploying. No real credentials included.
