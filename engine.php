<?php
set_time_limit(0);
error_reporting(E_ALL);
ini_set("display_errors",1);

$path = dirname( __FILE__ ) . '/';
require_once $path . 'inc/torrent.php';
require_once $path . 'inc/func.php';
require_once $path . '/inc/TransmissionRPC.class.php';
require_once $path . 'config.php';
/*


					  $rpc = new TransmissionRPC();
					  $result = $rpc->sstats( );
					  $added = $rpc->get(10);
					  $c = $added->arguments->torrents[0]->name;
					  if(is_dir($path."downloads/".$c))
						echo 'yes';
					  else
						echo 'nnnno';
					  die();
$t = 'DCI Banks S01E05 Wednesdays Child Part One HDTV XviD-AFG';
$t2 = 'DCI Banks 4x01 Wednesdays Child Part One HDTV XviD-AFG';
$func = new Func();
print_r($func->cleanTitle('tv',$t));
print_r($func->cleanTitle('tv',$t2));
die();
$func = new Func();
				$postData = array(
									"type"=>'tv',
									"year"=>'2010',
									"full_title"=>'Switched at Birth',
									"clean_title"=>'',
									"season"=>'',
									"episode"=>'',
									"feed_title"=>'',
									"torrent"=>''
									);
				$id = $func->getID($postData);
*/				
// DO NOT CHANGE BELOW:			
$titlesLog = $path. 'logs/titles.txt';	
$noTitleLog = $path. 'logs/noTitleLog.txt';	
$noEpisode = $path. 'logs/noEpisode.txt';	
$processedLog = $path. 'logs/processedLink.txt';	
$noIDonIWOLog = $path. 'logs/noIDonIWO.txt';	
$downloadDir = $path."downloads/";
$uploadDir = $path."upload/";
$tempDir = $path."temp/";
$cookie = $path."temp/cookie.txt";
$rarrunning = $path."temp/rar.txt";

$processed = file($processedLog, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
if( !$processed ) $processed = array();
$titles = file($titlesLog, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
if( !$titles ) $titles = array();
$func = new Func();
$func->MaxTorrents($maxTorrentProcess);
/***

			$postData = array(
									"year"=>'',
									"full_title"=>'Hotel Impossible',
									"clean_title"=>'Hotel Impossible',
									"season"=>'04',
									"episode"=>'03',
									"type"=>'tv',
									"feed_title"=>'Hotel Impossible S04E03 HDTV XviD-AFG',
									"torrent"=>''
									);
				echo "Getting ID from IWO for Hotel Impossible \n";
				$iwoid = $func->getID($postData);
				//$e_id = explode("||EP||",$iwoid);
				print_r($iwoid['e_id']); die();
***/
$process = array();
echo "\n Awesomeness started, Getting Feeds \n";	
//print_r($feeds);
foreach($feeds as $url => $feed):
	$page = $func->getFeed($feed['feed']);
	$xml = simplexml_load_string($page);
	if(!isset($xml->channel->item))
				continue;

		foreach ($xml->channel->item as $item) { 
            echo $item->title."\n";
			if(in_array($item->link, $processed))
				continue;
			$func->log($processedLog,$item->link); // log link to not process again	
			$cleanTitle = $func->cleanTitle($feed['type'],$item->title);
			
			if(!$cleanTitle)
			{
				$msg = "Cant find release format title from torrent feed: Skipping >> ". $item->title ." >> " . $item->link;
				$func->log($noTitleLog,$msg);
					continue;
			}	
			$title = ($feed['type'] == "tv")?$cleanTitle['title'].' S'.$cleanTitle['season'].'E'.$cleanTitle['episode']:$cleanTitle['title'];
			if(in_array($title, $titles))
			{
				echo "$title in titles log, skipping \n";
				//continue;
			}
			$func->log($titlesLog,$title);	// log title to not process again

			$postData = array(
						"year"=>$cleanTitle['year'],
						"full_title"=>$cleanTitle['full_title'],
						"clean_title"=>$cleanTitle['title'],
						"season"=>$cleanTitle['season'],
						"episode"=>$cleanTitle['episode'],
						"type"=>$feed['type'],
						"feed_title"=>(string)$item->title,
						"torrent"=>(string)$item->link
						);
			$postData['active'] = 1;
			$postData['userid'] = $iwoUserid;
			$postData['language'] = $feed['language'];
			$postData['link_type'] = $feed['quality_id'];
			$postData['subtitle'] = (isset($feed['subtitle']))? $feed['subtitle'] : null;
			
			//detect language
			$langExist = strpos(strtolower((string)$item->link), 'french');
			if( $langExist !== false ) {
			    $postData['language'] = 'French';
			}
			$langExist = strpos(strtolower((string)$item->link), 'german');
			if( $langExist !== false ) {
			    $postData['language'] = 'German';
			}
			$langExist = strpos(strtolower((string)$item->link), 'spanish');
			if( $langExist !== false ) {
			    $postData['language'] = 'Spanish';
			}
			

				echo "Getting ID from IWO for {$item->title} \n";
				$iwoid = $func->getID($postData);
				if(!isset($iwoid['id']))
				{
					$msg = "Cant find ID on IWO : Skipping >> ". $item->title ." >> " ;
					echo $msg ."\n";
					$msg .= print_r($postData, true);
					$func->log($noIDonIWOLog,$msg);
					continue;
				}
				echo "### ".$iwoid['id']."\n";
				if($feed['type'] == "tv")
				{
					if(!$iwoid['e_id'])
					{
						sleep(4);
						$iwoid = $func->getID($postData);
					}
					if(!$iwoid['e_id'])
					{
						sleep(4);
						$iwoid = $func->getID($postData);
					}
					if(!$iwoid['e_id'])
					{
						$msg = "Cant get episode id from iwo: Skipping >> ". $item->title;
						$msg .= print_r($postData, true);
						$func->log($noEpisode,$msg);
						continue;
					}	
					$postData['e_id'] = $iwoid['e_id'];
				}
					echo "Found ID for {$item->title} \n";	
					$postData['id'] = $iwoid['id'];
					$process[] = $postData;
					print_r($postData);

					echo "Downloading torrent file  \n";	
					$torrent_file = $path."/torrents/".time().".torrent";
					$func->downloadfile($postData['torrent'],$torrent_file);
					sleep(2);
					//echo "downloaded ".$postData['torrent']."\n";
					
					$torrentinfo = new Torrent($torrent_file);
					$dirName = trim($torrentinfo->info['name']);
					
					if($torrentinfo->size() > $maxTorrentSize)
					{
						echo "Torrent size is greater then set in config. Skipping... \n";
					}
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
							
						//while (!file_exists($rarrunning)) sleep(1);
						//$func->log($rarrunning,'');
						//$exec = exec( "'".$path."rar/rar' e -y '$rarfile' '$tempDir'", $command_output, $result);
						$exec = exec( "rar e -y '$rarfile' '$tempDir'", $command_output, $result);
						unlink($rarrunning);
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
						
						$fname = $postData['id'].'-'.$feed['quality'];
						if($postData['season'])
							$fname .= '-S'.$postData['season'].'E'.$postData['episode'];
						$fname .= '-iwatchonline.cr.'.$ext;
						
						$releaseFolder = $uploadDir.$dirName;
							mkdir($releaseFolder, 0777, true);
						echo "Renaming and copying file \n";
						copy($videoFile, $releaseFolder.'/'.$fname);
					$videoFile = $releaseFolder.'/'.$fname;	
					$botURL = 'http://95.211.168.168/bot/';	
					
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
					if($sloEnabled)
					{
						echo "Uploading to streamcloud... \n";
						//exec("php ".$path."/hosts/vidspot.php ".$videoFile." ". $amvUser." ". $amvPass." ". $postData. " >> /dev/null 2>&1");
					
						$postData['videoFile'] = $videoFile;	
						$postData['sloUser'] = $vpotUser;	
						$postData['sloPass'] = $vpotPass;	
						//$func->curl($botURL.'hosts/vidspot.php', $postData);
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
					/*
					if($is_file)
						exec('rm -rf '.$downloadDir.$dirName.' >> /dev/null 2>&1');
					if($rarfile)
						exec('rm -rf '.$tempDir.$dirName.' >> /dev/null 2>&1');
					
						exec('rm -rf '.$uploadDir.$dirName.' >> /dev/null 2>&1');
					*/		
					die("...oOops...enough for now...rerun again...\n");	
					}	
		}
endforeach;