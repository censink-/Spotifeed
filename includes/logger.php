<?php
	$user_ip = $_SERVER['REMOTE_ADDR'];

	function logger($newcontent)
	{
		$file = 'log.txt';
		$oldcontent = file_get_contents( $file);
		$content = $oldcontent;
		$content .= '[' . date('d-m-Y @ H:i:s') . '] ' . $newcontent . "\n";
		file_put_contents($file, $content);
	}