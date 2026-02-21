# threed-catalogue
three d's old php catalogue

Fixed up handling of some odd characters in searches and also the needsEncoding field in queries added by Michael to handle Digital Submission file encoding.
affects cdedit.php cdsearchadv.php cdsearch.php cdshow.php session_report_hi.php
Also fixed URLs to use https intead of http

Requires PGSQL-PHP
 - sudo apt-get install php-pgsql on Ubuntu/Debian
Requires PHP-curl
 - sudo apt-get install php-curl on Ubuntu/Debian