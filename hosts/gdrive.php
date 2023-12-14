<?php
set_time_limit(0);
ignore_user_abort(true);
error_reporting(E_ALL);
ini_set("display_errors",1);
$path = '/var/www/html/bot/';
require_once $path . 'inc/func.php';
$func = new Func();

$_POST['link'] = false;
echo "Uploading to GDrive ... \n";
if(!$_POST['link'] = $func->gdrive($_POST['videoFile'], $_POST['gdriveCommand'], $_POST['user'], $_POST['pass']))
	if(!$_POST['link'] = $func->gdrive($_POST['videoFile'], $_POST['gdriveCommand'], $_POST['user'], $_POST['pass']))
		if(!$_POST['link'] = $func->gdrive($_POST['videoFile'], $_POST['gdriveCommand'], $_POST['user'], $_POST['pass']))
				echo "Uploading to GDrive Failed... \n";
if($_POST['link'])
{
	echo "Posting GDrive link on IWO... \n";
	$func->curl('https://www.iwatchonline.cr/tools/postbot',$_POST);
}