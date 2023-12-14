<?php
class Func {

public function MaxTorrents($m)
{
	exec("ps -ef |grep -v grep | grep -c engine.php", $c, $result);
	if( @$c[0] > $m)
		die("Running Max Process set in config");
}

public function multiRequest($data, $options = array()) {
 
  // array of curl handles
  $curly = array();
  // data to be returned
  $result = array();
 
  // multi handle
  $mh = curl_multi_init();
 
  // loop through $data and create curl handles
  // then add them to the multi-handle
  foreach ($data as $id => $d) {
 
    $curly[$id] = curl_init();
 
    $url = (is_array($d) && !empty($d['url'])) ? $d['url'] : $d;
    curl_setopt($curly[$id], CURLOPT_URL,            $url);
    curl_setopt($curly[$id], CURLOPT_HEADER,         0);
    curl_setopt($curly[$id], CURLOPT_RETURNTRANSFER, 1);
 
    // post?
    if (is_array($d)) {
      if (!empty($d['post'])) {
        curl_setopt($curly[$id], CURLOPT_POST,       1);
        curl_setopt($curly[$id], CURLOPT_POSTFIELDS, $d['post']);
      }
    }
 
    // extra options?
    if (!empty($options)) {
      curl_setopt_array($curly[$id], $options);
    }
 
    curl_multi_add_handle($mh, $curly[$id]);
  }
 
  // execute the handles
  $running = null;
  do {
    curl_multi_exec($mh, $running);
  } while($running > 0);
 
 
  // get content and remove handles
  foreach($curly as $id => $c) {
    $result[$id] = curl_multi_getcontent($c);
    curl_multi_remove_handle($mh, $c);
  }
 
  // all done
  curl_multi_close($mh);
 
  return $result;
}

public function GetCookies($content) 
{
	if (($hpos = strpos($content, "\r\n\r\n")) > 0) $content = substr($content, 0, $hpos);
	if (empty($content) || stripos($content, "\nSet-Cookie: ") === false) return '';
	preg_match_all('/\nSet-Cookie: (.*)(;|\r\n)/U', $content, $temp);
	$cookie = $temp[1];
	$cookie = implode('; ', $cookie);
	return $cookie;
}

public function cleanTitle($type,$title)
{
	$title = preg_replace('#\.#',' ',$title);
	if($type=="tv")
	{
		if(preg_match('#(?<title>.*)S(?<season>\d{1,2})E(?<episode>\d{1,2})#',$title,$match))
		{
			$title = preg_replace('#\d{4}#','',$match['title']);
			//$title = str_replace(' US','',$title);
			$title = str_replace('1080','',$title);
			//$title = str_replace(' UK','',$title);
			return array('title'=>trim($title),'year'=>'','full_title'=>trim($match['title']),'season'=>$match['season'],'episode'=>$match['episode']);
		}
		elseif(preg_match('#(?<title>[a-zA-z ]+)(?<season>\d{1,2})x(?<episode>\d{1,2})#',$title,$match))
		{
			$title = preg_replace('#\d{4}#','',$match['title']);
			//$title = str_replace(' US','',$title);
			$title = str_replace('1080','',$title);
			//$title = str_replace(' UK','',$title);
			$match['season'] = sprintf('%02d', $match['season']);
			$match['episode'] = sprintf('%02d', $match['episode']);
			return array('title'=>trim($title),'year'=>'','full_title'=>trim($match['title']),'season'=>$match['season'],'episode'=>$match['episode']);
		}
	}
	elseif($type=="movie")
	{
		if(preg_match('#(.*)(\d{4})#',$title,$m))
		{
			$title = preg_replace('#\d{4}#','',$m[1]);
			$title = preg_replace('#-#','',$title);
			$title = preg_replace('#\[#','',$title);
			return array('title'=>trim($title),'year'=>trim($m[2]),'full_title'=>'','season'=>'','episode'=>'');
		}
	}
	return false;
}		

public function log($logfile,$message)
{
	$ourFileName = $logfile;
	$ourFileHandle = fopen($ourFileName, 'a+') or die("can't open file");
	fwrite($ourFileHandle, $message."\n");
	fclose($ourFileHandle);		
}

public function getID($request)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, 'http://www.iwatchonline.cr/tools/get_id');
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
	curl_setopt( $ch, CURLOPT_ENCODING, "UTF-8" );
	$id = curl_exec($ch);
	curl_close($ch);
		return json_decode($id,true);
}
public function getManualID($request)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, 'http://www.iwatchonline.cr/tools/manual_get_episode_id');
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
	curl_setopt( $ch, CURLOPT_ENCODING, "UTF-8" );
	$id = curl_exec($ch);
	curl_close($ch);
		return json_decode($id,true);
}

public function getFeed($url)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt( $ch, CURLOPT_ENCODING, "UTF-8" );
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); 
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        //curl_setopt($ch, CURLOPT_VERBOSE, true);
	$page = curl_exec($ch);
	curl_close($ch);
	return $page;
}

public function cut_str($str, $left, $right="\r") 
{
	$str = substr ( stristr ( $str, $left ), strlen ( $left ) );
	$leftLen = strlen ( stristr ( $str, $right ) );
	$leftLen = $leftLen ? - ($leftLen) : strlen ( $str );
	$str = substr ( $str, 0, $leftLen );
	return $str;
}

public function active_torrents() 
{
exec("transmission-remote -l", $output, $result);
print_r($coutput);
$torrents = array();
foreach($output as $out) {
$out = trim($out);
$out=str_replace("Up & Down","Up&Down",$out);
$out=str_replace("min","",$out);
$out=str_replace("hrs","",$out);
$par = explode(" ",$out);
$new = array();
foreach($par as $part) {
if(!empty($part))
$new[] = $part ;
}
$torrents[] = $new ;
}

return $torrents;
}

public function formatBytes($b,$p = null) 
{
    $units = array("B","kB","MB","GB","TB","PB","EB","ZB","YB");
    $c=0;
    if(!$p && $p !== 0) {
        foreach($units as $k => $u) {
            if(($b / pow(1024,$k)) >= 1) {
                $r["bytes"] = $b / pow(1024,$k);
                $r["units"] = $u;
                $c++;
            }
        }
        return number_format($r["bytes"],2) . " " . $r["units"];
    } else {
        return number_format($b / pow(1024,$p)) . " " . $units[$p];
    }
}

public function movieCleanTitle($title)
{
	$title = str_ireplace("."," ",$title);
	if(preg_match('#(.*)\d{4}+#U',$title,$cleanTitle)){
		$remove = array('1080','REPACK');
		$title = $cleanTitle[0];
		foreach ($remove as $word) {
			$title = str_ireplace($word,"",$title);
		}
		return $title;
	}
	$title = explode(" ",$title);
	return $title[0];
}

public function downloadfile($link, $saveto,$user=false,$pass=false)
{
    $handle = fopen($saveto, 'w+');
    if ($handle)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $link);
        curl_setopt($ch, CURLOPT_FILE, $handle);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
 	    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1');
        if($user)
		{
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_USERPWD, $user . ":" . $pass);
        }
		curl_exec($ch);
        curl_close($ch);
        fclose($handle);
    }
    else
    {
        echo "<br />Can not download $link , probably links is dead";
        exit();
    }
}
public function ftpdownload($ftp_server,$ftpPort, $ftp_user_name, $ftp_user_pass,$downloadFrom,$downloadDir)
{
	if(!$conn_id = ftp_ssl_connect($ftp_server,$ftpPort))
	die("Could not connect to ftp server");
	if(!$login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass))
	die("Could not connect to ftp server with username/password");
	$res = ftp_size($conn_id, $downloadFrom);

	if ($res != -1) { 
		echo "\nDownloading....\n";
		ftp_get($conn_id, $downloadDir."/".basename($downloadFrom), $downloadFrom, FTP_BINARY);
	} else {
		echo "\nGetting Directory...\n";
		$releaseFiles = ftp_nlist($conn_id, $downloadFrom);
		//unset($releaseFiles[count($releaseFiles)-1]);
		//sort($releaseFiles);
		print_r($releaseFiles);	
		foreach($releaseFiles as $releaseFile) {
		$releaseFile = basename($releaseFile);
			if(strstr($releaseFile," files = ") || ($releaseFile == "Subs") || ($releaseFile == "Sample"))
				continue;
				//echo $downloadFrom.'/'.$releaseFile; die();
					echo "\n Downloading -> $releaseFile....\n";
			ftp_get($conn_id, $downloadDir."/".$releaseFile, $downloadFrom.'/'.$releaseFile, FTP_BINARY);
		}
	}
}
public function id()
{
				$ext = "0123456789";
				$hex = "0123456789abcdef";
				$let = str_split($hex,1);
				$contbase = strlen($hex);
				$comple = '';
				$rand = '';
						for($i=0; $i < 46; $i++){
						$rand .= $ext{mt_rand() % strlen($ext)};
						}
				$base = $rand;
				for ($i = 0; $i < 32; $i++) {
					$comple = $let[fmod($base,$contbase)].$comple;
					$base = bcdiv($base,$contbase,0);
				}
			return $comple;
	}

public function curl($link, $postfields = '', $cookie = '', $refer = '', $progress_track = 0, $header = 1, $follow = 1, $usragent = '')
{
    $ch = curl_init($link);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    if ($header)
        curl_setopt($ch, CURLOPT_HEADER, 1);
    else
        curl_setopt($ch, CURLOPT_HEADER, 0);
    if ($follow)
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    else
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
    if ($usragent)
        curl_setopt($ch, CURLOPT_USERAGENT, $usragent);
    else
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1');
    if ($refer)
        curl_setopt($ch, CURLOPT_REFERER, $refer);
    if ($postfields) {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
    }
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    if ($cookie) {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
    }
    $page = curl_exec($ch);
    curl_close($ch);
    if (empty($page)) {
        echo "\n Could not connect to host:  $link \n";
        //  die();
    }
    else {
        return $page;
    }
}

public function rndNum($lg)
{
    $str = "0123456789";
    for ($i = 1; $i <= $lg; $i++) {
        $st = rand(0, 9);
        $pnt = substr($str, $st, 1);
    }
    return $pnt;
}

public function allmyvideos($user, $pass, $file_loc)
{
	$post = array(
			"op" => "login",
			"redirect" => "http://allmyvideos.net/?op=upload",
			"login" => $user,
			"password" => $pass
			);
	$cookie = false;
	$strpage = $this->curl("http://allmyvideos.net/", 0, $cookie, "http://allmyvideos.net/login.html");
	$cookie.="; " . $this->GetCookies($strpage);
	$strpage = $this->curl("http://allmyvideos.net/", $post, $cookie, "http://allmyvideos.net/login.html");
	$cookie.="; " . $this->GetCookies($strpage);

	$sess_id = $this->cut_str($strpage, 'sess_id" value="','"');

	$upfrm = $this->cut_str($strpage,'form-data" action="','"');

	preg_match('/\'script\'.*:.*\'(.*)\'/',$strpage,$script);

	$postData = array(
		'Filename'=> basename($file_loc),
		'sess_id'=> $sess_id,
		'fileext'=> htmlentities('*.3gp;*.3g2;*.asx;*.asf;*.avi;*.m4v;*.mpegts;*.mp4;*.mkv;*.flv;*.mpg;*.mpeg;*.mov;*.ogv;*.ogg;*.rm;*.wmv;*.webm;*.torrent'),
		'folder'=> htmlentities('/'),
		'Filedata"; filename="'.basename($file_loc).'"' => "@".$file_loc,
		'Upload'=> 'Submit Query'
	);
	/*
	$filesize = filesize($file_loc);
	$headers = array(
	'Connection: keep-alive',
	'Content-Length: '.$filesize.'',
	'Accept: *',
	'Content-Type: multipart/form-data; boundary=----------gL6ae0ae0Ef1GI3ei4GI3KM7gL6Ij5',
	'Origin: http://allmyvideos.net',

	);
	*/
	if(!isset($script[1]))
	{
		"Some error on Allmyvideos \n";
		return false;
	}
	$strpage = $this->curl($script[1], $postData, $cookie, 'http://allmyvideos.net/?op=upload',0,0);
	$link = explode(':',$strpage);

	if(isset($link[1]))
	{
		return 'http://allmyvideos.net/'.$link[0];
	}
return false;    
}

public function watchonlineto($user, $pass, $cookie, $file_loc)
{
	$post = array (
		"op" 		=> "login",
		"redirect" 	=> "/?op=upload",
		"login" 	=> $user,
		"password" 	=> $pass
	);
	
	$strpage 		= $this->curl("http://www.watchonline.to/", http_build_query($post), $cookie, "http://www.watchonline.to/");
	$strpage 		= $this->curl("http://www.watchonline.to/?op=upload", 0, $cookie, "http://www.watchonline.to/");
	
	$sess_id 		= $this->cut_str($strpage, 'sess_id" value="','"');
	$upfrm 			= $this->cut_str($strpage, 'form-data" action="','"');
	$srv_id 		= $this->cut_str($strpage, 'srv_id" value="','"');
	$disk_id 		= $this->cut_str($strpage, 'disk_id" value="','"');
	$srv_tmp_url 	= $this->cut_str($strpage, '<input type="hidden" name="srv_tmp_url" value="','">');
	
	$post = array (
		"upload_type" 								=> 'file',
		"sess_id" 									=> $sess_id,
		"srv_tmp_url" 								=> $srv_tmp_url,
		"srv_id" 									=> $srv_id,
		"disk_id" 									=> $disk_id,
		'file"; filename="'.basename($file_loc).'"' => "@".$file_loc,
		"fakefilepc" 								=> basename($file_loc),
		"file_title" 								=> '',
		"file_descr" 								=> '',
		"snapshot" 									=> '',
		"tags" 										=> '',
		"file_category" 							=> '0',
		"file_public" 								=> '0',
		"file_public" 								=> '0',
		'tos' 										=> "1",
		'submit_btn' 								=> "Upload!"
	);
	
	$rand 		= $this->rndNum(12);
	$uurl		= $upfrm.$rand.'&utype=reg&disk_id='.$disk_id;
	$strpage 	= $this->curl($uurl, $post, $cookie, '', 'http://www.watchonline.to/');
	
	if($locat = $this->cut_str($strpage,"'fn'>","</textarea>"))
	{
		$gpost['fn'] = "$locat" ;
		$gpost['st'] = "OK" ;
		$gpost['op'] = "upload_result" ;
		$strpage = $this->curl("http://www.watchonline.to/", $gpost, $cookie, $uurl);
		
		if($ddl = $this->cut_str($strpage,'onFocus="copy(this);">','</textarea>'))
			return $ddl;
	}
	
	return false;
}

public function putlocker($cookie,$file_loc)
{
    $ch = curl_init('http://www.putlocker.com/upload_form.php');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1');
    curl_setopt($ch, CURLOPT_REFERER, 'http://www.putlocker.com/cp.php');
    curl_setopt($ch, CURLOPT_COOKIE, $cookie);
    $result = curl_exec($ch);
    curl_close($ch);
	$cookie.="; " . $this->GetCookies($result);

	preg_match('/\'script\' \: \'(.*)\'/',$result,$action);
	preg_match('/\'session\': \'(.*)\'/',$result,$session);
	preg_match('/\'auth_hash\':\'(.*)\',/',$result,$auth_hash);
	preg_match('/\'upload_form.php\?done\=(.*)\'/',$result,$up);
	if(!isset($session[1]))
	{
		"Some error on Putlocker \n";
		return false;
	}
	$postData = array(
		'Filename'=> basename($file_loc),
		'folder'=> htmlentities('/'),
		'fileext'=> htmlentities('*'),
		'session'=> $session[1],
		'auth_hash'=> $auth_hash[1],
		'do_convert'=> '1',
		'Upload'=> 'Submit Query',
		'Filedata"; filename="'.basename($file_loc).'"' => "@".$file_loc
	);


//echo $result ; die();
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL,$action[1]); 
	curl_setopt($ch, CURLOPT_POST,1); 
    curl_setopt($ch, CURLOPT_COOKIE, $cookie);
	curl_setopt($ch, CURLOPT_REFERER, 'http://www.putlocker.com/upload_form.php');
	curl_setopt($ch, CURLOPT_HEADER, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER,array("Expect:"));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $postData); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1');
  	// curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	$result=curl_exec ($ch); 
	$cookie.="; " . $this->GetCookies($result);
	
	$done = 'http://www.putlocker.com/upload_form.php?done='.$up[1];
	$ch = curl_init();
	//curl_setopt($ch, CURLOPT_INTERFACE, "95.211.146.70");
	curl_setopt($ch, CURLOPT_URL,$done); 
	curl_setopt($ch, CURLOPT_HEADER, 1);
	curl_setopt($ch, CURLOPT_REFERER, 'http://www.putlocker.com/');
    curl_setopt($ch, CURLOPT_COOKIE, $cookie);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1');
	$result=curl_exec ($ch); 
	curl_close ($ch);	
	$cookie.="; " . $this->GetCookies($result);
	
	preg_match('/"cp.php\?uploaded=(.*)" target/',$result,$up);
	$up = 'http://www.putlocker.com/cp.php?uploaded='.$up[1];
	
	$ch = curl_init();
	//curl_setopt($ch, CURLOPT_INTERFACE, "95.211.146.70");
	curl_setopt($ch, CURLOPT_URL,$up); 
	curl_setopt($ch, CURLOPT_HEADER, 1);
	curl_setopt($ch, CURLOPT_REFERER, $done);
    curl_setopt($ch, CURLOPT_COOKIE, $cookie);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1');
	$result=curl_exec ($ch); 

	curl_close ($ch);	
	if( preg_match('#"http://www.putlocker.com/file/(.*)"#',$result,$link))
	return "http://www.putlocker.com/file/". $link[1];
return false;	
}

public function sockshare($cookie,$file_loc)
{
    $ch = curl_init('http://www.sockshare.com/upload_form.php');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1');
    curl_setopt($ch, CURLOPT_REFERER, 'http://www.sockshare.com/cp.php');
    curl_setopt($ch, CURLOPT_COOKIE, $cookie);
    $result = curl_exec($ch);
    curl_close($ch);
	$cookie.="; " . $this->GetCookies($result);

	preg_match('/\'script\' \: \'(.*)\'/',$result,$action);
	preg_match('/\'session\': \'(.*)\'/',$result,$session);
	preg_match('/\'auth_hash\':\'(.*)\',/',$result,$auth_hash);
	preg_match('/\'upload_form.php\?done\=(.*)\'/',$result,$up);

	if(!isset($session[1]))
	{
		"Some error on Sockshar \n";
		return false;
	}
	$postData = array(
		'Filename'=> basename($file_loc),
		'folder'=> htmlentities('/'),
		'fileext'=> htmlentities('*'),
		'session'=> $session[1],
		'auth_hash'=> $auth_hash[1],
		'do_convert'=> '1',
		'Upload'=> 'Submit Query',
		'Filedata"; filename="'.basename($file_loc).'"' => "@".$file_loc
	);


	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL,$action[1]); 
	curl_setopt($ch, CURLOPT_POST,1); 
    curl_setopt($ch, CURLOPT_COOKIE, $cookie);
	curl_setopt($ch, CURLOPT_REFERER, 'http://www.sockshare.com/upload_form.php');
	curl_setopt($ch, CURLOPT_HEADER, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER,array("Expect:"));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $postData); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1');
  	// curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	$result=curl_exec ($ch); 
	$cookie.="; " . $this->GetCookies($result);
	
	$done = 'http://www.sockshare.com/upload_form.php?done='.$up[1];
	$ch = curl_init();
	//curl_setopt($ch, CURLOPT_INTERFACE, "95.211.146.70");
	curl_setopt($ch, CURLOPT_URL,$done); 
	curl_setopt($ch, CURLOPT_HEADER, 1);
	curl_setopt($ch, CURLOPT_REFERER, 'http://www.sockshare.com/');
    curl_setopt($ch, CURLOPT_COOKIE, $cookie);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1');
	$result=curl_exec ($ch); 
	curl_close ($ch);	
	$cookie.="; " . $this->GetCookies($result);
	
	preg_match('/"cp.php\?uploaded=(.*)" target/',$result,$up);
	$up = 'http://www.sockshare.com/cp.php?uploaded='.$up[1];
	
	$ch = curl_init();
	//curl_setopt($ch, CURLOPT_INTERFACE, "95.211.146.70");
	curl_setopt($ch, CURLOPT_URL,$up); 
	curl_setopt($ch, CURLOPT_HEADER, 1);
	curl_setopt($ch, CURLOPT_REFERER, $done);
    curl_setopt($ch, CURLOPT_COOKIE, $cookie);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1');
	$result=curl_exec ($ch); 

	curl_close ($ch);	
	if( preg_match('#"http://www.sockshare.com/file/(.*)"#',$result,$link))
	return "http://www.sockshare.com/file/". $link[1];
return false;	
}

public function videott($user,$pass,$cookie,$filelocation){    
  
 
  $ref = "http://www.video.tt";
  $post = array();
  $post['login_username'] = $user;
  $post['login_password'] = $pass;
  $url = "http://www.video.tt/login.php";
  
   $page = $this->curl($ref,0,$cookie,$ref);
   $page = $this->curl($url,$post,$cookie,$url);
   
	$rnd = $this->rndNum(12);

	$name = basename($filelocation);
    $ref = "http://www.video.tt/upload.php\r\nX-Requested-With: XMLHttpRequest";    
    $page = $this->curl("http://www.video.tt/ajax/upload_track.php?cat_sel=1&f=$name",0,$cookie,$ref);

	$sessionId = urlencode($this->cut_str ( $page ,'sessionId":"' ,'",'));
    $sid = $this->cut_str ( $page ,'sid":"' ,'",');
    $sip = $this->cut_str ( $page ,'sip":"' ,'",');
    $slip = $this->cut_str ( $page ,'slip":"' ,'",');
    $sd = $this->cut_str ( $page ,'sd":"' ,'",');
    $ip = $this->cut_str ( $page ,'ip":"' ,'",');
    $cc = $this->cut_str ( $page ,'cc":"' ,'",');
	$enc = $this->cut_str($page,'enc":"','"');
    $swfLink = $this->cut_str($page,'swfLink":"','"');
	$swfLink= str_replace("\\","",$swfLink);
    $progressURL = urldecode($this->cut_str ( $page ,'progressURL":"' ,'",'));
    $submitURL = urldecode($this->cut_str ( $page ,'submitURL":"' ,'",'));
    $vc = $this->cut_str ( $page ,'vc":"' ,'"');
    $URL = htmlspecialchars_decode($progressURL);
    $URL= str_replace("\\","",$URL);
    $upurl= htmlspecialchars_decode($submitURL);
	$upurl= str_replace("\\","",$upurl);	
	
	$page = $this->curl("http://www.video.tt/upload.php",0,$cookie,"http://www.video.tt/upload.php");
	$ud = $this->cut_str($page,'ud" id="ud" value="','"');
    $un = $this->cut_str($page,'un" id="un" value="','"');
    
	//$upurl = $upurl."?X-Progress-ID=$sessionId";

			$post=array();
            $post["uploadSessionId"] = $sessionId;
			$post["APC_UPLOAD_PROGRESS"] = $sessionId;
            $post["ud"]=$ud;
			$post["un"]=$un;
			$post["enc"]=$enc;
            $post["swfLink"]=$swfLink;
            $post["broadcast"]="0";
            $post["allow_comments"]= "0";
			$post["allow_rating"]="0";
            $post["search_by_user"]= "0";
			$post["title"]="";
            $post["description"]="";
			$post["file"]="@$filelocation";
            $post["tags"] = "";
			$post["category"] = "1";
            $post["cat_sel"]="1";
			$post["sip"]=$sip;
			$post["ps"]="1";
            $post["cc"]=$cc;
            $post["bsid"]= "7";
			$post["sid"]=$sid;
            $post["ip"]= $ip;
			$post["vc"]=$vc;
            $post["slip"]=$slip;
            $post["sd"]=$sd;	
	
	$upurl = 'http://'.$sd.'.video.tt/ajax/upload_process.php?X-Progress-ID='.$sessionId;
	$page = $this->curl($upurl,$post,0,"http://www.video.tt/upload.php");
	//die();
	$upurl = $URL."/progress?X-Progress-ID=$sessionId&callback=jsonp$rnd";
	$page = $this->curl($upurl,0,$cookie,"http://www.video.tt/upload.php");
// print_r($post);
	$state=$this->cut_str ( $page ,'state" : "' ,'"');
                        if($state == "done"){
							echo "File successfully uploaded";
							$video_link= "http://www.video.tt/video/$vc";
                        }else {
							$video_link = false;
                        }
   return $video_link;
}

public function thevideome($user, $pass, $cookie, $file_loc)
{
	$post = array (
		"op" 		=> "login",
		"redirect" 	=> "/?op=upload",
		"login" 	=> $user,
		"password" 	=> $pass
	);
	
	$strpage 		= $this->curl("http://thevideo.me/", http_build_query($post), $cookie, "http://thevideo.me/");
	$strpage 		= $this->curl("http://thevideo.me/?op=upload", 0, $cookie, "http://thevideo.me/");
	
	$sess_id 		= $this->cut_str($strpage, 'sess_id" value="','"');
	$upfrm 			= $this->cut_str($strpage, 'form-data" action="','"');
	$srv_id 		= $this->cut_str($strpage, 'srv_id" value="','"');
	$disk_id 		= $this->cut_str($strpage, 'disk_id" value="','"');
	$srv_tmp_url 	= $this->cut_str($strpage, '<input type="hidden" name="srv_tmp_url" value="','">');
	
	$post = array (
		"upload_type" 								=> 'file',
		"sess_id" 									=> $sess_id,
		"srv_tmp_url" 								=> $srv_tmp_url,
		"srv_id" 									=> $srv_id,
		"disk_id" 									=> $disk_id,
		'file"; filename="'.basename($file_loc).'"' => "@".$file_loc,
		"fakefilepc" 								=> basename($file_loc),
		"file_title" 								=> '',
		"file_descr" 								=> '',
		"tags" 										=> '',
		"file_category" 							=> '0',
		"file_public" 								=> '0',
		'tos' 										=> "1",
		'submit_btn' 								=> "Upload!"
	);
	
	$rand 		= $this->rndNum(12);
	$uurl		= $upfrm.$rand.'&utype=reg&disk_id='.$disk_id;
	$strpage 	= $this->curl($uurl, $post, $cookie, '', 'http://thevideo.me/');
	
	if($locat = $this->cut_str($strpage,"'fn'>","</textarea>"))
	{
		$gpost['fn'] = "$locat" ;
		$gpost['st'] = "OK" ;
		$gpost['op'] = "upload_result" ;
		$strpage = $this->curl("http://thevideo.me/", $gpost, $cookie, $uurl);
		
		if($ddl = $this->cut_str($strpage,'onFocus="copy(this);">','</textarea>'))
			return $ddl;
	}
	
	return false;
}

/*public function playedto($user, $pass, $cookie, $file_loc)
{
	$post= array(
			"op" => "login",
			"redirect" => "/?op=upload",
			"login" => $user,
			"password" => $pass
			);
	//$cookie = false;
	//$strpage = curl("http://played.to/", 0, $cookie, "http://played.to/");

	//if(!stristr($strpage,'Logout'))
		$strpage = $this->curl("http://played.to/", http_build_query($post), $cookie, "http://www.video.tt/");
	
	$strpage = $this->curl("http://played.to/?op=upload", 0, $cookie, "http://played.to/");
	$sess_id = $this->cut_str($strpage, 'sess_id" value="','"');

    $upfrm = $this->cut_str($strpage,'form-data" action="','"');
    $srv_id = $this->cut_str($strpage,'srv_id" value="','"');
    $disk_id = $this->cut_str($strpage,'disk_id" value="','"');
    $srv_tmp_url = $this->cut_str($strpage, '<input type="hidden" name="srv_tmp_url" value="','">');
    
		$post = array(
							"upload_type" => 'file',
							"sess_id" => $sess_id,
							"srv_tmp_url" => $srv_tmp_url,
							"srv_id" => $srv_id,
							"disk_id" => $disk_id,
							'file"; filename="'.basename($file_loc).'"' => "@".$file_loc,
							"fakefilepc" => basename($file_loc),
							"file_title" => '',
							"file_descr" => '',
							"tags" => '',
							"file_category" => '0',
							"file_public" => '0',
							'tos' => "1",
							'submit_btn' => "Upload!"
							);	
				
    $rand = $this->rndNum(12);
    $uurl= $upfrm.$rand.'&utype=reg&disk_id='.$disk_id;
    $strpage = $this->curl($uurl, $post, $cookie, '', 'http://played.to/');
	if($locat= $this->cut_str($strpage,"'fn'>","</textarea>"))
	{
		$gpost['fn'] = "$locat" ;
		$gpost['st'] = "OK" ;
		$gpost['op'] = "upload_result" ;
		$strpage = $this->curl("http://played.to/", $gpost, $cookie, $uurl);
		if($ddl= $this->cut_str($strpage,'onFocus="copy(this);">','</textarea>'))
			return $ddl;
	}	
return false;    
}*/

public function vodlocker($user, $pass, $file_loc)
{
	$post = array(
			"op" => "login",
			"redirect" => "http://vodlocker.com/?op=upload",
			"login" => $user,
			"password" => $pass
			);
	$cookie = false;
	$strpage = $this->curl("http://vodlocker.com/", 0, $cookie, "http://vodlocker.com/login.html");
	$cookie.="; " . $this->GetCookies($strpage);
	$strpage = $this->curl("http://vodlocker.com/", $post, $cookie, "http://vodlocker.com/login.html");
	$cookie.="; " . $this->GetCookies($strpage);

	$sess_id = $this->cut_str($strpage, 'sess_id" value="','"');

	$upfrm = $this->cut_str($strpage,'form-data" action="','"');

	preg_match('/\'script\'.*:.*\'(.*)\'/',$strpage,$script);

	$postData = array(
		'Filename'=> basename($file_loc),
		'sess_id'=> $sess_id,
		'fileext'=> htmlentities('*.3gp;*.3g2;*.asx;*.asf;*.avi;*.m4v;*.mpegts;*.mp4;*.mkv;*.flv;*.mpg;*.mpeg;*.mov;*.ogv;*.ogg;*.rm;*.wmv;*.webm;*.torrent'),
		'folder'=> htmlentities('/'),
		'Filedata"; filename="'.basename($file_loc).'"' => "@".$file_loc,
		'Upload'=> 'Submit Query'
	);
	/*
	$filesize = filesize($file_loc);
	$headers = array(
	'Connection: keep-alive',
	'Content-Length: '.$filesize.'',
	'Accept: *',
	'Content-Type: multipart/form-data; boundary=----------gL6ae0ae0Ef1GI3ei4GI3KM7gL6Ij5',
	'Origin: http://vodlocker.com',

	);
	*/
	if(!isset($script[1]))
	{
		"Some error on vodlocker.com \n";
		return false;
	}
	$strpage = $this->curl($script[1], $postData, $cookie, 'http://vodlocker.comt/?op=upload',0,0);
	$link = explode(':',$strpage);

	if(isset($link[1]))
	{
		return 'http://vodlocker.com/'.$link[0];
	}
return false;    
}

function filenuke($user, $pass, $cookie, $file_loc)
{
	$post= array(
			"op" => "login",
			"redirect" => "/?op=upload",
			"username" => $user,
			"password" => $pass
			);
	$strpage = $this->curl("http://filenuke.com/auth/login", http_build_query($post), $cookie, "http://filenuke.com/");
	$url = $this->cut_str($strpage,'name="file" data-url="','"');
	$host = trim($this->cut_str($url,'http://','/upload'));
	$file_info =  finfo_open(FILEINFO_MIME);  
	$mime_type = finfo_file($file_info, $file_loc); 
	//$file = file_get_contents($file_loc);
	$fileHandle = fopen($file_loc, "r");
        $fp = fsockopen($host, 80, $errno, $errstr, 30);
        if ($fp) {
            $out = "POST /upload/".basename($url)." HTTP/1.1\r\n";
            $out .= "Host: ".$host."\r\n";
            $out .= "Referer: http://filenuke.com/\r\n";
            $out .= "Origin: http://filenuke.com\r\n";
            $out .= "Accept-Encoding: gzip, deflate, sdch\r\n";
            $out .= "Accept-Language: en-US,en;q=0.8\r\n";
            $out .= "User-Agent: Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/39.0.2171.71 Safari/537.36\r\n";
            $out .= "Content-Type: ".$mime_type."\r\n";
            $out .= 'Content-Length: ' . filesize($file_loc) . "\r\n";
            $out .= "Content-Disposition: attachment; filename=\"".urlencode(basename($file_loc))."\"\r\n\r\n";
         
			
			fwrite($fp, $out);
			while(!feof($fileHandle)){
				fwrite($fp, fgets($fileHandle, 1024));
			}
			fclose($fileHandle);
            $response = '';
            while (!feof($fp)) {
                $response .= fgets($fp, 128);
            }
            fclose($fp);
			if($link = $this->cut_str($response,'url":"','"'))
				return str_replace("\\","",$link);
        } 
		return false;
}

public function movreel($user, $pass, $cookie, $file_loc)
{
	$post= array(
			"op" => "login",
			"redirect" => "http://movreel.com/",
			"login" => $user,
			"password" => $pass
			);
		$strpage = $this->curl("http://movreel.com/", http_build_query($post), $cookie, "http://www.video.tt/");

	$sess_id = $this->cut_str($strpage, 'sess_id" value="','"');

    $upfrm = $this->cut_str($strpage,'form-data" action="','"');
    $srv_tmp_url = $this->cut_str($strpage, '<input type="hidden" name="srv_tmp_url" value="','">');
    
	$post = array(
				"upload_type" => 'file',
				"sess_id" => $sess_id,
				"srv_tmp_url" => $srv_tmp_url,
				'file_0"; filename="'.basename($file_loc).'"' => "@".$file_loc,
				'file_1"; filename=""' => "",
				"link_rcpt" => '',
				"link_pass" => '',
				'tos' => "1",
				'submit_btn' => "Upload"
				);	
		
    $rand = $this->rndNum(12);
    $uurl=$upfrm.$rand.'&js_on=1&utype=prem&upload_type=file';
    $strpage = $this->curl($uurl, $post, $cookie, '', 'http://movreel.com/');
	if($locat=$this->cut_str($strpage,"'fn'>","</textarea>"))
	{
		$gpost['fn'] = "$locat" ;
		$gpost['st'] = "OK" ;
		$gpost['op'] = "upload_result" ;
		$strpage = $this->curl("http://movreel.com/", $gpost, $cookie, $uurl);
		if($ddl=$this->cut_str($strpage,'onfocus="copy(this);">','</textarea>'))
			return $ddl;
	}	
return false;    
}

public function vidbux($user, $pass, $cookie, $file_loc)
{
	$post= array(
			"op" => "login",
			"redirect" => "http://www.vidbux.to/?op=upload",
			"login" => $user,
			"password" => $pass
			);
		$strpage = $this->curl("http://www.vidbux.to/", http_build_query($post), $cookie, "http://www.video.tt/");

	$sess_id = $this->cut_str($strpage, 'sess_id" value="','"');
    $upfrm = $this->cut_str($strpage,'form-data" action="','"');
    $srv_id = $this->cut_str($strpage,'srv_id" value="','"');
    $upsrv = $this->cut_str($strpage,'upsrv" value="','"');
    $srv_tmp_url = $this->cut_str($strpage, '<input type="hidden" name="srv_tmp_url" value="','">');
    
	$post = array(
				"upload_type" => 'file',
				"sess_id" => $sess_id,
				"srv_id" => $srv_id,
				"upsrv" => $upsrv,
				"srv_tmp_url" => $srv_tmp_url,
				'file_0"; filename="'.basename($file_loc).'"' => "@".$file_loc,
				'file_1"; filename=""' => "",
				"file_0_title" => '',
				"file_0_descr" => '',
				"link_rcpt" => '',
				"link_pass" => '',
				'tos' => "1",
				'submit_btn' => "Upload!"
				);	
		
    $rand = $this->rndNum(12);
    $uurl=$upfrm.$rand.'&js_on=1&utype=reg&upload_type=file';
    $strpage = $this->curl($uurl, $post, $cookie, '', 'http://www.vidbux.to/');
	
	if($locat=$this->cut_str($strpage,"'fn'>","</textarea>"))
	{
		$gpost['fn'] = "$locat" ;
		$gpost['st'] = "OK" ;
		$gpost['op'] = "upload_result" ;
		$strpage = $this->curl("http://www.vidbux.to/", $gpost, $cookie, $uurl);
		if($ddl=$this->cut_str($strpage,'onfocus="copy(this);" value="','"'))
			return $ddl;
	}	
return false;    
}

public function vidxden($user, $pass, $cookie, $file_loc)
{
	$post= array(
			"op" => "login",
			"redirect" => "http://www.vidxden.to/?op=upload",
			"login" => $user,
			"password" => $pass
			);
		$strpage = $this->curl("http://www.vidxden.to/", http_build_query($post), $cookie, "http://www.video.tt/");

	$sess_id = $this->cut_str($strpage, 'sess_id" value="','"');
    $upfrm = $this->cut_str($strpage,'form-data" action="','"');
    $srv_id = $this->cut_str($strpage,'srv_id" value="','"');
    $upsrv = $this->cut_str($strpage,'upsrv" value="','"');
    $srv_tmp_url = $this->cut_str($strpage, '<input type="hidden" name="srv_tmp_url" value="','">');
    
	$post = array(
				"upload_type" => 'file',
				"sess_id" => $sess_id,
				"srv_id" => $srv_id,
				"upsrv" => $upsrv,
				"srv_tmp_url" => $srv_tmp_url,
				'file_0"; filename="'.basename($file_loc).'"' => "@".$file_loc,
				'file_1"; filename=""' => "",
				"file_0_title" => '',
				"file_0_descr" => '',
				"link_rcpt" => '',
				"link_pass" => '',
				'tos' => "1",
				'submit_btn' => "Upload!"
				);	
		
    $rand = $this->rndNum(12);
    $uurl=$upfrm.$rand.'&js_on=1&utype=reg&upload_type=file';
    $strpage = $this->curl($uurl, $post, $cookie, '', 'http://www.vidxden.to/');
	
	if($locat=$this->cut_str($strpage,"'fn'>","</textarea>"))
	{
		$gpost['fn'] = "$locat" ;
		$gpost['st'] = "OK" ;
		$gpost['op'] = "upload_result" ;
		$strpage = $this->curl("http://www.vidxden.to/", $gpost, $cookie, $uurl);
		if($ddl=$this->cut_str($strpage,'onfocus="copy(this);">','</textarea>'))
			return $ddl;
	}	
return false;    
}

public function vidspot($user, $pass, $file_loc)
{
	$post = array(
			"op" => "login",
			"redirect" => "http://vidspot.net/?op=upload",
			"login" => $user,
			"password" => $pass
			);
	$cookie = false;
	$strpage = $this->curl("http://vidspot.net/", 0, $cookie, "http://vidspot.net/login.html");
	$cookie.="; " . $this->GetCookies($strpage);
	$strpage = $this->curl("http://vidspot.net/", $post, $cookie, "http://vidspot.net/login.html");
	$cookie.="; " . $this->GetCookies($strpage);

	$sess_id = $this->cut_str($strpage, 'sess_id" value="','"');

	$upfrm = $this->cut_str($strpage,'form-data" action="','"');

	preg_match('/\'script\'.*:.*\'(.*)\'/',$strpage,$script);

	$postData = array(
		'Filename'=> basename($file_loc),
		'sess_id'=> $sess_id,
		'fileext'=> htmlentities('*.3gp;*.3g2;*.asx;*.asf;*.avi;*.m4v;*.mpegts;*.mp4;*.mkv;*.flv;*.mpg;*.mpeg;*.mov;*.ogv;*.ogg;*.rm;*.wmv;*.webm;*.torrent'),
		'folder'=> htmlentities('/'),
		'Filedata"; filename="'.basename($file_loc).'"' => "@".$file_loc,
		'Upload'=> 'Submit Query'
	);
	
	if(!isset($script[1]))
	{
		"Some error on vidspot \n";
		return false;
	}
	$strpage = $this->curl($script[1], $postData, $cookie, 'http://vidspot.net/?op=upload',0,0);
	$link = explode(':',$strpage);

	if(isset($link[1]))
	{
		return 'http://vidspot.net/'.$link[0];
	}
return false;    
}

public function streamcloud($user, $pass, $file_loc)
{
	$post = array(
			"op" => "login",
			"redirect" => "http://streamcloud.eu/?op=upload",
			"login" => $user,
			"password" => $pass
			);
	$cookie = false;
	$strpage = $this->curl("http://streamcloud.eu/", 0, $cookie, "http://streamcloud.eu/login.html");
	$cookie.="; " . $this->GetCookies($strpage);
	$strpage = $this->curl("http://streamcloud.eu/", $post, $cookie, "http://streamcloud.eu/login.html");
	$cookie.="; " . $this->GetCookies($strpage);

	$sess_id = $this->cut_str($strpage, 'sess_id" value="','"');

	$upfrm = $this->cut_str($strpage,'form-data" action="','"');

	preg_match('/\'script\'.*:.*\'(.*)\'/',$strpage,$script);

	$postData = array(
		'Filename'=> basename($file_loc),
		'sess_id'=> $sess_id,
		'fileext'=> htmlentities('*.3gp;*.3g2;*.asx;*.asf;*.avi;*.m4v;*.mpegts;*.mp4;*.mkv;*.flv;*.mpg;*.mpeg;*.mov;*.ogv;*.ogg;*.rm;*.wmv;*.webm;*.torrent'),
		'folder'=> htmlentities('/'),
		'Filedata"; filename="'.basename($file_loc).'"' => "@".$file_loc,
		'Upload'=> 'Submit Query'
	);
	
	if(!isset($script[1]))
	{
		"Some error on streamcloud \n";
		return false;
	}
	$strpage = $this->curl($script[1], $postData, $cookie, 'http://streamcloud.eu/?op=upload',0,0);
	$link = explode(':',$strpage);

	if(isset($link[1]))
	{
		return 'http://streamcloud.eu/'.$link[0];
	}
return false;    
}

public function firedrive($username,$pass,$file_loc)
{
$post = array('user'=>$username,'pass'=>$pass,'remember'=>1,'json'=>1);
$page = $this->firedrivecurl('https://auth.firedrive.com/',$post);
$cookie = $this->GetCookies($page);
$id = time();
$page = $this->firedrivecurl('http://www.firedrive.com/upload?_='.$id,0,$cookie);
preg_match('#getUploadVars.*\'(.*)\'#isU',$page,$m);

$vars = $this->cut_str("return '","'",$page);	
$post = array(
		"name" => basename($file_loc) ,
		"vars" => $m[1],
		"target_folder" => 0,
		'file"; filename="'.basename($file_loc).'"' => "@".$file_loc
		);
//print_r($post);
$page = $this->firedrivecurl('https://upload.firedrive.com/web',$post,$cookie,'https://auth.firedrive.com/',0);
$return = json_decode($page);
if($return->id)
	return 'http://www.firedrive.com/file/'.$return->id;
return;
}

public function firedrivecurl($link, $postfields = '', $cookie = '', $refer = '',$heders = 1)
{
	$ch = curl_init($link);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_HEADER, $heders);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER,array("Expect:"));
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1');
	if ($refer) {
		curl_setopt($ch, CURLOPT_REFERER, $refer);
	}
	if ($postfields) {
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
	}
	if ($cookie) {
		curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
		curl_setopt($ch, CURLOPT_COOKIE, $cookie);
	}
	$page = curl_exec($ch);
	return ($page);
	curl_close($ch);
}	


}
?>
