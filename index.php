use Sabre\DAV\Client;

include 'vendor/autoload.php';

$settings = array(
    'baseUri' => 'https://webdav.yandex.ru',
    'userName' => 'user',
    'password' => 'pass123',
    // 'proxy' => 'locahost:8888',
);

$client = new Client($settings);

//////////// DELETE OLD

$response =  $client->propfind(
	'backups', // dir
	array(
    '{DAV:}displayname',
    '{DAV:}creationdate',
	) , // props
	1 // depth
);

function compareByTimeStamp($time1, $time2)
{
    if (strtotime($time1) < strtotime($time2))
        return 1;
    else if (strtotime($time1) > strtotime($time2)) 
        return -1;
    else
        return 0;
}

array_shift($response);
$dates = array_column($response, '{DAV:}displayname');

sort($dates, "compareByTimeStamp");

 // $method, $url = '', $body = null, array $headers = []
$response = $client->request('DELETE', 'dnkayuqs_backups/'.$dates[0] , null, array());
print_r($response['statusCode']) . "\n";