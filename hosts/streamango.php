<?php
set_time_limit(0);
ignore_user_abort(true);
error_reporting(E_ALL);
ini_set("display_errors",1);
$path = '/var/www/html/bot/';
require_once $path . 'inc/func.php';
$func = new Func();

$_POST['link'] = false;
echo "Uploading to streamango.com .... \n";

if(!$_POST['link'] = $func->streamango($_POST['mangoUser'],$_POST['mangoPass'],$_POST['videoFile']))
	if(!$_POST['link'] = $func->streamango($_POST['mangoUser'],$_POST['mangoPass'],$_POST['videoFile']))
		if(!$_POST['link'] = $func->streamango($_POST['mangoUser'],$_POST['mangoPass'],$_POST['videoFile']))
				echo "Uploading to streamango.com Failed... \n";
if($_POST['link'])
{
	echo "\nPosting streamango link on IWO... \n";
	$func->curl('https://www.iwatchonline.cr/tools/postbot',$_POST);
}