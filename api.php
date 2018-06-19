<?php
	error_reporting( 1 );

	require 'vendor/autoload.php';
	require 'includes/db.php';
	require 'includes/logger.php';

	$session = new SpotifyWebAPI\Session(
		'fb542e571c50404c935cf2a84536726d',
		'3497e052affa4971bffc006bd6ca96d7  ',
		'http://local.site/spotifeed/'
	);

	$api = new SpotifyWebAPI\SpotifyWebAPI();

	$result = [];

	if( isset( $_COOKIE['spotify'] ) )
	{
		$accessToken = $_COOKIE['spotify'];

		$api->setAccessToken( $accessToken );

		$action = $_GET['action'];

		logger( "API action: " . $action );

		$user_id = $_COOKIE['user_id'];

		$result['action'] = $action;
		$result['user']   = $user_id;
		$result['code']   = 204; //OK, but no content

		switch( $action )
		{
			case "exist": //Does the spotifeed playlist exist
				$result['step'] = 2;
				session_start();

				$result['playlist'] = null;

				$gotplaylistq = mysqli_query( $db, "SELECT last_use,playlist FROM users WHERE user_id = '" . $user_id . "';" );
				if( mysqli_error( $db ) )
				{
					$result['error'] = mysqli_error( $db );
					$result['code']  = 416;
				}
				$playlistq = mysqli_fetch_array( $gotplaylistq );

				$date               = $playlistq[0];
				$playlist           = $playlistq[1];
				$result['playlist'] = $playlist;
				$result['code']     = 200;

				$_SESSION['playlist'] = $playlist;

				$exists = 0;

				if( $playlist != null )
				{
					$playlistscheck = $api->getMyPlaylists( [ 'limit' => 50 ] );
					foreach( $playlistscheck->items as $playlistcheck )
					{
						if( $playlistcheck->id == $playlist )
						{
							$exists = 1;
						}
					}

				}

				if( !$exists )
				{
					$result['code'] = 201;

					$playlist           = $api->createUserPlaylist( $user_id, [
						'name'        => 'Spotifeed',
						'description' => 'Releases from the artists you follow, chronologically sorted. Last update: ' . date( "d-m-Y" )
					] );
					$result['playlist'] = $playlist->id;

					$spotifeed_image = file_get_contents( "assets/img/logo-512.jpg" );
					$spotifeed_image = base64_encode( $spotifeed_image );

					$api->updateUserPlaylistImage( $user_id, $playlist->id, $spotifeed_image );

					mysqli_query( $db, "UPDATE users SET playlist = '" . $playlist->id . "' WHERE user_id = '" . $user_id . "';" );
					if( mysqli_error( $db ) )
					{
						$result['code']  = 503;
						$result['error'] = mysqli_error( $db );
					}

					$_SESSION['date']     = date( strtotime( "-3 Months" ) );
					$_SESSION['playlist'] = $playlist->id;
				}
				else
				{
					$_SESSION['date'] = $date; //UNCOMMENT WHEN DONE
				}
				$_SESSION['date'] = date( strtotime( "-3 Months" ) );

				$result['date'] = $_SESSION['date'];

				break;
			case "following": //See who is followed
				$result['step'] = 3;
				session_start();

				$result['artists'] = null;
				$result['count']   = 0;

				$api_artists = $api->getUserFollowedArtists( [ 'type' => 'artist', 'limit' => 50 ] );
				$api_artists = $api_artists->artists;

				$artist_items    = $api_artists->items;
				$result['count'] = $api_artists->total;

				if( $api_artists->total > 50 )
				{
					$i = 50;
					while( $api_artists->total > $i )
					{
						$last = end( $artist_items )->id;

						$api_artists = $api->getUserFollowedArtists( [ 'type' => 'artist', 'after' => $last, 'limit' => 50 ] );
						$api_artists = $api_artists->artists;

						$artist_items = array_merge( $artist_items, $api_artists->items );
						$i            += 50;
					}
				}

				$artists = [];
				foreach( $artist_items as $artist )
				{
					$artists[] = $artist->id;
				}

				$result['artists'] = $artists;

				$_SESSION['artists'] = $artists;
				/*$artists_list        = "(null, '" . $user_id . "', '" . implode( "'),(null, '" . $user_id . "', '", $artists ) . "')";

				if( $api_artists->total > 0 )
				{
					$result['code'] = 200;
					$dartists       = mysqli_query( $db, "DELETE FROM follows WHERE user_id = '" . $user_id . "';" );
					$qartists       = mysqli_query( $db, "INSERT INTO follows VALUES " . $artists_list . ";" );

					if( mysqli_error( $db ) )
					{
						$result['code']  = 503;
						$result['error'] = "DB Error: " . mysqli_error( $db );
					}
				}*/

				break;
			case "tracks": //Get new releases
				$result['step'] = 4;
				session_start();

				$artistnum = 0;
				if( isset( $_GET['artist'] ) )
				{
					$artistnum = $_GET['artist'] - 1;
				}
				else
				{
					$_SESSION['albums'] = null;
					$_SESSION['tracks'] = null;
				}

				$artists   = $_SESSION['artists'];
				$datesince = strtotime( $_SESSION['date'] );
				if( $_SESSION['date'] != null )
				{
					$datesince = strtotime( '-3 Months' );
				}
				//$datesince       = strtotime( '-3 Months' );
				$artist     = $artists[$artistnum];
				$all_albums = [];
				$all_tracks = [];
				if( $_SESSION['albums'] != null )
				{
					$all_albums = $_SESSION['albums'];
				}
				if( $_SESSION['tracks'] != null )
				{
					$all_tracks = $_SESSION['tracks'];
				}

				$albums_raw = $api->getArtistAlbums( $artist, [ 'include_groups' => 'album,single' ] );
				foreach( $albums_raw->items as $album )
				{
					$album_date      = strtotime( $album->release_date );
					$album_date_prec = $album->release_date_precision;

					if( $album_date_prec == "day" && $album_date > $datesince )
					{
						$currentalbum['id']   = $album->id;
						$currentalbum['date'] = strtotime( $album_date );

						array_push( $all_albums, $currentalbum );

						$tracks_raw = $api->getAlbumTracks( $album->id, [ 'limit' => 50 ] );
						foreach( $tracks_raw->items as $track )
						{
							$currenttrack['id']   = $track->id;
							$currenttrack['date'] = date( 'd-m-Y', $album_date );

							array_push( $all_tracks, $currenttrack );
						}
					}
				}

				function cmp( $a, $b )
				{
					return strcmp( -strtotime( $a['date'] ), -strtotime( $b['date'] ) );
				}

				usort( $all_tracks, 'cmp' );
				$all_tracks = array_reverse( $all_tracks );

				$_SESSION['albums'] = $all_albums;
				$_SESSION['tracks'] = $all_tracks;
				$result['dateraw']  = $datesince;
				$result['date']     = strtotime( $datesince );

				$result['albums']    = $all_albums;
				$result['tracks']    = $all_tracks;
				$result['artists']   = $artists;
				$result['artist']    = $artist;
				$result['artistnum'] = $artistnum + 1;
				$result['artistsum'] = count( $artists );
				$result['albumsum']  = count( $all_albums );

				break;
			case "update": //Add releases to spotifeed playlist
				$result['step'] = 5;
				session_start();

				$part = 1;
				if( isset( $_GET['part'] ) )
				{
					$part = $_GET['part'];
				}

				$tracks            = $_SESSION['tracks'];
				$track_chunks      = array_chunk( $tracks, 100 );
				$result['songsum'] = count( $tracks );

				$result['songnum'] = ( ( $part - 1 ) * 100 ) + count( $track_chunks[$part - 1] );

				$result['part'] = $part;

				$currenttracks    = $track_chunks[$part - 1];
				$addcurrenttracks = [];
				foreach( $currenttracks as $currenttrack )
				{
					array_push( $addcurrenttracks, $currenttrack['id'] );
				}

				$currentPlaylistTracks = $api->getUserPlaylistTracks($user_id, $_SESSION['playlist']);
				$result['currentplaylist'] = $currentPlaylistTracks;

				$pos = ($part - 1) * 100;
				$api->addUserPlaylistTracks( $user_id, $_SESSION['playlist'], $addcurrenttracks, ['position' => $pos]);
				break;
			case "info": //Set info about spotifeed playlist (last update & other stats)
				$result['step'] = 6;
				session_start();

				$result['session']  = $_SESSION;
				$result['playlist'] = $_SESSION['playlist'];

				//unset( $_SESSION );
				//session_destroy();

				//$result['destroyed'] = true;
				break;
			default: //Errors
				$result['action'] = "unknown";
				$result['code']   = 404;
				$result['status'] = "Action not found";
				break;
		}
	}
	else
	{
		$result['code']   = 401; //Not logged in
		$result['status'] = 'Not logged in';

		logger( "API request without access_token!" );
	}

	$json = json_encode( $result, true );

	echo $json;