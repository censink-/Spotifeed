<?php
	require 'vendor/autoload.php';
	require 'includes/logger.php';
	require 'includes/db.php';

	$session = new SpotifyWebAPI\Session(
		'fb542e571c50404c935cf2a84536726d',
		'3497e052affa4971bffc006bd6ca96d7',
		'http://local.site/spotifeed/loggedin.php'
	);

	$api = new SpotifyWebAPI\SpotifyWebAPI();

	logger($user_ip . " visited /loggedin");

	if (isset($_GET['code']))
	{
		logger($user_ip . " visited /loggedin?code=" . $_GET['code']);

		$session->requestAccessToken($_GET['code']);
		$accessToken = $session->getAccessToken();

		logger($user_ip . " has access token '" . $accessToken . "'");
		setcookie('spotify', $accessToken, time()+3600);

		$api->setAccessToken( $accessToken );

		$me = $api->me();
		$user_id = $me->id;

		logger($user_ip . " logged in as " . $me->display_name . " with user_id " . $user_id);
		setcookie('user_id', $user_id, time()+3600);

		$login = mysqli_query($db, "INSERT INTO `users` VALUES (null, '" . $user_id . "', null, '" . $accessToken . "', null) ON DUPLICATE KEY UPDATE `last_use`=CURRENT_TIMESTAMP;");
		if (mysqli_error($db)){
			echo "DB ERROR";
			echo mysqli_error($db);
		}

		header('Location: ./');
	}