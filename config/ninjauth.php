<?php
/**
 * Configuration for NinjAuth
 */
return array(
	
	'urls' => array(
		'registration' => 'auth/register',
		'login' => 'auth/login',
		
		'registered' => 'auth/account',
		'logged_in' => 'auth/account',
	),

	/**
	 * Providers
	 * 
	 * Providers such as Facebook, Twitter, etc all use different Strategies such as oAuth, oAuth2, etc.
	 * oAuth takes a key and a secret, oAuth2 takes a (client) id and a secret, optionally a scope.
	 */
	'providers' => array(
		
		'facebook' => array(
			'id' => '192553490816292',
			'secret' => 'fdd9b8331d0ebec2c939e3dd6bb4e7ec',
		),
		
		'twitter' => array(
			'key' => 'rTEn9REHxgE4OOrAva8mw',
			'secret' => 'FpQCqRQlZKNOYtcFMYI9RMcpeKF1p8AKNKcz9VL56E',
		),

		'dropbox' => array(
			'key' => 'vvvq4goeycjzfgm',
			'secret' => 'yi2q4ce0adbcv2v',
		),

		'linkedin' => array(
			'key' => '8bpcipr0v68e',
			'secret' => 'Ba2MxtNMap7CywKB',
		),

		'flickr' => array(
			'key' => '68afd60a06c12657c324ea83d4c554eb',
			'secret' => '09dbb56a3d238876',
		),

		'youtube' => array(
			'key' => 'AI39si5U6RpTiDC9l5QDV1Xq9uAYph0PkyXBjDVCf_K4X5nRNMgVMP9d1OTlgDT9KXE-2Qce5AS5UTsdw2SnC0Qvoaqus49QLw',
			'scope' => 'http://gdata.youtube.com',
		),
	
	),
);