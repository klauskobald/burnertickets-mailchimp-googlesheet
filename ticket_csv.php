<?php
/**
 * User: klausk
 * Date: 11.04.18
 * Time: 00:19
 */

# on heroku set the env variable IMPORT_CSV_URL to point to this files URL


chdir(__DIR__);
require 'config.inc.php';
const DATA_PATH = "./var/data.json";

$data = json_decode(file_get_contents(DATA_PATH), JSON_OBJECT_AS_ARRAY);

foreach($data["records"] as $email=>$u){
	echo "$email,{$u['TicketNumber']}\n";
}