<?php

/**
 * Twitter bot for The Loop chat room.
 *
 * Set to run as a cron job every 15 minutes
 *
 * @author Shea Bunge <info@bungeshea.com>
 * @version 0.4
 */

/* Constants for Twitter app keys */
require __DIR__ . '/config.php';

/* Write log */
$log_file = 'loopbot.log';
$log = file_get_contents( $log_file );
$log .= print_r( $_POST, true ) . "\n\n";
file_put_contents( $log_file, $log );

/* Make sure the request is authorized */
if ( KEY !== @$_POST['key'] ) {
	die( 'Not authorized' );
}

/* Load Composer dependencies */
require __DIR__ . '/vendor/autoload.php';

/**
 * Retrieve the Twitter handle of a WPSE member
 * @param string $wpse_username The username on WPSE
 * @param string $at_symbol Value to prepend the username with
 * @param unknown $fallback The value to return if no Twitter handle was found
 * @return string|unknown  The Twitter handle if found, $fallback otherwise
 */
function wpse_twitter_handle( $wpse_username, $prepend = '@', $fallback = false ) {

	/* WPSE => Twitter */
	$twitter_handles = array(
		'Rarst'          => 'Rarst',
		'toscho'         => 'toscho',
		'kaiser'         => 'unserkaiser',
		'bungeshea'      => 'bungeshea',
		'bungeshea'      => 'bungeshea_',
		'Wyck'           => 'wycks_s',
		'Tom J Nowell'   => 'Tarendai',
		'ungestaltbar'   => 'ungestaltbar',
		'Sven'           => 'hofmannsven',
		'userabuser'     => 'PissOffHomeboy',
		'Stack Exchange' => 'StackWordPress',
		'Stephen Harris' => 'stephenharris88',
		'Chip Bennett'   => 'chip_bennett',
		'lkraav'         => 'lkraav',
		'GhostToast'     => '_ghosttoast',
		'Sisir'          => 'prionkor',
		'anu'            => 'anu',
		'mebu83'         => 'Mebu83',
	);

	if ( isset( $twitter_handles[ $wpse_username ] ) ) {
		return $prepend . $twitter_handles[ $wpse_username ];
	} else {
		return $fallback;
	}
}

/**
 * Set up Codebird
 */
\Codebird\Codebird::setConsumerKey( TWITTER_KEY, TWITTER_SECRET );
$cb = \Codebird\Codebird::getInstance();
$cb->setToken( TWITTER_TOKEN, TWITTER_TOKENSECRET );

/* Set up variables */
$text = strip_tags( $_POST[ 'title' ] );

/* Replace direct replies with @mentions */
if ( preg_match( '/^:(\d+) /', $text ) ) {

	/* Extract the real username from the description field */
	preg_match( "/^@([\w\d_\-']+) /", $_POST[ 'description' ], $matches );

	/* Try to convert wpse username to Twitter handle */
	$atname = wpse_twitter_handle( $matches[1], '', $matches[1] );

	/* Add an @ symbol and a space */
	$atname = '@' . $atname . ' ';

	/* Replace the user ID with the Twitter handle */
	$text = preg_replace( '/^:(\d+) /', $atname, $text );
}

/* Attempt to convert a WPSE username into a Twitter handle */
$author = wpse_twitter_handle( $_POST['author'], '@', $_POST['author'] );

/* Build tweet text */
$tweet = $text . ' #loopchat via ' .  $author;

/* Send the tweet to twitter */
$params = array(
	'status' => $tweet
);

$reply = $cb->statuses_update( $params );
