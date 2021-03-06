<pre><?php
/**
 * User: klausk
 * Date: 04.04.18
 * Time: 09:38
 */
e(basename(__FILE__));

chdir(__DIR__);

require 'config.inc.php';

const DATA_PATH = "./var/data.json";
const RUN_FILE = "./var/run";
const NEW_FILE = "./var/new";



if (!is_dir(dirname(DATA_PATH))) mkdir(dirname(DATA_PATH));
@touch(DATA_PATH);
if (!is_dir(dirname(DATA_PATH))) die("- cannot create " . DATA_PATH);

if (is_file(RUN_FILE) && filectime(RUN_FILE)>time()-300) die("- process is locked. found " . RUN_FILE);
@touch(RUN_FILE);

e("query burnertickets");
$req="https://burnertickets.com/BurnerTicketing/API/index.php?method=GetUsersWithTicketsEventId&eventId=$burnertickets_eventId&apiKey=$burnertickets_apiKey";
e($req);
$str = file_get_contents($req);
$bt = json_decode($str, JSON_OBJECT_AS_ARRAY);
#print_r($bt); die(); # VIEW THE SHIT

if ($bt["code"] != 1) die('- unexpected result: ' . $str);

e("found records:", count($bt["message"]));
require "MailChimp.php";

$data = json_decode(file_get_contents(DATA_PATH), JSON_OBJECT_AS_ARRAY);

use DrewM\MailChimp\MailChimp;

$MailChimp = new MailChimp($mailchimp_apikey);

if (!is_array($data)) {
	$data = array("records" => array());
}

$ct = 0;
$failed = 0;
foreach ($bt["message"] as $u) {

	$key = $u["EmailAddress"];
	if (!DEBUG_FORCE_REWRITE && array_key_exists($key, $data["records"]))
		continue;
	e("new " . $key);

	$result = $MailChimp->post(
		"lists/$mailchimp_list_id/members", [
			'email_address' => $u["EmailAddress"],
			'merge_fields'  => [
				'NAME'         => $u["FirstName"] . " " . $u["LastName"],
				'TICKETNUMBER' => $u["TicketNumber"]
			],
			'status'        => 'subscribed',
		]
	);

	$req="https://burnertickets.com/BurnerTicketing/API/index.php?method=GrabUsersCustomEventInfo&eventId=$burnertickets_eventId&apiKey=$burnertickets_apiKey&userId=" . $u["UserId"];
	e($req);
	$str = file_get_contents($req);
	$userCustomInfo = json_decode($str, JSON_OBJECT_AS_ARRAY);
#	print_r($userCustomInfo);
	$u["extra"] = array();
	foreach ((array)$userCustomInfo["message"] as $m) {
		$mkey = str_replace($burnertickets_eventId . "_", "", $m["meta_key"]);
		$u["extra"][$mkey] = $m["meta_value"];
	}

	$data["records"][$key] = $u;
	if ($result["id"]) {
		$ct++;
		e("  insert ok");
	} else {
		e("  failed:", $result["title"]);
		$failed++;
	}
}
if ($ct || DEBUG_FORCE_REWRITE) {
	$data["updated_at"] = date("Y-m-d H:i:s");
	file_put_contents(DATA_PATH, json_encode($data));
	touch(NEW_FILE);
}



unlink(RUN_FILE);
if ($failed) e("failed:", $failed);
e("done.");

function e() {
	echo date("Y-m-d H:i:s") . " " . join(" ", func_get_args()) . "\n";
}