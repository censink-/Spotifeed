<?php
	require 'vendor/autoload.php';
	require 'includes/logger.php';

	$session = new SpotifyWebAPI\Session(
		'fb542e571c50404c935cf2a84536726d',
		'3497e052affa4971bffc006bd6ca96d7',
		'http://local.site/spotifeed/'
	);

	$api = new SpotifyWebAPI\SpotifyWebAPI();

	if (isset($_COOKIE['spotify'])) {

		$accessToken = $_COOKIE['spotify'];

		$api->setAccessToken($accessToken);
	}
	if (isset($_COOKIE['user_id']))
	{
		$user_id = $_COOKIE['user_id'];
		logger($user_id . ' visited index');
	}
	else
	{
		$user_ip = $_SERVER['REMOTE_ADDR'];
		logger($user_ip . ' visited index');
	}
?>
<html>
	<head>
		<link rel="apple-touch-icon" sizes="180x180" href="apple-touch-icon.png">
		<link rel="icon" type="image/png" sizes="32x32" href="favicon-32x32.png">
		<link rel="icon" type="image/png" sizes="16x16" href="favicon-16x16.png">
		<link rel="manifest" href="site.webmanifest">
		<link rel="mask-icon" href="safari-pinned-tab.svg" color="#1db954">
		<meta name="apple-mobile-web-app-title" content="Spotifeed">
		<meta name="application-name" content="Spotifeed">
		<meta name="msapplication-TileColor" content="#1db954">
		<meta name="theme-color" content="#ffffff">
		<link rel="stylesheet" href="assets/css/fonts.css">
		<link rel="stylesheet" href="assets/css/style.css">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Spotifeed</title>
	</head>
	<body>
		<a href="cookies.php" style="position: absolute;top:-2px;right:-2px;border-bottom:1px solid white;border-left:1px solid white;border-radius: 4px;padding:3px;text-decoration: none;">Clear cookies</a>
		<div id="box">
			<h1 class="normal">SpotiFeed</h1>
			<hr class="spacer">
			<div id="content">
			<p id="login-msg">To use SpotiFeed, you need to log in using your Spotify Account</p>
				<div id="steps" class="hidden">
					<div id="step-1" class="step completed">
						<span class="step-icon">1</span>
						<p class="step-description">Log in</p>
					</div>
					<div id="step-2" class="step active">
						<span class="step-icon">2</span>
						<a href="api.php?action=exist"><p class="step-description">Checking if playlist exists...</p></a>
						<p class="step-result">It does, we'll be updating it now</p>
					</div>
					<div id="step-3" class="step pending">
						<span class="step-icon">3</span>
						<a href="api.php?action=following"><p class="step-description">Getting followed artists</p></a>
						<p class="step-result">You're following a bunch of artists!</p>
					</div>
					<div id="step-4" class="step pending">
						<span class="step-icon">4</span>
						<a href="api.php?action=artist"><p class="step-description">Getting new tracks from followed artists</p></a>
						<p class="step-result">This might take a while..</p>
					</div>
					<div id="step-5" class="step pending">
						<span class="step-icon">5</span>
						<a href="api.php?action=update"><p class="step-description">Adding tracks to playlist</p></a>
						<p class="step-result">This might take a while..</p>
					</div>
					<div id="step-6" class="step pending">
						<span class="step-icon">6</span>
						<a href="api.php?action=info"><p class="step-description">Updating playlist description</p></a>
						<p class="step-result">All done!</p>
					</div>
				</div>
			</div>
			<hr class="spacer">
			<?php
			if (!isset($_COOKIE['spotify'])) {
			?>
			<a href="login.php" class="btn btn-green btn-block" id="login-btn">Log in</a>
			<?php } else {
				$me = $api->me();
				$name = $me->display_name;
				?>
			<a class="btn btn-black btn-block" id="welcome-btn">Welcome <?= $name ?></a>
			<?php } ?>
			<a href="privacy.php" id="privacy" target="_blank">Privacy</a>
		</div>
		<script type="text/javascript" src="assets/js/jquery.min.js"></script>
		<script type="text/javascript" src="assets/js/main.js"></script>
	</body>
</html>