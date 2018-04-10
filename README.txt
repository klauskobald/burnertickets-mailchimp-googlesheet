@author: Klaus Kobald (aka Nagu Whiteout - the naked DJ)
@date: 2018-04-10

GOAL

Automatically get tickets from burnertickets.com and
store them into mailchimp and put them into a google sheet.

Warning:
The folder google-api-php-client might change in the future. If something is
not working, you might have to get a new version. Currently we are running v4.


PREREQUESITE

1.
From burnertickets get
api key, event id
(we asume that url does not change - it´s hardcoded in burner_tickets_to_mailchimp.php)

2.
From mailchimp get
api key, list id
(we asume that url does not change - it´s hardcoded in burner_tickets_to_mailchimp.php)


INSTALL DEPENDENCIES

cd google-api-php-client/
composer install

(you might have to install compser first!)


SETUP CONFIG

Rename config.inc.DEMO.php to config.inc.php and fill in the correct values


SETUP FILES

Make var/ writeable for the user that is running the scripts.
If you run as root which is most likely so when using cron
you don´t have to bother with this.


TEST

php burner_tickets_to_mailchimp.php
This should output something like
2018-04-10 15:01:51 query burnertickets
2018-04-10 15:01:52 found records: 3
2018-04-10 15:01:52 new x@y.net
2018-04-10 15:01:53   failed: Member Exists
2018-04-10 15:01:53 new j@test.com
2018-04-10 15:01:54   failed: Member Exists
2018-04-10 15:01:54 new a@gmail.com
2018-04-10 15:01:55   failed: Member Exists
2018-04-10 15:01:55 failed: 3
2018-04-10 15:01:55 done.

"failed" in this case means, that the emails do already exist in mailchimp. So that´ ok.


SETUP GOOGLE SHEET

This information is vague - sorry for that - I did not fully document the journey
Create a new sheet and copy it´s ID into the config.inc.php
Navigate to https://console.developers.google.com/apis/
Create a project and enable the Google Sheets API
Goto Credentials > OAuth consent Screen and fill out the required fields
Back to Credentials > Create credentials > OAuth Client ID
Run
php add_to_google_sheet.php
During the first call this should bring up a verification dialog
- follow the instructions and paste the token you get into the console.
This will save the credentials into /root/.credentials/...

After this, every call should start the sync.

Output looks like
2018-04-10 09:07:39 update google sheet
2018-04-10 09:07:39 append 1
2018-04-10 09:07:39 append 12
2018-04-10 09:07:40 append 3580
2018-04-10 09:07:40 done.

The script will first read the googlesheet and will only append records with new user IDs
The new rows immediatly appear in the google doc as the script runs.



SETUP CRON

/etc/cron.hourly/burnertickets_pull
############
#!/bin/bash
php /var/www/html/burners.kobald.com/burner_tickets_to_mailchimp.php > /var/log/burnertickets_pull.log
php /var/www/html/burners.kobald.com/add_to_google_sheet.php >> /var/log/burnertickets_pull.log
#
############


VIEW DATABASE

view_records.php will simply show the var/data.json content


