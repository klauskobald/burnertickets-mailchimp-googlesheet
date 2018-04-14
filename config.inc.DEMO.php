<?php
/**
 * User: klausk
 * Date: 10.04.18
 * Time: 14:50
 */

# set this to true for testing phase. This will overwrite data file once and again.
const DEBUG_FORCE_REWRITE=false;

# Burnertickets
$burnertickets_apiKey = "";
$burnertickets_eventId = "";

# these are the burnerticket standardfields
$fields = [
	"UserId", "TicketNumber", "FirstName", "LastName", "Address", "Address2", "Zipcode", "City", "State", "Country", "Phone", "EmailAddress"
];

# these fields have to match the questionaire that burnertickets have setup for you
$extras = [
	"BurnerName",
	"Attend2017",
	"FirstBurn",
	"OtherBurns",
	"FoundFrom",
	"ReadDesciption",
	"Principles",
	"Cocreate",
	"consent",
	"LNT",
	"Skills",
	"Rules"
];


# Mailchimp
$mailchimp_apikey = '';
$mailchimp_list_id = '';

# Google
#   Example 1-8LfFh6HO9381111198XZXf6J3KTtPGwlfPabHRgXrg
$spreadsheetId = "";

# Dreams Platform
$dreams_url="https://YOUR-DREAMS-PLATFORM.herokuapp.com/";
