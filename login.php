<?php
	require 'vendor/autoload.php';

	$session = new SpotifyWebAPI\Session(
		'fb542e571c50404c935cf2a84536726d',
		'3497e052affa4971bffc006bd6ca96d7',
		'http://local.site/spotifeed/loggedin.php'
	);

	$api = new SpotifyWebAPI\SpotifyWebAPI();

	if (isset($_GET['code'])) {
		$session->requestAccessToken($_GET['code']);
		$api->setAccessToken($session->getAccessToken());

		print_r(json_encode($api->me(), true));
	} else {
		$options = [
			'scope' => [
				'playlist-modify-private',
				'playlist-modify-public',
				'user-follow-read',
				'ugc-image-upload'
			],
			''
		];

		header('Location: ' . $session->getAuthorizeUrl($options));
		die();
	}