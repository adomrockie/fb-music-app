<?php
$pre = 'skip_fbapi';
include 'include/config.php';
include_once 'include/functions.php';
?>

	<form id="dummy_form_tab"></form>
	<div id="player_Tab" align="center">
	<img src="<?php echo $config['fb']['appcallbackurl']; ?>/images/spinner.gif" id="spinner_tab" style="display:none; padding-bottom: 5px;"/>
	</div>
	
	<?php $user = $_POST['fb_sig_profile_user']; ?>
	
	<center>
	<?php 
	$i=0;
	$db->Raw("SET NAMES utf8");
	$uploads = $db->Raw("SELECT `id`,`title`,`artist`,`count`,`link`,`buy_link`,`dl` FROM `userdb_uploads` WHERE `user`='$user' ORDER BY `id`");
	$uploads_count = count($uploads);
	$total_count = $uploads_count;
	?>
	<?php if($total_count == 0) { ?>
		error('This user does not have any songs!','');
	<?php } else { ?>
			<table border="0" width="100%" cellpadding="0" cellspacing="0">
			<?php $i=0; ?>
			<?php foreach($uploads as $display) { ?>
				<?php $i+=1; ?>
				<tr>
					<td>
						<center>

							<?php if ($uploads_count == $i) { ?>
								<div style="border-top: 1px solid #cccccc; border-bottom: 1px solid #cccccc; border-left: 1px solid #cccccc; border-right: 1px solid #cccccc; background-color: #F7F7F7; padding: 1px;">
							<?php } else { ?>
								<div style="border-top: 1px solid #cccccc; border-left: 1px solid #cccccc; border-right: 1px solid #cccccc; background-color: #F7F7F7; padding: 1px;">
							<?php } ?>

							<table border="0" width="100%">
								<tr>
									<td valign="center" width="3%">
										<div style="padding-right: 5px; padding-left: 5px;"><a clickrewriteurl="<?php echo $config['fb']['appcallbackurl']; ?>player.php?id=<?php echo $display['id']; ?>&from_tab=1" clickrewriteid="player_Tab" clickrewriteform="dummy_form_tab" clicktoshow="spinner_tab"><img src="<?php echo $config['fb']['appcallbackurl']; ?>images/track.gif" align="top" border="0"></a></div>
									</td>
									<td valign="center">
										<a clickrewriteurl="<?php echo $config['fb']['appcallbackurl']; ?>player.php?id=<?php echo $display['id']; ?>&from_tab=1" clickrewriteid="player_Tab" clickrewriteform="dummy_form_tab" clicktoshow="spinner_tab"><?php echo htmlspecialchars_decode(utf8_decode($display['title']), ENT_QUOTES); ?> by <?php echo htmlspecialchars_decode(utf8_decode($display['artist']), ENT_QUOTES); ?></a>
									</td>
									<td valign="center" style="text-align: right;">
										<?php if ($display['buy_link'] !== '') { ?>
											<a href="<?php echo $display['buy_link']; ?>" target="_blank">Buy Now</a>
										<?php } ?>
										
										<?php if ($display['dl'] == 1) { ?>
											<?php if ($display['buy_link'] !== '') echo ' - '; ?>
											<a href="<?php echo $config['fb']['appcallbackurl']; ?>download.php?id=<?php echo $display['id']; ?>" target="_blank">Download</a>
										<?php } ?>
										
										[<?php echo $display['count']; ?> plays]
										
									</td>
								</tr>
							</table>
						</div></center>
					</td>
				</tr>
			<?php } ?>
			
			</table>
			</center>
	<?php } ?>
	
	<?php 
	$commentBoxStatus = $db->Raw("SELECT `comment_box` FROM `userdb_users` WHERE `user`='$user'");
	if (isset($_POST['fb_sig_type']))
		$commentBoxStatus = $db->Raw("SELECT `comment_box` FROM `pages` WHERE `fb_page_id`='$user'");
	?>
	
	<?php if($commentBoxStatus[0]['comment_box'] == 1) { ?>
		<div style="border: 1px solid #cccccc; padding: 10px; margin-top: 10px;">
			<fb:comments xid="music_<?php echo $user ?>" candelete="<?php if ($user == $_POST['fb_sig_user'] || $_POST['fb_sig_is_admin']) echo 'true'; else echo 'false'; ?>" numposts="5"></fb:comments>
		</div>
	<?php } ?>
	
<?php $total_count = $db->Raw("SELECT COUNT(*) FROM `userdb_plays` WHERE `owner`='$_POST[fb_sig_profile_user]'"); ?>
<div style="margin-top: 10px; border-top: 1px solid #d8dfea; padding: 3px 16px; height: 14px; color: #3b5998;">
	<div style="float: left;"><?php echo $total_count[0]['COUNT(*)']; ?> total plays<fb:visible-to-owner> - <a href="<?php echo $config['fb']['fburl']; ?><?php if($_POST['fb_sig_is_admin']) echo '?fb_page_id=' . $_POST['fb_sig_profile_id'] . ''; ?>">edit player</a></fb:visible-to-owner></div>
	<div style="float: right;">Generated by the <a href="<?php echo $config['fb']['about_url']; ?>">Music Application</a></div>
</div>

<?php include_once 'inc.tracking.php'; ?>
