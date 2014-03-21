<?php

/**
 * Twitter bot for The Loop chat room.
 *
 * Set to run as a cron job every 15 minutes
 *
 * @author Shea Bunge <info@bungeshea.com>
 * @version 0.3
 */

/* Constants for Twitter app keys */
require __DIR__ . '/config.php';

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
		'Wyck'           => 'wycks_s',
		'Tom J Nowell'   => 'Tarendai',
		'ungestaltbar'   => 'ungestaltbar',
		'Sven'           => 'hofmannsven',
		'userabuser'     => 'PissOffHomeboy',
		'Stack Exchange' => 'StackWordPress',
		'Stephen Harris' => 'stephenharris88',
		'Chip Bennett'   => 'chip_bennett',
		'lkraav'         => 'lkraav',
		'GhostToast'    => '_ghosttoast',
	);

	if ( isset( $twitter_handles[ $wpse_username ] ) ) {
		return $prepend . $twitter_handles[ $wpse_username ];
	} else {
		return $fallback;
	}
}

/**
 * Get the latest starred items from The Loop
 * @return array
 */
function loopchat_get_stars() {
	$xml = new SimpleXMLElement( 'http://chat.stackexchange.com/feeds/rooms/starred/6', null, true );
	$items = array();

	foreach ( $xml->entry as $item ) {
		$items[] = (object) array(
			'text' => (string) $item->title,
			'link' => (string) $item->link[ 'href' ],
			'author' => (string) $item->author->name,
			'published' => $item->published
		);
	}

	return $items;
}

/**
 * Set up Codebird
 */
\Codebird\Codebird::setConsumerKey( TWITTER_KEY, TWITTER_SECRET );
$cb = \Codebird\Codebird::getInstance();
$cb->setToken( TWITTER_TOKEN, TWITTER_TOKENSECRET );

/**
 * Run the bot
 */
foreach ( loopchat_get_stars() as $star ) {

	/* Make sure that the tweet is fresh */
	if ( ( time() - (60*15) ) > strtotime( $star->published ) ) {
		break;
	}

	/* Attempt to convert a WPSE username into a Twitter handle */
	$author = wpse_twitter_handle( $star->author, '@', $star->author );

	/* Build tweet text */
	$tweet = $star->text . ' #loopchat via ' .  $author;

	/* Send the tweet to twitter */
	$params = array(
		'status' => $tweet
	);

	$tweet = $cb->statuses_update( $params );
}
