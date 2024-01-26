<?php

session_start();
$session_id = session_id();

if(isset($_SERVER["HTTP_ORIGIN"]))
{
    // You can decide if the origin in $_SERVER['HTTP_ORIGIN'] is something you want to allow, or as we do here, just allow all
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
}
else
{
    //No HTTP_ORIGIN set, so we allow any. You can disallow if needed here
    header("Access-Control-Allow-Origin: *");
}

header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


if($_SERVER["REQUEST_METHOD"] == "OPTIONS")
{
    if (isset($_SERVER["HTTP_ACCESS_CONTROL_REQUEST_METHOD"]))
        header("Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE, PUT"); //Make sure you remove those you do not want to support

    if (isset($_SERVER["HTTP_ACCESS_CONTROL_REQUEST_HEADERS"]))
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

    //Just exit with 200 OK with the above headers for OPTIONS method
    exit(0);
}
//From here, handle the request as it is ok

define( 'ID', 'InHiring');
define( 'META_TITLE', 'InHiring');
define( 'META_KEYWORD', 'inhiring.com');
define( 'META_DESCRIPTION', 'inhiring.com');
define( 'DB_HOST', '103.159.50.243' );
define( 'DB_PORT', '5432' );
define( 'DB_NAME', 'inhiringdb' );
define( 'DB_USER', 'postgres' );
define( 'DB_PASSWORD', 'ZNBhAKtkwNTR' );
define( 'LANGUAGE', 'vi' );
define( 'COMPANY_ID', 'ROOT' );
define( 'CURRENCY_ID', '23' );
define( 'COMPANY_NAME', 'DIGISOURCE' );
define( 'COMPANY_LAT', '10.861177053727173' );
define( 'COMPANY_LNG', '106.74004260552694' );
define( 'URL', 'https://beinhiring.inhiring.in/' );
define( 'CONTACT_EMAIL', '');
define( 'CONTACT_TEL', '(+84) ');
define( 'CONTACT_ADDRESS', 'Tp. Hồ Chí Minh');
define( 'SERVICE_URL', 'http://beinhiring.inhiring.io:2021/');
define( 'WS_URL', 'ws://beinhiring.inhiring.io:2022/');
define( 'ODD_COLOR', "#fffde7");
define( 'EVEN_COLOR', "#fff");
define( 'thousands_point', ",");
define( 'decimal_point', ".");
define( 'date_format', "YYYY-MM-DD");
define( 'copyright', "All Rights Reserved by inhiring.com");
define( 'addons', "addons");
define( 'db_type', "postgresql");
define( 'developer', "0");
define( 'HOME', "home");
define( 'SUPPORT_EMAIL', "no-reply@mhb4xg5ldz.emailserver.net.vn");
define( 'DEFAULT_DOC_PATH', "/www/wwwroot/devin.inhiring.io/disk/");
define( 'GOOGLE_API_KEY', '');
define( 'TOKEN_AUTHENTICATION', "true");
define( 'SECRET_KEY', "bGS6lzFqvvSQ8ALbOxatm7/Vk7mLQyzqaS34Q4oR1ew=");
define( 'TIME_TO_LIVE', "8640000");
define( 'ALG', "HS256");

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

if(DEFAULT_DOC_PATH == "")
{
	define("DOC_PATH", dirname( __FILE__ )."/disk/");
}else{
	define("DOC_PATH", DEFAULT_DOC_PATH);
}
	
require_once( ABSPATH . 'app/AppSession.php' );
require_once( ABSPATH . 'app/data/DataTier.php' );
require_once( ABSPATH . 'app/data/PostgreSQLTier.php' );

if(isset($_COOKIE["vd_id"]))
{
	$session_id = $_COOKIE["vd_id"];
}else
{
	 setcookie("vd_id", $session_id, time() + (10 * 365 * 24 * 60 * 60), '/');
}

$appSession = new AppSession($session_id);
if($appSession->getConfig()->hasKey("currency_id") == false)
{
	$appSession->getConfig()->setProperty("currency_id", CURRENCY_ID);
}
if($appSession->getConfig()->hasKey("thousands_sep") == false)
{
	$appSession->getConfig()->setProperty("thousands_sep", thousands_point);
}

if($appSession->getConfig()->hasKey("decimal_point") == false)
{
	$appSession->getConfig()->setProperty("decimal_point", decimal_point);
}
if($appSession->getConfig()->hasKey("date_format") == false)
{
	$appSession->getConfig()->setProperty("date_format", date_format);
}
if($appSession->getConfig()->getProperty("addons") == "")
{
	$appSession->getConfig()->setProperty("addons", addons);
}
$appSession->getConfig()->setProperty("service_url", SERVICE_URL);
$appSession->getConfig()->setProperty("ws_url", WS_URL);
$appSession->getConfig()->setProperty("company_id", COMPANY_ID);
if(db_type == "postgresql")
{
	$dataTier = new PostgreSQLTier(DB_NAME, DB_HOST, DB_PORT, DB_USER, DB_PASSWORD);
	$appSession->setTier($dataTier);
}else{
$dataTier = new DataTier(SERVICE_URL, "", "");
$appSession->setTier($dataTier);
}


require_once(ABSPATH.'app/lang/Language.php' );
$session_id = $appSession->getConfig()->getProperty("session_id");
if($session_id == "")
{
	$session_id = $appSession->getTool()->getId();
	$appSession->getConfig()->setProperty("session_id", $session_id);
	$appSession->getConfig()->save();
}
$lang_id = $appSession->getConfig()->getProperty("lang_id");
if($lang_id == "")
{
	if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
	{
		$default_lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
		$sql = "SELECT id FROM res_lang WHERE code iLIKE '%".$default_lang."%' AND status =0";
		$msg = $appSession->getTier()->createMessage();
		$msg->add("query", $sql);
		$arr = $appSession->getTier()->getArray($msg);
		if(count($arr)>0)
		{
			$lang_id = $arr[0][0];
			
		}else
		{
			$lang_id = "vi";
		}
		$appSession->getConfig()->setProperty("lang_id", $lang_id);
		$appSession->getConfig()->save();
	}
}
$routing = new Language();
$routing->load($appSession->getTier(), "web_routes", $lang_id);