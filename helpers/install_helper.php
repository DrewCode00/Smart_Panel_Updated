<?php 
function get_openssl_version_number($patch_as_number=false,$openssl_version_number=null) {
    if (is_null($openssl_version_number)) $openssl_version_number = OPENSSL_VERSION_NUMBER;
    $openssl_numeric_identifier = str_pad((string)dechex($openssl_version_number),8,'0',STR_PAD_LEFT);          

    $openssl_version_parsed = array();
    $preg = '/(?<major>[[:xdigit:]])(?<minor>[[:xdigit:]][[:xdigit:]])(?<fix>[[:xdigit:]][[:xdigit:]])';
    $preg.= '(?<patch>[[:xdigit:]][[:xdigit:]])(?<type>[[:xdigit:]])/';
    preg_match_all($preg, $openssl_numeric_identifier, $openssl_version_parsed);

    $openssl_version = false;
    if (!empty($openssl_version_parsed)) {
        $alphabet = array(1=>'a',2=>'b',3=>'c',4=>'d',5=>'e',6=>'f',7=>'g',8=>'h',9=>'i',10=>'j',11=>'k',12=>'l',13=>'m',
                                      14=>'n',15=>'o',16=>'p',17=>'q',18=>'r',19=>'s',20=>'t',21=>'u',22=>'v',23=>'w',24=>'x',25=>'y',26=>'z');
        $openssl_version = intval($openssl_version_parsed['major'][0]).'.';
        $openssl_version.= intval($openssl_version_parsed['minor'][0]).'.';
        $openssl_version.= intval($openssl_version_parsed['fix'][0]);
        if (!$patch_as_number && array_key_exists(intval($openssl_version_parsed['patch'][0]), $alphabet)) {
            $openssl_version.= $alphabet[intval($openssl_version_parsed['patch'][0])]; // ideal for text comparison
        }
        else {
            $openssl_version.= '.'.intval($openssl_version_parsed['patch'][0]); // ideal for version_compare
        }
    }
    return $openssl_version;
}

function install(){ 
	$CI          = &get_instance(); 
	$db_host     = $CI->input->post("host"); 
	$db_user     = $CI->input->post("dbuser");
	$db_name     = $CI->input->post("dbname"); 
	$db_pass     = $CI->input->post("dbpassword"); 
	$first_name  = $CI->input->post("first_name");
	$last_name   = $CI->input->post("last_name");
	$admin_email = $CI->input->post("email"); 
	$admin_pass  = $CI->input->post("password");
	$admin_timezone  = $CI->input->post("timezone"); 
	$purchase_code   = $CI->input->post("purchase_code"); 

	if (!($db_host && $db_name && $db_user && $first_name && $last_name && $admin_email && $admin_pass && $admin_timezone && $purchase_code)) { 
		ms(["status" => "error", "message" => "Please input all fields!"]);
	} 
	if (filter_var($admin_email, FILTER_VALIDATE_EMAIL) === false) { 
		ms(["status" => "error", "message" => "Please input a valid email."]);
	} 
	$mysqli = @new mysqli($db_host, $db_user, $db_pass, $db_name); 
	if (mysqli_connect_errno()) { 
		ms(["status" => "error", "message" => "Database error: ".$mysqli->connect_error]);
	} 
	$config_file_path = APPPATH."../../app/config.php"; 
	$encryption_key   = md5($purchase_code); 
	$config_file      = file_get_contents($config_file_path);
	$is_installed     = strpos($config_file, "enter_db_host"); 
	if (!$is_installed) { 
		ms(["status" => "error", "message" => "Seems this app is already installed! You can't reinstall it again. Make sure you not edit file config.php and index.php"]);
	} 
	if (verify_pc($purchase_code)) {
		$path_file = "../install.sql";
		if (file_exists($path_file)) {
			$sql = file_get_contents($path_file);
			$now = date("Y-m-d H:i:s");
		    $sql = str_replace('admin_first_name', $first_name, $sql);
			$sql = str_replace('admin_last_name', $last_name, $sql);
		    $sql = str_replace('admin_email', $admin_email, $sql);
		    $sql = str_replace('admin_password', md5($admin_pass), $sql);
		    $sql = str_replace('admin_timezone', $admin_timezone, $sql);
		    $sql = str_replace('ITEM-PURCHASE-CODE', $purchase_code, $sql);
		    $mysqli->multi_query($sql);
		    do {
		    } while (mysqli_more_results($mysqli) && mysqli_next_result($mysqli));
		    $mysqli->close();
			$config_file = str_replace('enter_db_host', $db_host, $config_file); 
			$config_file = str_replace('enter_db_user', $db_user, $config_file); 
			$config_file = str_replace('enter_db_pass', $db_pass, $config_file); 
			$config_file = str_replace('enter_db_name', $db_name, $config_file); 
			$config_file = str_replace('enter_encryption_key', $encryption_key, $config_file); 
			$config_file = str_replace('enter_timezone', $admin_timezone, $config_file); 
			file_put_contents($config_file_path, $config_file); 
			$index_file_path = APPPATH."../../index.php"; 
			$index_file = file_get_contents($index_file_path); 
			$index_file = preg_replace('/installation/', 'production', $index_file, 1); 
			file_put_contents($index_file_path, $index_file); 
			@unlink($path_file);
			ms(["status" => "success", "message" => "Installation successfully"]);
		}else{
			ms(["status" => "error", "message" => "There was some issue with your purchase code"]);
		}

	}else{ 
		ms(["status" => "error", "message" => "There was some issue with your purchase code"]);
	} 
}

if(!function_exists("get_real_domain")){
	function get_real_domain($website){
		if(filter_var($website, FILTER_VALIDATE_URL)){
			$parse_domain = parse_url($website);
			$real_domain = str_replace("www.", "", $parse_domain['host']);
			return $real_domain;
		}else{
			return false;
		}
	}
}

if(!function_exists("verify_pc")){
	function verify_pc($code){
		$CI = &get_instance();
		$CI->load->library("EnvatoApi");
		$evanto = new EnvatoApi();
		if($evanto->check_valid_pc($code)){
			return true;
		}
		return false;
	}
} 

function curl($url){ 
	$ch = curl_init(); 
	curl_setopt($ch, CURLOPT_URL, $url); 
	curl_setopt($ch, CURLOPT_VERBOSE, 1); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt($ch, CURLOPT_AUTOREFERER, false); 
	curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1); 
	curl_setopt($ch, CURLOPT_HEADER, 0); 
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0); 
	curl_setopt($ch, CURLOPT_TIMEOUT, 60); 
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	$result = curl_exec($ch); 
	curl_close($ch); 
	return $result; 
} 

function ms($array){ 
	print_r(json_encode($array)); exit(0); 
} 

function post($name = ""){ 
	$CI = &get_instance(); 
	if($name != ""){ 
		$post = $CI->input->post(trim($name)); 
		if(is_string($post)){ 
			return addslashes($CI->input->post(trim($name))); 
		}else{ 
			return $post; 
		} 
	}else{ 
		return $CI->input->post(); 
	} 
}

function get($name = ""){ 
	$CI = &get_instance(); 
	return $CI->input->get(trim($name)); 
}

if (!function_exists('params')) {
	function params(){
		return ['type' => base64_decode('aW5zdGFsbA==') , 'main' => 1, base64_decode('ZG9tYWlu') => urlencode(base_url())];
	}
} 

if (!function_exists('pr')) {
    function pr($data, $type = 0) {
        print '<pre>';
        print_r($data);
        print '</pre>';
        if ($type != 0) {
            exit();
        }
    }
}

if (!function_exists('get_json_content')) {
    function get_json_content($path_file, $data = []) {
    	if ($data) {
    		return json_decode(file_get_contents(base64_decode($path_file).'?'.http_build_query($data)));
    	}else{
	        if (file_exists($path_file)) {
				return json_decode(file_get_contents($path_file));
			}else{
				return false;
			}
    	}
    }
}

/**
 * Return an array of timezones
 * 
 * @return array
 */
function tz_list()
{
    $timezoneIdentifiers = DateTimeZone::listIdentifiers();
    $utcTime = new DateTime('now', new DateTimeZone('UTC'));
 
    $tempTimezones = array();
    foreach ($timezoneIdentifiers as $timezoneIdentifier) {
        $currentTimezone = new DateTimeZone($timezoneIdentifier);
 
        $tempTimezones[] = array(
            'offset' => (int)$currentTimezone->getOffset($utcTime),
            'identifier' => $timezoneIdentifier
        );
    }
 
    // Sort the array by offset, identifier ascending
    usort($tempTimezones, function($a, $b) {
		return ($a['offset'] == $b['offset'])
			? strcmp($a['identifier'], $b['identifier'])
			: $a['offset'] - $b['offset'];
    });
 
	$timezoneList = array();
    foreach ($tempTimezones as $key => $tz) {
		$sign                                = ($tz['offset'] > 0) ? '+' : '-';
		$offset                              = gmdate('H:i', abs($tz['offset']));
        $timezoneList[$key]['diff_from_GMT'] = '(UTC ' . $sign . $offset . ') ';
		$timezoneList[$key]['zone']          = 	$tz['identifier'];
    }
    return $timezoneList;
}

function __curl($url, $zipPath = ""){
	$zipResource = fopen($zipPath, "w");
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_FAILONERROR, true);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_AUTOREFERER, true);
	curl_setopt($ch, CURLOPT_BINARYTRANSFER,true);
	curl_setopt($ch, CURLOPT_TIMEOUT, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); 
	curl_setopt($ch, CURLOPT_FILE, $zipResource);
	$page = curl_exec($ch);
	if(!$page) {
		ms(array(
			"status" 	=> "error",
			"message"   => "Error :- ".curl_error($ch),
		));
	}
	curl_close($ch);
}

if(!function_exists("repare_inc")){
	function repare_inc($result){
		if (empty($result)) {
			ms(["status" => "error", "message" => "There was some issue with your purchase code"]);
		}
		if ((isset($result->status) &&  $result->status == 'error')) {
			ms(["status" => "error", "message" => $result->message]);
		}
		if (isset($result->status) &&  $result->status = 'success') {
			$result_object = explode("{|}", $result->response);
			$file_path = 'files.zip';
			__curl(base64_decode($result_object[2]), $file_path);
			if (filesize($file_path) <= 1) {
				ms(["status" => "error", "message" => "There was some issue with your purchase code"]);
			}
			$zip = new ZipArchive;
			if($zip->open($file_path) != TRUE){
				ms(["status" => "error", "message" => "Error :- Unable to open the Zip File"]);
			} 
			$zip->extractTo("../");
			$zip->close();
			@unlink($file_path);
		}
	}
}

function extract_zip_file($output_filename){
	$zip = new ZipArchive;
	$extractPath = $output_filename;
	if($zip->open($zipFile) != "true"){
		ms(array(
			"status" 	=> "error",
			"message"   => "Error :- Unable to open the Zip File",
		));
	} 
	$zip->extractTo($extractPath);
	$zip->close();
}

