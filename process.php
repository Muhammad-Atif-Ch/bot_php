		<?php
		set_time_limit(0);
		ignore_user_abort(true);
		error_reporting(E_ALL);
		ini_set("display_errors",1);

		$path = dirname( __FILE__ ) . '/';
		require_once $path . 'inc/torrent.php';
		require_once $path . 'inc/func.php';
		require_once $path . '/inc/TransmissionRPC.class.php';
		require_once $path . 'config.php';
		require_once $path . 'manual_config.php';

		$postData = array();
		// DO NOT CHANGE BELOW:			
		$titlesLog = $path. 'logs/titles.txt';	
		$noTitleLog = $path. 'logs/noTitleLog.txt';	
		$noEpisodeManualLog = $path. 'logs/noEpisodeManualLog.txt';	
		$processedLog = $path. 'logs/processedLink.txt';	
		$noIDonIWOLog = $path. 'logs/noIDonIWO.txt';	
		$downloadDir = $path."downloads/";
		$uploadDir = $path."upload/";
		$tempDir = $path."temp/";
		$cookie = $path."temp/cookie.txt";
		
		/*
		
		$func = new Func();
		$torrent['remote'] = 'http://rarbg.com/download.php?id=2b8hvjm&f=Authors%20Anonymous%202014%20HDRIP%20x264%20AC3%20TiTAN-[rarbg.com].torrent';
		$torrent_file = $path."/torrents/".time().".torrent";
		$func->downloadfile($torrent['remote'],$torrent_file);
			
		//$torrent_file = $path."torrents/download.php?id=sjemun3&f=Sparks%202013%20HDRip%20XviD%20AC3-RARBG-[rarbg.com].torrent";
		$torrentinfo = new Torrent($torrent_file);
		//print_r(trim($torrentinfo->info['name']));
		$dirName = trim($torrentinfo->info['name']);
				$filelist = array();
				$handle = opendir(urldecode($downloadDir.$dirName));
				while (false !== ($files = readdir($handle)))
				{
					if ($files !== '.' && $files !== '..')
					{
						array_push($filelist, $files);
					}
				}
				closedir($handle);
				sort($filelist);	
				print_r($filelist);	
		die();

		*/


		$processed = file($processedLog, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		$titles = file($titlesLog, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

		$func = new Func();

		//print_r($_POST);
		//print_r($_FILES);

		
		if($manualTesting)
		{
			$torrent = array();
			$torrent['f_name'] = $torrent_filename;
			$torrent_file = $path."/torrents/". $torrent_filename;
			$torrent['type'] = $torrent_category;
			
			$postData['content_id'] = $iwo_content_id;
			$postData['id'] = $iwo_content_id;
			$postData['season'] = $iwo_season_number;
			$postData['episode'] = $iwo_episode_number;
		
			$postData['active'] = $iwo_active ;
			$postData['userid'] = $iwo_userid;
			$postData['language'] = $iwo_language;
			$postData['link_type'] = $iwo_link_type;
			$postData['quality'] = $iwo_quality;
			$postData['upload_type'] = $upload_type;
		}
		else
		{
			$torrent = array();
			$torrent['tmp_name'] = $_FILES['torrent_file']['tmp_name'];
			$torrent['f_name'] = $_FILES['torrent_file']['name'];
			$torrent['remote'] = $_POST['remote'];
			$torrent['type'] = $_POST['torrent_type'];
			$torrent['upload_type'] = $_POST['upload_type'];
			$upload_type = $_POST['upload_type'];


			$postData['content_id'] = $_POST['iwo_id'];
			$postData['id'] = $_POST['iwo_id'];
			$postData['season'] = $_POST['season'];
			$postData['episode'] = $_POST['episode'];
		
			$postData['active'] = $_POST['active'];
			$postData['userid'] = $_POST['userid'];
			$postData['language'] = $_POST['language'];
			$postData['link_type'] = $_POST['link_type'];
			$postData['quality'] = $_POST['quality'];
			$postData['subtitle'] = $_POST['subtitle'];
			//$postData['e_id'] = $_POST['e_id'];
		}

			
						echo "Getting ID from IWO for\n";
						$iwoid = $func->getManualID($postData);
						if($torrent['type'] == "tv")
						{
							if(!$iwoid['e_id'])
							{
								sleep(4);
								$iwoid = $func->getManualID($postData);
							}
							if(!$iwoid['e_id'])
							{
								sleep(4);
								$iwoid = $func->getManualID($postData);
							}
							if(!$iwoid['e_id'])
							{
								$msg = "Cant get episode id from iwo: Skipping";
								$msg .= print_r($postData, true);
								$func->log($noEpisodeManualLog,$msg);
								print_r($msg); die();
							}	
							$postData['e_id'] = $iwoid['e_id'];
						}
// START TORRENT ======== //	
if($upload_type == 'torrent')
{				
		if(@$torrent['tmp_name'] && !$manualTesting)
		{
			$torrent_file = $path."/torrents/". $torrent['f_name'];
			if(!move_uploaded_file($torrent['tmp_name'], $torrent_file))
				echo 'not uploaded';
			else
				echo 'uploaded';			
		}
		if(@$torrent['remote'] && !$manualTesting)
		{
			echo "Downloading torrent file  \n";	
			$torrent_file = $path."/torrents/".time().".torrent";
			$func->downloadfile($torrent['remote'],$torrent_file);
		}
			$torrentinfo = new Torrent($torrent_file);
			$dirName = trim($torrentinfo->info['name']);
			sleep(2);	
			
			try
			{
			  $rpc = new TransmissionRPC();
			  $result = $rpc->sstats( );
			  echo "GET SESSION STATS... [{$result->result}]\n";
			  $result = $rpc->add_file( $torrent_file, $downloadDir );

			  sleep( 4 );
			  if(!$id = $result->arguments->torrent_added->id)
				die("Cound not add file");
			
			  $noDir = $result->arguments->torrent_added->name;
			  if(is_file($path."downloads/".$noDir))
				$is_file = true;
			  else
				$is_file = false;
				
			  echo "Adding Torrent ... [{$result->result}] (id=$id)\n";
			  $result = $rpc->start( $id );
			  echo "starting Torrent ... [{$result->result}]\n";
			  
			} catch (Exception $e) {
			  die('[ERROR] ' . $e->getMessage() . PHP_EOL);
			}
					$percentrun = true;
					while($percentrun)
					{
					  $i = 0;
					  $rpc->return_as_array = true;
					  $result = $rpc->get( $id, array( 'percentDone' ) );

					  $rpc->return_as_array = false;
					  
						$percentDone = $result['arguments']['torrents']['0']['percentDone'];
						if($percentDone == 1) {
							$i++;
						}
						if($i == 1) {
							$percentrun = false;
							break 1;
						}
						echo "percentDone:".$percentDone."\n";
						sleep(2);
					}	  
					echo "percentDone:".$percentDone."\n";
					echo "Download Completed... \n";
						sleep(3);
} 
// END TORRENT ======== //

// END HTTP ======== //
if($upload_type == 'http')
{
		$dirName = time();
		mkdir($downloadDir.$dirName, 0777, true);
		sleep(2);
		$httpdata = parse_url($http_link);
		$user = ($httpdata['user'])?$httpdata['user']:false;
		$pass = ($httpdata['pass'])?$httpdata['pass']:false;
		$func->downloadfile($http_link,$downloadDir.$dirName."/".basename($http_link),$user,$pass);
}

// END HTTP ======== //

// END FTP ======== //
if($upload_type == 'ftp') 
{
		$fflink = ($manualTesting)?$ftp_link:$_POST['remote'];
		$ftpdata = parse_url($fflink);
		$dirName = time();
		mkdir($downloadDir.$dirName, 0777, true);
		sleep(2);
		$func->ftpdownload($ftpdata['host'],$ftpdata['port'], $ftpdata['user'], $ftpdata['pass'],urldecode($ftpdata['path']),$downloadDir.$dirName);
}

// END FTP ======== //
						
				if($is_file)
				{
					echo "Copying....\n";
					mkdir($downloadDir.$dirName, 0777, true);
					copy($path."downloads/".$noDir, $downloadDir.$dirName.'/'.$noDir);
				}
				$from = urldecode($downloadDir.$dirName);
				// get files
				$filelist = array();
					$handle = opendir(urldecode($downloadDir.$dirName));
					while (false !== ($files = readdir($handle)))
					{
						if ($files !== '.' && $files !== '..')
						{
							array_push($filelist, $files);
						}
					}
					closedir($handle);
					sort($filelist);	
					print_r($filelist);	

					echo "Searching Video Files.. \n";
					$rarfile = false;
					$videoFiles = array();
					foreach ($filelist as $file)
					{
						$ext = substr(strrchr($file, '.'), 1);	
						if (($ext == "mkv") || ($ext == "avi") || ($ext == "mpg") || ($ext == "mp4") || ($ext == "flv") || ($ext == "wmv") || ($ext == "xvid")) {
								$videoFiles[] = $downloadDir.$dirName.'/'.$file;
							}
						if ($ext == "rar" && empty($videoFiles)) {
								$rarfile = $downloadDir.$dirName.'/'.$file;
						}					
					}	
					if($rarfile)
					{	
					echo "Found files to unpack...Unpacking.. \n";
						$tempDir = $tempDir.$dirName;
						if (!mkdir($tempDir, 0777, true))
							die('Failed to create folder in temp...');
										
						//$exec = exec( "'".$path."rar/rar' e -y '$rarfile' '$tempDir'", $command_output, $result);
						$exec = exec( "rar e -y '$rarfile' '$tempDir'", $command_output, $result);
						$filelist = array();
						$handle = opendir($tempDir);
						while (false !== ($files = readdir($handle)))
						{
							if ($files !== '.' && $files !== '..')
							{
								$filelist[] = $files;
							}
						}
						closedir($handle);
						sort($filelist);	
						foreach ($filelist as $file)
						{
							$filename = basename($file);
							$ext = substr(strrchr($filename, '.'), 1);
								 if (($ext == "mkv") || ($ext == "avi") || ($ext == "mpg") || ($ext == "mp4") || ($ext == "flv") || ($ext == "wmv") || ($ext == "xvid")) {
									$videoFiles[] = $tempDir.'/'.$file;
								 }
						}						
					}	// rar Found IF ends
					
					if(!empty($videoFiles))
					{
						echo "Found video .... \n";
						if(count($videoFiles) == 1)
						{
							$videoFile = $videoFiles[0];
						}
						else
						{
							$firstVideoSize = filesize($videoFiles[0]);
							$videoFile = $videoFiles[0];
							foreach($videoFiles as $k => $v)
							{
								if($k == 0)
									continue;
								$newSize = filesize($v);
								if( $newSize > $firstVideoSize) 
								{
									$firstVideoSize = $newSize;
									$videoFile = $v;
								}
							}
						}
						/*echo $videoFile;
						die("Ok...enough for today");
						*/
						$fname = basename($videoFile);
						$ext = pathinfo($fname, PATHINFO_EXTENSION);
						
						$fname = $postData['id'].'-'.$postData['quality'];
						if($postData['season'])
							$fname .= '-S'.$postData['season'].'E'.$postData['episode'];
						$fname .= '-iwatchonline.cr.'.$ext;
						
						$releaseFolder = $uploadDir.$dirName;
							mkdir($releaseFolder, 0777, true);
						echo "Renaming and copying file \n";
						copy($videoFile, $releaseFolder.'/'.$fname);
						
					$videoFile = $releaseFolder.'/'.$fname;		
					
					$processHosts = array();
					if($fireDEnabled)
					{
						echo "Uploading to Firedrive... \n";
						//exec("php ".$path."/hosts/firedrive.php ".$videoFile." ". $putlockerCookie." ". $postData. " >> /dev/null 2>&1");
					
						$postData['videoFile'] = $videoFile;	
						$postData['firedrive_user'] = $firedrive_user;	
						$postData['firedrive_pass'] = $firedrive_pass;	
						//$func->curl($botURL.'hosts/firedrive.php', $postData);
						$curlData = array();
						$curlData['url'] = $botURL.'hosts/firedrive.php';	
						$curlData['post'] = $postData;	
						$processHosts[] = $curlData;
					}
					if($sshareEnabled)
					{
						echo "Uploading to Sockshare... \n";
						//exec("php ".$path."/hosts/sockshare.php ".$videoFile." ". $sockshareCookie." ". $postData. " >> /dev/null 2>&1");
						
						$postData['videoFile'] = $videoFile;	
						$postData['sockshareCookie'] = $sockshareCookie;	
						//$func->curl($botURL.'hosts/sockshare.php', $postData);
						$curlData = array();
						$curlData['url'] = $botURL.'hosts/sockshare.php';	
						$curlData['post'] = $postData;	
						$processHosts[] = $curlData;
					}
					if ($wotoEnabled)
					{
						echo "Uploading to watchonline.to...\n";
						
						$postData['videoFile'] 	= $videoFile;
						$postData['wotoUser'] 	= $wotoUser;
						$postData['wotoPass'] 	= $wotoPass;
						$postData['cookie'] 	= $cookie;
						
						$curlData 				= array();
						$curlData['url'] 		= $botURL.'hosts/watchonlineto.php';
						$curlData['post'] 		= $postData;
						$processHosts[] 		= $curlData;
					}
					if($amvEnabled)
					{
						echo "Uploading to Allmyvideos... \n";
						//exec("php ".$path."/hosts/allmyvideos.php ".$videoFile." ". $amvUser." ". $amvPass." ". $postData. " >> /dev/null 2>&1");
					
						$postData['videoFile'] = $videoFile;	
						$postData['amvUser'] = $amvUser;	
						$postData['amvPass'] = $amvPass;	
						//$func->curl($botURL.'hosts/allmyvideos.php', $postData);
						$curlData = array();
						$curlData['url'] = $botURL.'hosts/allmyvideos.php';	
						$curlData['post'] = $postData;	
						$processHosts[] = $curlData;
					}
					if($sloEnabled)
					{
						echo "Uploading to Streamcloud... \n";
						//exec("php ".$path."/hosts/streamcloud.php ".$videoFile." ". $amvUser." ". $amvPass." ". $postData. " >> /dev/null 2>&1");
					
						$postData['videoFile'] = $videoFile;	
						$postData['sloUser'] = $amvUser;	
						$postData['sloPass'] = $amvPass;	
						//$func->curl($botURL.'hosts/streamcloud.php', $postData);
						$curlData = array();
						$curlData['url'] = $botURL.'hosts/streamcloud.php';	
						$curlData['post'] = $postData;	
						$processHosts[] = $curlData;
					}
					if($vttEnabled)
					{
						echo "Uploading to Video.tt... \n";
						//exec("php ".$path."/hosts/videott.php ".$videoFile." ". $vttUser." ". $vttPass." ". $cookie." ". $postData. " >> /dev/null 2>&1");
					
						$postData['videoFile'] = $videoFile;	
						$postData['vttUser'] = $vttUser;	
						$postData['vttPass'] = $vttPass;	
						$postData['cookie'] = $cookie;	
						//$func->curl($botURL.'hosts/videott.php', $postData);
						$curlData = array();
						$curlData['url'] = $botURL.'hosts/videott.php';	
						$curlData['post'] = $postData;	
						$processHosts[] = $curlData;
					}
					if($mvrEnabled)
					{
						echo "Uploading to Movreel... \n";
						//exec("php ".$path."/hosts/movreel.php ".$videoFile." ". $mvrUser." ". $mvrPass." ". $cookie." ". $postData. " >> /dev/null 2>&1");
					
						$postData['videoFile'] = $videoFile;	
						$postData['mvrUser'] = $mvrUser;	
						$postData['mvrPass'] = $mvrPass;	
						$postData['cookie'] = $cookie;	
						//$func->curl($botURL.'hosts/movreel.php', $postData);
						$curlData = array();
						$curlData['url'] = $botURL.'hosts/movreel.php';	
						$curlData['post'] = $postData;	
						$processHosts[] = $curlData;
					}
					if($vdbEnabled)
					{
						echo "Uploading to Vidbux... \n";
						//exec("php ".$path."/hosts/vidbux.php ".$videoFile." ". $vdbUser." ". $vdbPass." ". $cookie." ". $postData. " >> /dev/null 2>&1");
					
						$postData['videoFile'] = $videoFile;	
						$postData['vdbUser'] = $vdbUser;	
						$postData['vdbPass'] = $vdbPass;	
						$postData['cookie'] = $cookie;	
						//$func->curl($botURL.'hosts/vidbux.php', $postData);
						$curlData = array();
						$curlData['url'] = $botURL.'hosts/vidbux.php';	
						$curlData['post'] = $postData;	
						$processHosts[] = $curlData;
					}
					if($vdxEnabled)
					{
						echo "Uploading to Vidxden... \n";
						//exec("php ".$path."/hosts/vidxden.php ".$videoFile." ". $vdxUser." ". $vdxPass." ". $cookie." ". $postData. " >> /dev/null 2>&1");
					
						$postData['videoFile'] = $videoFile;	
						$postData['vdxUser'] = $vdxUser;	
						$postData['vdxPass'] = $vdxPass;	
						$postData['cookie'] = $cookie;	
						//$func->curl($botURL.'hosts/vidxden.php', $postData);
						$curlData = array();
						$curlData['url'] = $botURL.'hosts/vidxden.php';	
						$curlData['post'] = $postData;	
						$processHosts[] = $curlData;
					}
					if($fnukEnabled)
					{
						echo "Uploading to Filenuke... \n";
						//exec("php ".$path."/hosts/filenuke.php ".$videoFile." ". $fnukUser." ". $fnukPass." ". $cookie." ". $postData. " >> /dev/null 2>&1");
					
						$postData['videoFile'] = $videoFile;	
						$postData['fnukUser'] = $fnukUser;	
						$postData['fnukPass'] = $fnukPass;	
						$postData['cookie'] = $cookie;	
						//$func->curl($botURL.'hosts/filenuke.php', $postData);
						$curlData = array();
						$curlData['url'] = $botURL.'hosts/filenuke.php';	
						$curlData['post'] = $postData;	
						$processHosts[] = $curlData;
					}

					/*if($ptoEnabled)
					{
						echo "Uploading to Played.to... \n";
						//exec("php ".$path."/hosts/playedto.php ".$videoFile." ". $ptoUser." ". $ptoPass." ". $cookie." ". $postData. " >> /dev/null 2>&1");
					
						$postData['videoFile'] = $videoFile;	
						$postData['ptoUser'] = $ptoUser;	
						$postData['ptoPass'] = $ptoPass;	
						$postData['cookie'] = $cookie;	
						//$func->curl($botURL.'hosts/playedto.php', $postData);
						$curlData = array();
						$curlData['url'] = $botURL.'hosts/playedto.php';	
						$curlData['post'] = $postData;	
						$processHosts[] = $curlData;
					}*/
					
		

					if($vodEnabled)
					{
						echo "Uploading to vodlocker... \n";
						//exec("php ".$path."/hosts/vodlocker.php ".$videoFile." ". $amvUser." ". $amvPass." ". $postData. " >> /dev/null 2>&1");
					
						$postData['videoFile'] = $videoFile;	
						$postData['vodUser'] = $vodUser;	
						$postData['vodPass'] = $vodPass;	
						//$func->curl($botURL.'hosts/vodlocker.php', $postData);
						$curlData = array();
						$curlData['url'] = $botURL.'hosts/vodlocker.php';	
						$curlData['post'] = $postData;	
						$processHosts[] = $curlData;
					}

					if ($vmeEnabled)
					{
						echo "Uploading to thevideo.me...\n";
						
						$postData['videoFile'] 	= $videoFile;
						$postData['vmeUser'] 	= $vmeUser;
						$postData['vmePass'] 	= $vmePass;
						$postData['cookie'] 	= $cookie;
						
						$curlData 				= array();
						$curlData['url'] 		= $botURL.'hosts/thevideome.php';
						$curlData['post'] 		= $postData;
						$processHosts[] 		= $curlData;
					}
					
					if($vpotEnabled)
					{
						echo "Uploading to vidspot... \n";
						//exec("php ".$path."/hosts/vidspot.php ".$videoFile." ". $amvUser." ". $amvPass." ". $postData. " >> /dev/null 2>&1");
					
						$postData['videoFile'] = $videoFile;	
						$postData['vpotUser'] = $vpotUser;	
						$postData['vpotPass'] = $vpotPass;	
						//$func->curl($botURL.'hosts/vidspot.php', $postData);
						$curlData = array();
						$curlData['url'] = $botURL.'hosts/vidspot.php';	
						$curlData['post'] = $postData;	
						$processHosts[] = $curlData;
					}
					
					if($olcoEnabled)
					{
						echo "Uploading to openload.co... \n";
	
						$postData['videoFile'] = $videoFile;
						$postData['olcoUser'] = $olcoUser;
						$postData['olcoPass'] = $olcoPass;
						
						$processHosts[] = array(
								'url'  => $botURL.'hosts/openloadco.php',	
								'post' => $postData
						);
					}
					
					if($gdriveEnabled)
					{
						echo "Uploading to GDrive... \n";
							
						$postData['videoFile'] = $videoFile;
						$postData['gdriveCommand'] = $gdriveCommand;
						$postData['user'] = $imovietoUser;
						$postData['pass'] = $imovietoPass;
						
						$processHosts[] = array(
								'url'  => $botURL.'hosts/gdrive.php',
								'post' => $postData
						);
					}

					if($mangoEnabled)
					{
						echo "Uploading to streamango.com... \n";
	
						$postData['videoFile'] = $videoFile;
						$postData['mangoUser'] = $mangoUser;
						$postData['mangoPass'] = $mangoPass;
						
						$processHosts[] = array(
								'url'  => $botURL.'hosts/streamango.php',	
								'post' => $postData
						);
					}

					if($cherryEnabled)
					{
						echo "Uploading to streamcherry.com... \n";
					
						$postData['videoFile'] = $videoFile;
						$postData['cherryUser'] = $cherryUser;
						$postData['cherryPass'] = $cherryPass;
					
						$processHosts[] = array(
								'url'  => $botURL.'hosts/streamcherry.php',
								'post' => $postData
						);
					}
					
					if($watchersEnabled)
					{
					    echo "Uploading to watchers.to... \n";
					    
					    $postData['videoFile'] = $videoFile;
					    $postData['watchersUser'] = $watchersUser;
					    $postData['watchersPass'] = $watchersPass;
					    
					    $processHosts[] = array(
					        'url'  => $botURL.'hosts/watchers.php',
					        'post' => $postData
					    );
					}


					
					$r = $func->multiRequest($processHosts);
					print_r($r);
					/*if($is_file)
						exec('rm -rf '.$downloadDir.$dirName.' >> /dev/null 2>&1');
					if($rarfile)
						exec('rm -rf '.$tempDir.$dirName.' >> /dev/null 2>&1');
					
						exec('rm -rf '.$uploadDir.$dirName.' >> /dev/null 2>&1');
							
					die("...oOops...enough for now...rerun again...\n");	*/
					}	
		//exec("php /var/www/html/bot/mengine.php $torrent");
