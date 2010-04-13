<?php
include 'database.php';

$config = array();

########## Facebook Developer API Information ##########
# This information can be retrieved at the Facebook Developer
# website. In order to get this information, you must create
# your own application which will create the API information.
############################################################
$config['fb']['key'] = 'cd92203d073c3556a00ca2167c661421';
$config['fb']['secret'] = 'd0f1a9f7a0a85aa51badb50110e7bfd8';
$config['fb']['appcallbackurl'] = 'http://wdc01.web.burst-dev.com/music/';
$config['fb']['fburl'] = 'http://apps.facebook.com/stevenlu/';
$config['fb']['about_url'] = 'http://www.facebook.com/apps/application.php?id=2436915755';

########## Server Information ##########
# This should be a reflection of your 
# server infomation, its as simple as that.
########################################
$config['server']['internal_url'] = '/var/www/domains/wdc01.web.burst-dev.com/html/music/';
$config['server']['secret'] = "theqa3ExUs92f4uNADrebR5sTusWadREJa5AP3U4AZ6fERA7aQaTaheFU7asufru";
$config['server']['streaming'] = "http://wdc01.stream.burst-dev.com";
$config['server']['uri_prefix'] = "/stream/";

########## Paypal Instant Payment Notification ##########
# Instantly inputs all data into the database;
# instant payment processing.
############################################################
$config['pp']['pay_to'] = 'slu@burst-dev.com';
	
########## Database Information ##########
# The MySQL database information needed
# to store user information.
########################################
$config['db']['host'] = "localhost";
$config['db']['username'] = 'music';
$config['db']['password'] = 'KTPtd2dX4Zh4XMw7';
$config['db']['table'] = 'music';	

########## DO NOT TOUCH ANYTHING BELOW HERE ##########
# Simply logs into Facebook, the MySQL database
# and sees if the Facebook user is an administrator.
##################################################

if ($pre !== 'skip_fbapi') {
	$facebook = new Facebook($config['fb']['key'], $config['fb']['secret']);
	if($pre !== 'skip_login')
	{
		if (isset($fb_page_id)) 
		{
			if ($_POST['fb_sig_is_admin'] == '1') 
				$user = $fb_page_id;
		} 
		else
		{	
			$user = $facebook->require_login();
			if ($user == NULL OR $user == 0) {
				$user = $_POST['fb_sig_user'];
			}
		}
	}
}

$db = new BurstMySQL ($config['db']['host'], $config['db']['username'], $config['db']['password'], $config['db']['table']);	

?>