<?php
set_time_limit(0);
ignore_user_abort(true);
error_reporting(E_ALL);
ini_set("display_errors",1);
$path = '/var/www/html/bot/';
require_once $path . 'inc/func.php';
$func = new Func();

//$_POST = explode("|||",base64_decode($_POST));
$_POST['link'] = false;
echo "Uploading to filenuke.com ... \n";
if(!$_POST['link'] = $func->filenuke($_POST['fnukUser'],$_POST['fnukPass'],$_POST['cookie'],$_POST['videoFile']))
	if(!$_POST['link'] = $func->filenuke($_POST['fnukUser'],$_POST['fnukPass'],$_POST['cookie'],$_POST['videoFile']))
		if(!$_POST['link'] = $func->filenuke($_POST['fnukUser'],$_POST['fnukPass'],$_POST['cookie'],$_POST['videoFile']))
				echo "Uploading to filenuke.com Failed... \n";
if($_POST['link'])
{
	echo "Posting filenuke.com link on IWO... \n";
	$func->curl('https://www.iwatchonline.cr/tools/postbot',$_POST);
}