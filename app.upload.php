<?php if($_GET['step'] == 'reset') { ?>
	<?php 
	$tempData = $db->Raw("SELECT `location` FROM `userdb_temporary` WHERE `user`='$user'");
	try {
		unlink($tempData[0]['location']);
	} catch (Exception $e) { }
	$db->Raw("DELETE FROM `userdb_temporary` WHERE `user`='$user' LIMIT 1"); // limit for good coding practice
	?>
	<?php if(isset($_GET['fb_page_id'])) { redirect('' . $config['fb']['fburl'] . '?tab=index&fb_page_id=' . $_GET['fb_page_id'] . ''); } else { redirect('' . $config['fb']['fburl'] . '?tab=index'); } ?>
<?php } elseif($_GET['step'] == 2) { ?>
	<?php
   
   $error_msgs = array(
      'no_file' => 'You didn\'t provide us a file for us to process.',
      'file_format' => 'You gave us a file we could not understand, please check your file format and try again.',
      'file_size' => 'The file you gave us was too big, please give us a smaller file.',
      'bad_hash' => 'Couldn\'t authorize your upload, you might have cntl+t\'ed.',
      'temp_exists' => 'We already have a from you! Please continue or delete that entry.'
   );
		
   if (count($db->Raw("SELECT `user` FROM `userdb_temporary` WHERE `user`='$user'")) > 0)
      $error = 'temp_exists';
   else if ($_GET['hash'] !== md5($_GET['credit'] . ':' . $_GET['usage'] . ':' . $user . ':' . $config['fb']['secret']))
      $error = 'bad_hash';
   else if ($_FILES['upfile']['name'] == NULL)
      $error = 'no_file';
   else if (!in_array(strtolower(substr($_FILES['upfile']['name'], strrpos($_FILES['upfile']['name'], '.') + 1)), array('mp3','m4a','mp4','aac','flv')))
      $error = 'file_format';
   else if ($_FILES['upfile']['size'] >= 20971520 || !file_exists($_FILES['upfile']['tmp_name']))
      $error = 'file_size';
 
   if (isset($error))
   {
      $url_append = '';
      if (isset($_GET['fb_page_id']))
         $url_append = '&fb_page_id=' . $_GET['fb_page_id'];

      $facebook->redirect($config['fb']['fburl'] . "?tab=index&error=" . urlencode($error_msgs[$error]) . $url_append);
      die();
   }
   ?>
		<?php
	
	// We are first going to check it there's something already our control.
	// If someone pressed the back button on their browser, they'll have a cache of old page
	// So we're gonna assume that they want this new song in place
	// therefore, we're going to replace it with the new data
	
	$tempData = $db->Raw("SELECT `location` FROM `userdb_temporary` WHERE `user`='$user'");
	if (count($tempData) > 0) {
		try {
			unlink($tempData[0]['location']);
		} catch (Exception $e) { }
		$db->Raw("DELETE FROM `userdb_temporary` WHERE `user`='$user' LIMIT 1"); // limit for good coding practice
	}
	
	require_once('include/getid3/getid3.php');
	$getid3 = new getID3;

	try 
	{
		$id3data = $getid3->analyze($_FILES['upfile']['tmp_name']);
		getid3_lib::CopyTagsToComments($id3data);
		$title = htmlspecialchars(utf8_encode($id3data['comments_html']['title'][0]), ENT_QUOTES);
		$artist = htmlspecialchars(utf8_encode($id3data['comments_html']['artist'][0]), ENT_QUOTES);
		$playtime = $id3data['playtime_seconds'];
		$sample_rate = $id3data['audio']['sample_rate'];
		$filesize = $id3data['filesize'];
		$fileformat = $id3data['fileformat'];
	}
	catch (Exception $e) 
	{
		echo 'ID3tag ERROR: ' .  $e->message;
	}

	include 'include/class.encryption.php';
	$encryption = new encryption_class();
	$md5 = md5_file($_FILES['upfile']['tmp_name']);
	$secure_file_name = $encryption->encrypt(sha1($user), $md5);

	$secure_temporary_location = '' . $config['server']['internal_url'] . 'temp/' . $secure_file_name . '.' . strtolower(substr($_FILES['upfile']['name'], strrpos($_FILES['upfile']['name'], '.') + 1)) . '';
	rename($_FILES['upfile']['tmp_name'], $secure_temporary_location);

	$db->Raw("INSERT INTO `userdb_temporary` (`user`,`title`,`artist`,`md5`,`filesize`,`fileformat`,`sample_rate`,`location`,`playtime`) VALUES ('$user','$title','$artist','$md5','$filesize','$fileformat','$sample_rate','$secure_temporary_location','$playtime')"); 
	?>
	
	<div style="border: 1px solid #e2c822; background-color: #fff9d7; padding: 5px;">
	<b><a href="<?php echo "" . $config['fb']['fburl'] . "?tab=index&display=add&method=upload&step=3" . pages($_GET['fb_page_id']) . ""; ?>">Please click here if you are not automatically redirected within five seconds...</a></b>
	</div>
	<?php if(isset($_GET['fb_page_id'])) { $facebook->redirect("" . $config['fb']['fburl'] . "?tab=index&display=add&method=upload&step=3&fb_page_id=" . $_GET['fb_page_id'] . ""); } else { $facebook->redirect("" . $config['fb']['fburl'] . "?tab=index&display=add&method=upload&step=3"); } ?>

<?php } elseif ($_GET['step'] == 3) { ?>
	<?php if(isset($error)) { ?>
		<?php if($error == 'missing_information') { ?>
			<?php error('Not Enough Information','I need ALL of the boxes filled below.'); ?>
		<?php } ?>
	<?php } else { ?>
		<?php explanation('Song Information (ID3)','Here is what I got from what you uploaded, but I\'m not sure if it is right. Do me a favor and check it over and press sumbit when you\'re done.'); ?>

		<?php $temporary_information = $db->Raw("SELECT `title`,`artist`,`filesize`,`sample_rate`,`fileformat`,`playtime` FROM `userdb_temporary` WHERE `user`='$user' LIMIT 1"); ?>
		
		<?php if(count($temporary_information) == '0') { ?>
			<?php if(isset($_GET['fb_page_id'])) { error('Error','What the?! Something wrong happened, try going <a href="' . $config['fb']['fburl'] . '?action=2&method=upload&fb_page_id=' . $_GET['fb_page_id'] . '">back</a> and try again.'); } else { error('Error','An unexpected error has occurred, please go <a href="' . $config['fb']['fburl'] . '?action=2&method=upload">back</a> and try again.'); } ?>
		<?php } else { ?>
			<?php $external_temp = substr($temporary_information['0']['location'], $temporary_information['0']['location'], '/') + 1; ?>
			<table border="0" cellpadding="0" cellspacing="0" width="100%">
				<tr>
				
					<td valign="top">
					<div style="background-color: #fff5b1; border: 1px solid #ffd04d; padding: 10px; font-size: 16px; text-align: center; margin: 10px 0px 10px 20px;">
						File Size: <?php echo round(($temporary_information[0]['filesize']/1000000), 2); ?>MB<br />
						File Format: <?php echo $temporary_information[0]['fileformat']; ?><br />
						Sample Rate: <?php echo round(($temporary_information[0]['sample_rate']/1000), 1); ?>KHz<br />
						Playtime: <?php echo round($temporary_information[0]['playtime']/60, 2); ?> min.
					</div>
					</td>
				
					<td valign="top">
						<div align="center" style="margin:10px 20px 10px 10px; background-color: #f7f7f7; border: 1px solid #cccccc;">
						<fb:editor action="?tab=index&display=add&method=upload&step=4<?php if(isset($_GET['fb_page_id'])) { echo '&fb_page_id=' . $_GET['fb_page_id'] . ''; } ?>" labelwidth="100">
							<fb:editor-text label="Title" name="title" value="<?php echo htmlspecialchars_decode(utf8_decode($temporary_information[0]['title']), ENT_QUOTES); ?>" maxlength="100" />
							<fb:editor-text label="Artist" name="artist" value="<?php echo htmlspecialchars_decode(utf8_decode($temporary_information[0]['artist']), ENT_QUOTES); ?>" maxlength="100" />
							<?php
							if (isset($_GET['fb_page_id'])) {
							?>
							<fb:editor-text label="Buy Link" name="buy_link" value="<?php echo $db_info[0]['buy_link']; ?>" maxlength="100" />
							<?php
							}
							?>
							<fb:editor-custom label="Post to Wall?">
								<input type="checkbox" name="wall" value="true">
							</fb:editor-custom>
							<fb:editor-buttonset>
								<fb:editor-button value="Submit"/>
							</fb:editor-buttonset>
						</fb:editor>
						</div>
					</td>
					
				</tr>
			</table>
		<?php } ?>
	<?php } ?>
<?php } elseif ($_GET['step'] == 4) { ?>
	<?php if($_POST['title'] == NULL or $_POST['artist'] == NULL) { ?>
		<?php if(isset($_GET['fb_page_id'])) { redirect('' . $config['fb']['fburl'] . '?tab=index&display=add&method=upload&step=3&error=missing_information&fb_page_id=' . $_GET['fb_page_id'] . ''); } else { redirect('' . $config['fb']['fburl'] . '?tab=index&display=add&method=upload&step=3&error=missing_information'); } ?>
	<?php } else { ?>
		
		<?php $title = htmlspecialchars(utf8_encode($_POST['title']), ENT_QUOTES); $artist = htmlspecialchars(utf8_encode($_POST['artist']), ENT_QUOTES); ?>
		<?php $tempData = $db->Raw("SELECT `md5`,`filesize`,`fileformat`,`playtime`,`sample_rate`,`location` FROM `userdb_temporary` WHERE `user`='$user'"); ?>
		<?php $filesize = $tempData[0]['filesize']; $sample_rate = $tempData[0]['sample_rate']; $fileformat = $tempData[0]['fileformat']; $md5 = $tempData[0]['md5']; $playtime = $tempData[0]['playtime']; ?>

		<?php		
      include 'include/aws/sdk.class.php';
      $s3 = new AmazonS3();

      $s3->create_object('fb-music', basename($tempData[0]['location']), array(
         'fileUpload' => $tempData[0]['location'],
         'acl' => AmazonS3::ACL_AUTH_READ,
         'storage' => AmazonS3::STORAGE_REDUCED,
      ));

      /*
      $selDrive = $db->Raw("SELECT `data` FROM `system` WHERE `var`='drive'");
		$userFolder = array_sum(str_split($user));
		
		if(!file_exists('users/' . $selDrive[0]['data'] . '/' . $userFolder . '/'))
			mkdir('users/' . $selDrive[0]['data'] . '/' . $userFolder . '/');
		rename($tempData[0]['location'], 'users/' . $selDrive[0]['data'] . '/' . $userFolder . '/' . basename($tempData[0]['location']) . '');
		*/
		$db->Raw("DELETE FROM `userdb_temporary` WHERE `user`='$user' LIMIT 1");
	   unlink($tempData[0]['location']);	
      /*
		$link = '' . $config['server']['streaming'] . '/stream/' . $selDrive[0]['data'] . '/' . $userFolder . '/' . basename($tempData[0]['location']) . '';
		$drive = $selDrive[0]['data'];
		*/

      $link = basename($tempData[0]['location']);
      

      $db->Raw("INSERT INTO `userdb_uploads` (`user`,`title`,`artist`,`md5`,`filesize`,`sample_rate`,`fileformat`,`type`,`link`,`playtime`,`buy_link`,`server`,`drive`) VALUES ('$user','$title','$artist','$md5','$filesize','$sample_rate','$fileformat','upload','$link','$playtime','$_POST[buy_link]','s3','s3')");
		
		//need to get a STATIC XID from id
		$id = $db->Raw("SELECT `id` FROM `userdb_uploads` WHERE `user`='$user' ORDER BY `id` DESC LIMIT 1");
		$id = $id[0]['id'];
		$db->Raw("UPDATE `userdb_uploads` SET `xid`=`id` WHERE `id`='$id'");
		?>	

	
		<?php // if(!isset($_GET['fb_page_id'])) { include 'fb.feed.php'; } ?>
		
		<?php 
		if($_POST['wall'])
			if(isset($_GET['fb_page_id'])) { redirect('' . $config['fb']['fburl'] . '?tab=index&publish&fb_page_id=' . $_GET['fb_page_id'] . ''); } else { redirect('' . $config['fb']['fburl'] . '?tab=index&publish'); } 
		else
			if(isset($_GET['fb_page_id'])) { redirect('' . $config['fb']['fburl'] . '?tab=index&fb_page_id=' . $_GET['fb_page_id'] . ''); } else { redirect('' . $config['fb']['fburl'] . '?tab=index'); }
		?>
		
	<?php } ?>
<?php } ?>
