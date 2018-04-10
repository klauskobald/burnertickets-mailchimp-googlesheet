<?php
/**
 * User: klausk
 * Date: 08.04.18
 * Time: 23:59
 */

// MAIN CODE IS ALL THE WAY DOWN
e(basename(__FILE__));
chdir(__DIR__);
if(!file_exists("./var/new")) {
	e("no changes");
	die();
}
require 'config.inc.php';
const DATA_PATH = "./var/data.json";

require_once __DIR__ . '/google-api-php-client/vendor/autoload.php';

define('APPLICATION_NAME', 'Nagus-App');
define('CREDENTIALS_PATH', '~/.credentials/sheets.googleapis.com-php-nagu.json');
define('CLIENT_SECRET_PATH', __DIR__ . '/client_secret.json');
// If modifying these scopes, delete your previously saved credentials
// at ~/.credentials/sheets.googleapis.com-php-quickstart.json
define(
	'SCOPES', implode(
	' ', array(
		Google_Service_Sheets::SPREADSHEETS)
)
);

date_default_timezone_set('America/New_York'); // Prevent DateTime tz exception
if (php_sapi_name() != 'cli') {
	throw new Exception('This application must be run on the command line.');
}

/**
 * Returns an authorized API client.
 * @return Google_Client the authorized client object
 */
function getClient() {
	$client = new Google_Client();
	$client->setApplicationName(APPLICATION_NAME);
	$client->setScopes(SCOPES);
	$client->setAuthConfig(CLIENT_SECRET_PATH);
	$client->setAccessType('offline');

	// Load previously authorized credentials from a file.
	$credentialsPath = expandHomeDirectory(CREDENTIALS_PATH);
	if (file_exists($credentialsPath)) {
		$accessToken = json_decode(file_get_contents($credentialsPath), true);
	} else {
		// Request authorization from the user.
		$authUrl = $client->createAuthUrl();
		printf("Open the following link in your browser:\n%s\n", $authUrl);
		print 'Enter verification code: ';
		$authCode = trim(fgets(STDIN));

		// Exchange authorization code for an access token.
		$accessToken = $client->fetchAccessTokenWithAuthCode($authCode);

		// Store the credentials to disk.
		if (!file_exists(dirname($credentialsPath))) {
			mkdir(dirname($credentialsPath), 0700, true);
		}
		file_put_contents($credentialsPath, json_encode($accessToken));
		printf("Credentials saved to %s\n", $credentialsPath);
	}
	$client->setAccessToken($accessToken);

	// Refresh the token if it's expired.
	if ($client->isAccessTokenExpired()) {
		$client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
		file_put_contents($credentialsPath, json_encode($client->getAccessToken()));
	}
	return $client;
}

/**
 * Expands the home directory alias '~' to the full path.
 * @param string $path the path to expand.
 * @return string the expanded path.
 */
function expandHomeDirectory($path) {
	$homeDirectory = getenv('HOME');
	if (empty($homeDirectory)) {
		$homeDirectory = getenv('HOMEDRIVE') . getenv('HOMEPATH');
	}
	return str_replace('~', realpath($homeDirectory), $path);
}


//////////////////   MAIN
//

// Get the API client and construct the service object.
$client = getClient();
$service = new Google_Service_Sheets($client);

$range = 'A1:A';
$response = $service->spreadsheets_values->get($spreadsheetId, $range);
$currentValues = [];

$fields=[
	"UserId","TicketNumber","FirstName","LastName","Address","Address2","Zipcode","City","State","Country", "Phone","EmailAddress"
];

$conf = ["valueInputOption" => "RAW"];
$range = 'A1';
$postBody = new Google_Service_Sheets_ValueRange();

$vals = $response->getValues();
if ($vals) {
	foreach ($vals as $a) {
		$currentValues[$a[0]] = true;
	}
	$writeFirstRow=false;
}
else
	$writeFirstRow=true;

$data = json_decode(file_get_contents(DATA_PATH), JSON_OBJECT_AS_ARRAY);

e("update google sheet");
foreach ((array)$data["records"] as $u) {

	if($writeFirstRow){
		$writeFirstRow=false;
		$d=$fields;
		$d[]="ANSWERS =>";
		foreach($u["extra"] as $k=>$v)
			$d[]=strval($k);
		$postBody->setValues(["values" => array_values($d)]);
		$response = $service->spreadsheets_values->append($spreadsheetId, $range, $postBody, $conf);

	}

	if ($currentValues[$u["UserId"]])
		continue;

	e("append",$u["UserId"]);
	$d = [];
	foreach($fields as $f)
		$d[]=strval($u[$f]);
	$d[]="";
	foreach($u["extra"] as $v)
		$d[]=strval($v);

	$postBody->setValues(["values" => array_values($d)]);
	$response = $service->spreadsheets_values->append($spreadsheetId, $range, $postBody, $conf);

}

e("done.");
#print_r($response);


function e() {
	echo date("Y-m-d H:i:s") . " " . join(" ", func_get_args()) . "\n";
}