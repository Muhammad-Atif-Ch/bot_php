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
		$videoFile = 'vf';
		$putlockerCookie = 'pl';
		$postData['season'] = 111;
		$postData['episode'] = 222;
		$postData = base64_encode(implode("|||",$postData));
		exec("php ".$path."/hosts/test.php ".$videoFile." ". $putlockerCookie." ". $videoFile. " ", $output, $ret);
		print_r($output);
		print_r($ret);
		die();
		
		
		
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
		}
		else
		{
			$torrent = array();
			$torrent['tmp_name'] = $_FILES['torrent_file']['tmp_name'];
			$torrent['f_name'] = $_FILES['torrent_file']['name'];
			$torrent['remote'] = $_POST['remote'];
			$torrent['type'] = $_POST['torrent_type'];


			$postData['content_id'] = $_POST['iwo_id'];
			$postData['id'] = $_POST['iwo_id'];
			$postData['season'] = $_POST['season'];
			$postData['episode'] = $_POST['episode'];
		
			$postData['active'] = $_POST['active'];
			$postData['userid'] = $_POST['userid'];
			$postData['language'] = $_POST['language'];
			$postData['link_type'] = $_POST['link_type'];
			$postData['quality'] = $_POST['quality'];
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
						$fname .= '-iwatchonline.to.'.$ext;
						
						$releaseFolder = $uploadDir.$dirName;
							mkdir($releaseFolder, 0777, true);
						echo "Renaming and copying file \n";
						copy($videoFile, $releaseFolder.'/'.$fname);
					$videoFile = $releaseFolder.'/'.$fname;	
					//$postData = base64_encode(implode("|||",$postData));


						$content_id = $postData['content_id'];
						$id = $postData['id'];
						$season = $postData['season'];
						$episode = $postData['episode'] ;
						$e_id = $postData['e_id'];
						$active = $postData['active'];
						$userid = $postData['userid'];
						$language = $postData['language'] ;
						$link_type = $postData['link_type'] ;
						$quality = $postData['quality'];
						
					if($putlEnabled)
					{

			
						//$content_id." ".$id." ".$season." ".$episode." ".$e_id." ".$active." ".$userid." ".$language." ".$link_type." ".$quality	
						echo "Uploading to Firedrive... \n";
						exec("php ".$path."/hosts/firedrive.php ".$videoFile." ". $putlockerCookie." ". $content_id." ".$id." ".$season." ".$episode." ".$e_id." ".$active." ".$userid." ".$language." ".$link_type." ".$quality. " ", $output, $ret); print_r($output);print_r($ret);
						//exec("php ".$path."/hosts/firedrive.php ".$videoFile." ". $putlockerCookie." ". $postData. " >> /dev/null 2>&1");
					}
					if($sshareEnabled)
					{
						echo "Uploading to Sockshare... \n";
						exec("php ".$path."/hosts/sockshare.php ".$videoFile." ". $sockshareCookie." ". $content_id." ".$id." ".$season." ".$episode." ".$e_id." ".$active." ".$userid." ".$language." ".$link_type." ".$quality. " ", $output, $ret); print_r($output);print_r($ret);
						//exec("php ".$path."/hosts/sockshare.php ".$videoFile." ". $sockshareCookie." ". $postData. " >> /dev/null 2>&1");
					}
					if($amvEnabled)
					{
						echo "Uploading to Allmyvideos... \n";
						exec("php ".$path."/hosts/allmyvideos.php ".$videoFile." ". $amvUser." ". $amvPass." ". $content_id." ".$id." ".$season." ".$episode." ".$e_id." ".$active." ".$userid." ".$language." ".$link_type." ".$quality. " ", $output, $ret); print_r($output);print_r($ret);
						//exec("php ".$path."/hosts/allmyvideos.php ".$videoFile." ". $amvUser." ". $amvPass." ". $postData. " >> /dev/null 2>&1");
					}
					if($vttEnabled)
					{
						echo "Uploading to Video.tt... \n";
						exec("php ".$path."/hosts/videott.php ".$videoFile." ". $vttUser." ". $vttPass." ". $cookie." ". $content_id." ".$id." ".$season." ".$episode." ".$e_id." ".$active." ".$userid." ".$language." ".$link_type." ".$quality. " ", $output, $ret); print_r($output);print_r($ret);
						//exec("php ".$path."/hosts/videott.php ".$videoFile." ". $vttUser." ". $vttPass." ". $cookie." ". $postData. " >> /dev/null 2>&1");
					}
					if($ptoEnabled)
					{
						echo "Uploading to Played.to... \n";
						exec("php ".$path."/hosts/playedto.php ".$videoFile." ". $ptoUser." ". $ptoPass." ". $cookie." ". $content_id." ".$id." ".$season." ".$episode." ".$e_id." ".$active." ".$userid." ".$language." ".$link_type." ".$quality. " ", $output, $ret); print_r($output);print_r($ret);
						//exec("php ".$path."/hosts/playedto.php ".$videoFile." ". $ptoUser." ". $ptoPass." ". $cookie." ". $postData. " >> /dev/null 2>&1");
					}
					if($mvrEnabled)
					{
						echo "Uploading to Movreel... \n";
						exec("php ".$path."/hosts/movreel.php ".$videoFile." ". $mvrUser." ". $mvrPass." ". $cookie." ". $content_id." ".$id." ".$season." ".$episode." ".$e_id." ".$active." ".$userid." ".$language." ".$link_type." ".$quality. " ", $output, $ret); print_r($output);print_r($ret);
						//exec("php ".$path."/hosts/movreel.php ".$videoFile." ". $mvrUser." ". $mvrPass." ". $cookie." ". $postData. " >> /dev/null 2>&1");
					}
					if($vdbEnabled)
					{
						echo "Uploading to Vidbux... \n";
						exec("php ".$path."/hosts/vidbux.php ".$videoFile." ". $vdbUser." ". $vdbPass." ". $cookie." ". $content_id." ".$id." ".$season." ".$episode." ".$e_id." ".$active." ".$userid." ".$language." ".$link_type." ".$quality. " ", $output, $ret); print_r($output);print_r($ret);
						//exec("php ".$path."/hosts/vidbux.php ".$videoFile." ". $vdbUser." ". $vdbPass." ". $cookie." ". $postData. " >> /dev/null 2>&1");
					}
					if($vdxEnabled)
					{
						echo "Uploading to Vidxden... \n";
						exec("php ".$path."/hosts/vidxden.php ".$videoFile." ". $vdxUser." ". $vdxPass." ". $cookie." ". $content_id." ".$id." ".$season." ".$episode." ".$e_id." ".$active." ".$userid." ".$language." ".$link_type." ".$quality. " ", $output, $ret); print_r($output);print_r($ret);
						//exec("php ".$path."/hosts/vidxden.php ".$videoFile." ". $vdxUser." ". $vdxPass." ". $cookie." ". $postData. " >> /dev/null 2>&1");
					}
					if($fnukEnabled)
					{
						echo "Uploading to Filenuke... \n";
						exec("php ".$path."/hosts/filenuke.php ".$videoFile." ". $fnukUser." ". $fnukPass." ". $cookie." ". $content_id." ".$id." ".$season." ".$episode." ".$e_id." ".$active." ".$userid." ".$language." ".$link_type." ".$quality. " ", $output, $ret); print_r($output);print_r($ret);
						//exec("php ".$path."/hosts/filenuke.php ".$videoFile." ". $fnukUser." ". $fnukPass." ". $cookie." ". $postData. " >> /dev/null 2>&1");
					}

					/*if($is_file)
						exec('rm -rf '.$downloadDir.$dirName.' >> /dev/null 2>&1');
					if($rarfile)
						exec('rm -rf '.$tempDir.$dirName.' >> /dev/null 2>&1');
					
						exec('rm -rf '.$uploadDir.$dirName.' >> /dev/null 2>&1');
							
					die("...oOops...enough for now...rerun again...\n");	*/
					}	
		//exec("php /var/www/html/bot/mengine.php $torrent");
