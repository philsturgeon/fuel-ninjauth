<?php
/**
 * Configuration for NinjAuth
 */
return array(
	
	'urls' => array(
		'registration' => 'auth/register',
		'login' => 'auth/login',
		'callback' => 'auth/callback',
		
		'registered' => 'auth/account',
		'logged_in' => 'auth/account',
	),
	/**
	* Linking
	*
	* This allows for linking of existing accounts to providers. 
	* If set to true, calling the login url whilst a user is logged in will cause the account to be linked 
	* with the logged in user rather than trying to make a new account.
	*/
	'link' => false,
	
	/**
	 * Providers
	 * 
	 * Providers such as Facebook, Twitter, etc all use different Strategies such as oAuth, oAuth2, etc.
	 * oAuth takes a key and a secret, oAuth2 takes a (client) id and a secret, optionally a scope.
	 */
	'providers' => array(
		
		'facebook' => array(
			'id' => '',
			'secret' => '',
			'scope' => '',
		),
		
		'twitter' => array(
			'key' => '',
			'secret' => '',
		),

		'dropbox' => array(
			'key' => '',
			'secret' => '',
		),

		'linkedin' => array(
			'key' => '',
			'secret' => '',
		),

		'flickr' => array(
			'key' => '',
			'secret' => '',
		),

		'youtube' => array(
			'key' => '',
			'scope' => 'http://gdata.youtube.com',
		),
	
	),
);
