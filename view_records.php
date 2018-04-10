<pre><?php
/**
 * User: klausk
 * Date: 04.04.18
 * Time: 09:38
 */
chdir(__DIR__);
const DATA_PATH = "./var/data.json";
$data = json_decode(file_get_contents(DATA_PATH), JSON_OBJECT_AS_ARRAY);

print_r(json_encode($data,JSON_PRETTY_PRINT));
