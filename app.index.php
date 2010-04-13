<?php
/*
First display page (display == null) will show the editing player functions for the user.

The page is split into two columns by a table, then in those columns are tables.
The left hand column will display the user's player and edit functions,
while the right hand column simply displays editing information.

Below the editor is a button where a user can add a song.
*/
?>

<div style="margin-bottom: -5px; padding: 5px; border-bottom: 1px solid #cccccc; background-color: #eceff5;">
<?php if(isset($_GET['fb_page_id'])) { ?>
	<table border="0" cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td width="75%">
			<?php 
			$pdata = $db->Raw("SELECT `name`,`status` FROM `pages` WHERE `fb_page_id`='$_GET[fb_page_id]'");
			
			if ($pdata[0]['name'] == '') {
				$pdata = $facebook->api_client->fql_query("SELECT name FROM page WHERE page_id='$_GET[fb_page_id]'");
				$name = $pdata[0]['name'];
				$db->Raw("UPDATE `pages` SET `name`='$name' WHERE `fb_page_id`='$_GET[fb_page_id]'");
			}
			?>
				
			You are currently editing page, <b><?php echo $pdata[0]['name']; ?></b>, change back to your <b><a href="<?php echo $config['fb']['fburl'] ?>">profile</a></b>?
		</td>
		<td width="25%" style="text-align: right;">
		<?php 
		echo ('<b>');
		if ($pdata[0]['status'] == '1')
			echo ('Verification Submitted');
		elseif ($pdata[0]['status'] == '3')
			echo ('Verification Issue. <a href="?tab&verify&fb_page_id='. $fb_page_id . '">Resolve Now</a>.');
		elseif ($pdata[0]['status'] == '2')
			echo ('Verified!');
		elseif ($pdata[0]['status'] == '0')
			echo ('Unverified. <a href="?tab&verify&fb_page_id='. $fb_page_id . '">Verify Now</a>.');
		echo ('</b>');
		?>
		</td>
	</tr>
	</table>
<?php } else { ?>
	<?php 
	$pdata = $db->Raw("SELECT `fb_page_id`,`name` FROM `pages` WHERE `owner`='$user'");
	if(count($pdata) > 0) {
		$count = 0;
		foreach ($pdata as $parse) {
			$count = $count+1;
			if($count !== 1 AND $count == count($pdata)) $page_string='' . $page_string . 'or ';
			if($parse['name'] == '') {
				$pname = $facebook->api_client->fql_query("SELECT name FROM page WHERE page_id='$parse[fb_page_id]'");
				$pname = $pname[0]['name'];
				$parse['name'] = $pname;
				$db->Raw("UPDATE `pages` SET `name`='$pname' WHERE `fb_page_id`='$parse[fb_page_id]'");
			}
			$page_string = '' . $page_string . '<b><a href="' . $config['fb']['fburl'] . '?fb_page_id=' . $parse['fb_page_id'] . '">' . $parse['name'] . '</a></b>';
			$page_string='' . $page_string . ', ';
		} 
		
		$page_string = substr_replace($page_string,"",-2);
		?>
		
		You are currently editing your <b>profile</b>, change to page <?php echo $page_string; ?>? <b><a href="http://www.facebook.com/add.php?api_key=<?php echo $config['fb']['key']; ?>&pages&_fb_q=1">Add more</a></b>?
	<?php 
	} else {
	?>
		You are currently editing your <b>profile</b>. No other players or pages have been detected, <b><a href="http://www.facebook.com/add.php?api_key=<?php echo $config['fb']['key']; ?>&pages&_fb_q=1">add a page</a></b>.
	<?php } ?>
<?php } ?>
</div>

<?php if ($_GET['display'] == NULL) { ?>
	<div style="margin: 10px">
	<table border="0" width="100%" cellspacing="5px">
		<tr>
			<td valign="top">
				<div style="border: 1px solid #cccccc; padding: 10px; margin-top: 5px;">
				<table border="0" width="100%">
					<tr>
						<td valign="top">
							<div>
								<?php 
								if (isset($_GET['hide_box'])) {
									if (isset($_GET['fb_page_id']))
										$db->Raw("UPDATE `pages` SET `comment_box`='0' WHERE `fb_page_id`='$_GET[fb_page_id]'");
									else
										$db->Raw("UPDATE `userdb_users` SET `comment_box`='0' WHERE `user`='$user'");
								} elseif (isset($_GET['show_box'])) {
									if (isset($_GET['fb_page_id']))
										$db->Raw("UPDATE `pages` SET `comment_box`='1' WHERE `fb_page_id`='$_GET[fb_page_id]'");
									else
										$db->Raw("UPDATE `userdb_users` SET `comment_box`='1' WHERE `user`='$user'");
								}
								?>
								
								<?php 
								if (isset($_GET['fb_page_id']))
									$commentBoxStatus = $db->Raw("SELECT `comment_box` FROM `pages` WHERE `fb_page_id`='$_GET[fb_page_id]'");
								else
									$commentBoxStatus = $db->Raw("SELECT `comment_box` FROM `userdb_users` WHERE `user`='$user'");
								?>
								<div style="float: left;"><img src="<?php echo $config['fb']['appcallbackurl']; ?>images/edit_playlist.gif" align="top" border="0"></div>
								<div style="float: right; margin-top: 10px; margin-right: 5px;"><?php if ($commentBoxStatus[0]['comment_box'] == 1) { ?><a href="<?php echo $config['fb']['fburl']; ?>?tab=index&hide_box<?php pages($_GET['fb_page_id']); ?>">Hide Comment Box</a><?php } else { ?><a href="<?php echo $config['fb']['fburl']; ?>?tab=index&show_box<?php pages($_GET['fb_page_id']); ?>">Show Comment Box</a><?php } ?> - <?php if (isset($_GET['update'])) { ?>Updated<?php } else { ?><a href="<?php echo $config['fb']['fburl']; ?>?tab=index&update<?php pages($_GET['fb_page_id']); ?>">Update Player to <?php if(isset($_GET['fb_page_id'])) echo 'Page'; else echo 'Profile'; ?></a><?php } ?></div>
							</div>
						</td>
					</tr>
					<tr>
						<td valign="top">
							<?php include 'app.edit.php'; ?>
							<?php if (isset($_GET['update'])) {
								include 'fb.profile.php';
							} elseif (isset($_GET['publish'])) { 
								include 'fb.publish.php';
							}
							?>
							<br />
							<?php if ($_GET['action'] !== 'edit') { ?>
								<div style="margin-left: 5px; float: left;"><table border="0" width="525px"><tr><td width="50%"><fb:add-section-button section="profile" /></td><td width="50%"><div align="right"><a href="<?php echo $config['fb']['fburl']; ?>?tab=index&display=add<?php pages($_GET['fb_page_id']); ?>"><img src="<?php echo $config['fb']['appcallbackurl']; ?>images/add_song.png" border="0"></a></div></td></tr></table></div>
							<?php } ?>
						<td>	
					</tr>
				</table>
				</div>
			</td>

			<td width="170px" valign="top">
			<div style="margin: 5px; padding: 10px; border: 1px solid #cccccc;">
				<center><b>EDITOR KEY</b></center>
				<table border="0">
					<tr>
						<td style="border: 1px solid #cccccc; padding: 2px;"><img src="<?php echo $config['fb']['appcallbackurl']; ?>images/arrow_up.png" align="top" border="0"></td><td style="padding-left: 5px;"><fb:intl>move song up</fb:intl></td>
					</tr>
					<tr>	
						<td style="border: 1px solid #cccccc; padding: 2px;"><img src="<?php echo $config['fb']['appcallbackurl']; ?>images/arrow_down.png" align="top" border="0"></td><td style="padding-left: 5px;"><fb:intl>move song down</fb:intl></td>
					</tr>
					<tr>
						<td style="border: 1px solid #cccccc; padding: 2px;"><img src="<?php echo $config['fb']['appcallbackurl']; ?>images/link.png" align="top" border="0"></td><td style="padding-left: 5px;"><fb:intl>get share link</fb:intl></td>
					</tr>
					<tr>
						<td style="border: 1px solid #cccccc; padding: 2px;"><img src="<?php echo $config['fb']['appcallbackurl']; ?>images/embed.png" align="top" border="0"></td><td style="padding-left: 5px;"><fb:intl>get embed code</fb:intl></td>
					</tr>
					<tr>	
						<td style="border: 1px solid #cccccc; padding: 2px;"><img src="<?php echo $config['fb']['appcallbackurl']; ?>images/tag_blue_edit.png" align="top" border="0"></td><td style="padding-left: 5px;"><fb:intl>edit settings</fb:intl></td>
					</tr>
					<tr>	
						<td style="border: 1px solid #cccccc; padding: 2px;"><img src="<?php echo $config['fb']['appcallbackurl']; ?>images/delete.png" align="top" border="0"></td><td style="padding-left: 5px;"><fb:intl>delete song from player</fb:intl></td>
					</tr>
				</table>
			</td>

		</tr>
	</table>
	<?php include 'inc.stats.php'; ?>
	</div>
<?php } elseif ($_GET['display'] == 'add') { ?>
	<?php 
	if($_GET['method'] == 'upload') 
	{
		include 'app.upload.php';
	}
	elseif ($_GET['method'] == 'link')
	{ 
		include 'app.link.php';
	}
	elseif ($_GET['method'] == 'youtube')
	{
		include 'app.youtube.php';
	}
	elseif ($_GET['method'] == NULL) 
	{ 
	?>
	
		<?php
		// $sr = new SuperRewardsAPI($config['sr']['key'], $config['sr']['secret']);
		// $sr->set_facebook($facebook);
		// $credit = $sr->users_getPoints($facebook->user);
		// $credit = $credit[0]['points'];
		// $check_ifexists = $db->Raw("SELECT COUNT(*) FROM `userdb_users` WHERE `user`='$user'");
		
		// if ($check_ifexists[0]['COUNT(*)'] == 0) $db->Raw("INSERT INTO `userdb_users` (`user`,`credit`,`override`) VALUES ('$user','$credit','0')");
		//	else $db->Raw("UPDATE `userdb_users` SET `credit`='$credit' WHERE `user`='$user'");
		?>
		
			<?php
			// checks how many credits the user has available
			// pulls it from the database and sets it to a variable
			// if it is a facebook page, it will also take the owner's available slots

			$credit = $db->Raw("SELECT `credit`,`override` FROM `userdb_users` WHERE `user`='$user'");
			$credit = $credit[0]['credit']+$credit[0]['override'];

			$usage = $db->Raw("SELECT COUNT(*) FROM `userdb_uploads` WHERE `user`='$user' AND `type`='upload'");
			$usage = $usage[0]['COUNT(*)'];

			if (isset($_GET['fb_page_id'])) 
			{
				$credit_of_owner = $db->Raw("SELECT `credit`,`override` FROM `userdb_users` WHERE `user`=$_POST[fb_sig_user]");
				$credit = $credit + $credit_of_owner[0]['credit'] + $credit_of_owner[0]['override'];

				$usage_of_owner = $db->Raw("SELECT COUNT(*) FROM `userdb_uploads` WHERE `user`='$_POST[fb_sig_user]'");
				$usage = $usage + $usage_of_owner[0]['COUNT(*)'];
			}
			else
			{
				$users_pages = $db->Raw("SELECT `fb_page_id` FROM `pages` WHERE `owner`=$user");

				foreach ($users_pages as $page_parse) 
				{
					$page_credit = $db->Raw("SELECT `credit`,`override` FROM `userdb_users` WHERE `user`='$page_parse[fb_page_id]'");
					$credit = $credit + $page_credit[0]['credit'] + $page_credit[0]['override'];

					$page_usage = $db->Raw("SELECT COUNT(*) FROM `userdb_uploads` WHERE `user`='$page_parse[fb_page_id]'");
					$usage = $usage + $page_usage[0]['COUNT(*)'];
				}
			}
			?>
			
			<div style="margin: 10px">
			<table border="0" width="100%" cellspacing="5px">
				<tr>
					<td width="70%">
						<table border="0" width="100%">
	
							<tr>
								<td>
									<table border="0" width="100%">
										<tr>
											<td width="20%">
												<img src="<?php echo $config['fb']['appcallbackurl']; ?>images/add_music.gif" align="top" border="0">
											</td>
											<td width="80%">
												<font size="1em">[<a href="<?php echo $config['fb']['fburl']; ?>?tab=help">need help?</a>]</font>
											</td>
										</tr>
									</table>
								</td>
							</tr>
							<tr>

								<td>
								
								<table border="0" width="100%">
									<tr>
										<td>
											<table border="0" cellpadding="0" cellspacing="1">
												<tr>
													<td>
														<font size="2em"><b>From your computer&nbsp;</b></font>
													</td>
													
													<td>
														<font size="2em">(mp3, m4a, flv supported; max 25MBs)</font>
													</td>
												</tr>
												
												<tr>
													<td>
													</td>
													
													<td>
														<font size="2em"><u><?php echo $credit+2; ?></u> total slots, <u><?php echo ($credit+2)-$usage; ?></u> available for use, <b><a href="<?php echo $config['fb']['fburl']; ?>?tab=offers">get more here</a></b></font>
													</td>
												</tr>
											</table>
													
											<?php 
											if ($_GET['error'] == 'file_format')
											{
												error('Not an Acceptable File','You did not give us a file that we accept, you must upload a MP3, M4A, MP4, or AAC audio file.');
											}
											elseif ($_GET['error'] == 'no_file')
											{
												error('Nothing Uploaded','We cannot continue unless you give us an audio file.');
											}
											?>
											
										</td>
									</tr>
									
									<tr>
										<td>
												
												<?php $check_temporary = $db->Raw("SELECT COUNT(*) FROM `userdb_temporary` WHERE `user`='$user'"); ?>
												<?php $check_temporary = $check_temporary[0]['COUNT(*)']; ?>
												<?php if ($check_temporary >= 1) { ?>
													<?php if(isset($_GET['fb_page_id'])) { error("Incomplete","Looks like you forgot to finish a previous addition, would you like to continue?<br /><a href='" .  $config['fb']['fburl'] . "?tab=index&display=add&method=upload&step=3&fb_page_id=" . $_GET['fb_page_id'] . "'>Yes, lets continue!</a> - <a href='" . $config['fb']['fburl'] . "?tab=index&display=add&method=upload&step=reset&fb_page_id=" . $_GET['fb_page_id'] . "'>No, scrap it.</a>"); } else { error("Incomplete","Looks like you forgot to finish, would you like to continue? <a href='" . $config['fb']['fburl'] . "?tab=index&display=add&method=upload&step=3'>Yes</a> - <a href='" .$config['fb']['fburl'] . "?tab=index&display=add&method=upload&step=reset'>No</a>"); } ?>
												<?php } elseif ($credit+2 <= $usage) { ?>
													<?php error('Not enough slots!','You need more slots to use this feature! <a href="' . $config['fb']['fburl'] . '?tab=offers">Click here to get some!</a>'); // I want this an image overlaying the actual upload system ?>
												<?php } else { ?>
														<form name="form1" enctype="multipart/form-data" method="post" action="<?php echo $config['fb']['appcallbackurl']; ?>?tab=index&display=add&method=upload&step=2<?php pages($_GET['fb_page_id']); ?>&X-Progress-ID=<?php echo md5($user); ?>">
															<table class="editorkit" border="0" cellspacing="0" style="width:425px">
																<tr class="width_setter">
																	<th style="width:75px"></th>
																	<td></td>
																	</tr><tr>
																	<th><label>File:</label></th>
																	<td class="editorkit_row">
																		<input name="upfile" type="file" size="23" style="color: #003366; font-family: Verdana; font-weight: normal; font-size:11px">
																	</td>
																	<td class="right_padding"></td>
																</tr>
																<tr>
																	<th></th>
																	<td class="editorkit_buttonset">
																		<input name='upload' type='submit' id='upload' class="editorkit_button action" value='Upload' clickthrough="true" />
																	</td>
																	<td class="right_padding">
																		
																	</td>
																</tr>
															</table>
															<div style="margin-left: 200px; margin-top: -40px;">
															<fb:iframe src="<?php echo $config['fb']['appcallbackurl']; ?>uploadprogress.php?id=<?php echo md5($user); ?>" width="250" height="45" frameborder="0" scrolling="no"></fb:iframe></div>
														</form>
												<?php } ?>
												
										</td>
									</tr>
								</table>
								
								<br />
								
								<table border="0" width="100%">
									<tr>
										<td>
											<font size="2em"><b>From online</b> (mp3, m4a, youtube supported)</font>
											
											<?php 
											if ($_GET['error'] == 'no_link_submitted')
											{
												error('Nothing Submitted','We cannot continue until you give us a link to a file on the web.');
											}
											elseif ($_GET['error'] == 'does_not_end_in_mp3')
											{
												error('Not an Audio File','You need to specify a link that leads to an audio file.');
											}
											elseif ($_GET['error'] == 'not_valid_link')
											{
												error('File Inexistant','The file you have specified does not exist, please check the link and try again!');
											}
											?>
											
										</td>
									</tr>
									
									<tr>
										<td>
											<fb:editor action="?tab=index&display=add&method=link&step=2<?php pages($_GET['fb_page_id']); ?>" labelwidth="0">
												<fb:editor-text label="Link" name="link" value="http://"/>
												<fb:editor-buttonset>
													<fb:editor-button value="Submit"/>
												</fb:editor-buttonset>
											</fb:editor>
										</td>
									</tr>
								</table>
								
								<br />
								
								<table border="0" width="100%">
									<tr>
										<td>
											<font size="2em"><b>Youtube Search</b> (keyword)</font>
											
											<?php 
											if ($_GET['error'] == 'empty')
											{
												error('Nothing Submitted','We cannot continue until you give us a link to a file on the web.');
											}
											?>
											
										</td>
									</tr>
									
									<tr>
										<td>
											<fb:editor action="?tab=index&display=add&method=youtube&search<?php pages($_GET['fb_page_id']); ?>" labelwidth="0">
												<fb:editor-text label="Search" name="search" value="song title - artist name"/>
												<fb:editor-buttonset>
													<fb:editor-button value="Submit"/>
												</fb:editor-buttonset>
											</fb:editor>
										</td>
									</tr>
								</table>
								
								<br />
								
								<td>
							</tr>
						</table>
					</td>

					<td width="30%">
						<div style="background-color: #fff5b1; border: 1px solid #ffd04d; padding: 10px; font-size: 16px; text-align: center;">Three ways of adding music to your player. Either give us the file  to host or give us a link (can be from youtube).<br /><br />Letting us host it (<i>from your computer</i> feature) requires a sufficient amount of slots. You can use the other features as much as you like.<br /><br />Fill in any of the feature's required data and press the appropriate button to continue.</div>
					</td>

				</tr>
			</table>
			</div>

	<?php } ?>
<?php } ?>