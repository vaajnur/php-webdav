<?
use Sabre\DAV\Client;

include 'vendor/autoload.php';

$settings = array(
    'baseUri' => 'https://webdav.yandex.ru',
    'userName' => 'user',
    'password' => 'pass123',
    // 'proxy' => 'locahost:8888',
);

$LOCAL_back_folder = 'backups';
$YA_back_folder = 'phpjs_backup';

$client = new Client($settings);

$response =  $client->propfind(
	$YA_back_folder, // dir
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

usort($dates, "compareByTimeStamp");
$dates = array_reverse($dates);

//////////// DELETE OLD
 // $method, $url = '', $body = null, array $headers = []
$response = $client->request('DELETE', "$YA_back_folder/".$dates[0] , null, array());

$backup_date_arr = scandir( dirname(__DIR__) . "/$LOCAL_back_folder");
// избавляемся от родит. директорий
$backup_date = $backup_date_arr[2];
$backup_files = scandir(dirname(__DIR__) . "/$LOCAL_back_folder/" . $backup_date);
unset($backup_files[0]);
unset($backup_files[1]);

$response = $client->request('MKCOL', "$YA_back_folder/". $backup_date ,  null);
foreach ($backup_files as $key => $backup_file) {
	$file = fopen(dirname(__DIR__) ."/$LOCAL_back_folder/" . $backup_date . "/$backup_file", 'r');
	$url = "$YA_back_folder/{$backup_date}/$backup_file";
        // заменяем пробелы дабы не было 400 ошибки (Bad Message 400 reason: Unknown Version)
	$url = str_replace ( ' ', '%20', $url);
	$response = $client->request('PUT',  $url,  $file );
}