<?php
$iwoUserid = '30'; // User id on iwo
$maxTorrentSize = '500000'; 
$maxTorrentProcess = '5'; // run only 3 torrents download/upload at a time. seed does not count.
$botURL = 'http://95.211.168.168/bot/';

//watchonline.to upload account
$wotoEnabled = 1;
$wotoUser = "7130854";
$wotoPass = "7130854";

//Allmyvideos.net upload account
$amvEnabled = 0;
$amvUser = "member";
$amvPass = "straptwitteralive1985";

//vidspot.net upload account
$vpotEnabled = 0;
$vpotUser = "iwomember";
$vpotPass = "send00down";

//Video.tt upload account
$vttEnabled = 0;
$vttUser = "iwodavid";
$vttPass = "9f9801a7e7d46";

//thevideo.me upload account
$vmeEnabled = 0;
$vmeUser = "member1";
$vmePass = "send00down";

//vodlocker.com upload account
$vodEnabled = 0;
$vodUser = "spec10";
$vodPass = "123321";




###################################################################

//firedrive.com upload account
$fireDEnabled = 0;
$firedrive_user = "iwosd";
$firedrive_pass = "ledSuncmasterbx2031";

//Sockshare.com upload account
$sshareEnabled = 0;
$sockshareCookie = "auth=OTU1MDgzKzdhNzFkZjkxOGM1NWU1MmU2N2E3MjMwOGUyY2QyNmYyNWQzNGI1MTg%3D;";

//movreel.com upload account
$mvrEnabled = 0;
$mvrUser = "senddown";
$mvrPass = "9f9801a7e7d46";

//vidbux.com upload account
$vdbEnabled = 0;
$vdbUser = "senddown";
$vdbPass = "Suncmasterbx2031";

//filenuke.com upload account
$fnukEnabled = 0;
$fnukUser = "senddown";
$fnukPass = "9f9801a7e7d46";

//vidxden.com upload account
$vdxEnabled = 0;
$vdxUser = "member";
$vdxPass = "Suncmasterbx2031";

//streamcloud.eu upload account
$sloEnabled = 0;
$sloUser = "senddown";
$sloPass = "send00down";

$feeds = array(
	
	        array("type"=>"tv","quality"=>"HDTV","quality_id"=>"4","language"=>"English","feed"=>"https://sceneaccess.eu/rss?feed=dl&cat=45,17,11&passkey=a03bcc7231ee3e2b0bc5f7e7f990be99"),
		    array("type"=>"tv","quality"=>"HD","quality_id"=>"2","language"=>"English","feed"=>"https://sceneaccess.eu/rss?feed=dl&cat=44,27&passkey=a03bcc7231ee3e2b0bc5f7e7f990be99"),
			array("type"=>"tv","quality"=>"HDTVS","quality_id"=>"4","language"=>"English","feed"=>"https://www.scenetime.com/get_rss.php?feed=direct&user=member1&cat=77,83,2&passkey=66efffa6d8ec624f663babd74de56054"),
			array("type"=>"movie","quality"=>"DVDRip","quality_id"=>"1","language"=>"English","feed"=>"https://www.scenetime.com/get_rss.php?feed=direct&user=member1&cat=57,103,1&passkey=66efffa6d8ec624f663babd74de56054"),
			//array("type"=>"tv","quality"=>"HDTV","quality_id"=>"4","language"=>"English","feed"=>"http://rss.torrentleech.org/0a423639796c407241fd")
			);
			
$deleteTorrent = 1; // only enable if you dont want to seed back and delete after upload complete.

// Manual torrent setting

$postData = array();
$postData['active'] = 1;
$postData['userid'] = $iwoUserid;
$postData['language'] = "English";
$postData['link_type'] = "4"; // quality_id
$postData['quality'] = "HDTV"; // quality_id
	
		
?>