# OTRS_DB_API
A json/xml API for fetching and updating tickets in OTRS without using OTRS.

Usage:<br>
Place these files on your OTRS instance somewhere in /var/www/html. This would allow you to access the API via http://yourotrsinstancedomain.tld/api/ and the Test page via http://yourotrsinstancedomain.tld/test/ without disrupting your OTRS experience.<br><br>
NOTE: This is meant to be an api accessed via server side scripting on another server. The test directory is merely a POC and offers a security risk if using customer facing as you can manipulate the javascript.


