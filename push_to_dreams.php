<?php
/**
 * User: klausk
 * Date: 11.04.18
 * Time: 00:28
 *
 * Deploy this branch of the dreams platform!
 * https://github.com/klauskobald/dreams
 *
 */
e(basename(__FILE__));
chdir(__DIR__);
if(!file_exists("./var/new") && !$argv[1]) {
	e("no changes");
	die();
}

require 'config.inc.php';
e("starting import on dreams platform");
$r = file_get_contents($dreams_url . "import");
if (strpos("$r", "Import File")) {
	e("sucess");
	@unlink("./var/new");
}
else
	e("failed");

function e() {
	echo date("Y-m-d H:i:s") . " " . join(" ", func_get_args()) . "\n";
}