<?php
$iwoUserid = '47348'; // User id on iwo
$maxTorrentSize = '500000'; 
$maxTorrentProcess = '5'; // run only 3 torrents download/upload at a time. seed does not count.
$botURL = 'http://95.211.168.168/bot/';

//watchonline.to upload account
$wotoEnabled = 0;
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

//openload.co upload account
$olcoEnabled = 1;
$olcoUser = "da0bfb6caff2fe2c";
$olcoPass = "bKd6tfZq";

//GDrive upload account
$gdriveEnabled = 1;
$gdriveCommand = "sudo /usr/local/bin/gdrive upload --share --no-progress ";
$imovietoUser = 'ka3kousa@gmail.com';
$imovietoPass = 'BEjofciaswi4';

//streamgo.com upload account
$mangoEnabled = 1;
$mangoUser = "SUo2SFi4nb";
$mangoPass = "3Qk1ghQq";

//streamgo.com upload account
$cherryEnabled = 1;
$cherryUser = "am7tBrz6UC";
$cherryPass = "RpA24-d2";

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

//watchers.to upload account
$watchersEnabled = 0;
$watchersUser = "spec10";
$watchersPass = "send00down";

$feeds = array(

	        array("type"=>"tv","quality"=>"HDTV","quality_id"=>"4","language"=>"English","feed"=>"https://rss.torrentleech.org/e9c7fd51758d24ac18a3"),
	        array("type"=>"movie","quality"=>"DVDRip","quality_id"=>"1","language"=>"English","feed"=>"https://rss.torrentleech.org/e9c7fd51758d24ac18a3"),
			array("type"=>"tv","quality"=>"HDTVS","quality_id"=>"4","language"=>"English","feed"=>"https://www.scenetime.com/get_rss.php?feed=direct&user=member1&cat=9,8,83,2&passkey=66efffa6d8ec624f663babd74de56054"),
			array("type"=>"movie","quality"=>"DVDRip","quality_id"=>"1","language"=>"English","feed"=>"https://www.scenetime.com/get_rss.php?feed=direct&user=member1&cat=59,57,103,1&passkey=66efffa6d8ec624f663babd74de56054"),
			array("type"=>"movie","quality"=>"DVDRip","quality_id"=>"1","language"=>"English","feed"=>"http://rss.workisboring.com/torrents/rss?u=909066;tp=1b80f3723b54d75b32bca8cbb974eee2;20;download"),
			array("type"=>"tv","quality"=>"HDTV","quality_id"=>"4","language"=>"English","feed"=>"http://rss.workisboring.com/torrents/rss?u=909066;tp=1b80f3723b54d75b32bca8cbb974eee2;22;5;download"),
			array("type"=>"tv","quality"=>"HD","quality_id"=>"2","language"=>"English","feed"=>"http://rss.workisboring.com/torrents/rss?u=909066;tp=1b80f3723b54d75b32bca8cbb974eee2;bookmarks;download")
			//
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