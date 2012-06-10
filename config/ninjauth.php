<?php
/**
 * Configuration for NinjAuth
 */
return array(

	/**
	 * Adapter
	 * 
	 * NinjAuth can use different adapters, so it will work with 'auth', 'sentry' or 'warden'.
	 */
	'adapter' => 'SimpleAuth',

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
			'scope' => array('email', 'offline_access'),
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

		'openid' => array (
			'identifier_form_name' => 'openid_identifier',
			'ax_required' => array('contact/email', 'namePerson/first', 'namePerson/last'),
			'ax_optional' => array('namePerson/friendly', 'birthDate', 'person/gender', 'contact/country/home'),
		),

	),

	/**
	 * link_multiple_providers
	 * 
	 * Can multiple providers be attached to one user account
	 */
	'link_multiple_providers' => true,

	/**
	 * default_group
	 * 
	 * How should users be signed up
	 */
	'default_group' => 1,
);
