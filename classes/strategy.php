<?php

namespace NinjAuth;

/**
 * NinjAuth Strategy
 *
 * @package    FuelPHP/NinjAuth
 * @category   Stategies
 * @author     Phil Sturgeon
 * @copyright  (c) 2012 HappyNinjas Ltd
 * @license    http://philsturgeon.co.uk/code/dbad-license
 */

abstract class Strategy
{
	/**
	 * @var  string  Strategy name
	 */
	public $name;
	
	protected static $providers = array(
		'facebook' => 'OAuth2',
		'twitter' => 'OAuth',
		'blooie' => 'OAuth2',
		'dropbox' => 'OAuth',
		'flickr' => 'OAuth',
		'google' => 'OAuth2',
		'github' => 'OAuth2',
		'linkedin' => 'OAuth',
		'paypal' => 'OAuth2',
		'openid' => 'OpenId',
		'soundcloud' => 'OAuth2',
		'windowslive' => 'OAuth2',
	);
	
	public function __construct($provider)
	{
		$this->provider = $provider;
		
		// Take config from the ninjauth.php
		$this->config = \Config::get("ninjauth.providers.{$provider}");

		// Adapters interact with user systems and whatnot
		$this->adapter = Adapter::forge(\Config::get('ninjauth.adapter'));
		
		if ($this->config === null)
		{
			throw new Exception(sprintf('Provider "%s" has no config.', $provider));
		}
		
		if ( ! $this->name)
		{
			// Attempt to guess the name from the class name
			$this->name = strtolower(str_replace('NinjAuth\Strategy_', '', get_class($this)));
		}
	}
	
	public static function forge($provider)
	{
		// If a strategy has been specified use it, otherwise look it up
		$strategy = \Config::get("ninjauth.providers.{$provider}.strategy") ?: \Arr::get(static::$providers, $provider);
		
		if (is_null($strategy))
		{
			throw new Exception(sprintf('Provider "%s" has no strategy.', $provider));
		}
		
		$class = "NinjAuth\\Strategy_{$strategy}";
		
		return new $class($provider);
	}
	
	public static function login_or_register($strategy)
	{
		$token = $strategy->callback();
		
		switch ($strategy->name)
		{
		 	case 'oauth':
				$user_hash = $strategy->provider->get_user_info($strategy->consumer, $token);
			break;

			case 'oauth2':
				$user_hash = $strategy->provider->get_user_info($token);
			break;

			case 'openid':
				$user_hash = $strategy->get_user_info($token);
			break;

			default:
				throw new Exception("Unsupported Strategy: {$strategy->name}");
		}
		
		// If there is no uid we don't know who this is
		if (empty($user_hash['uid']))
		{
			throw new Exception('No uid in response from the provider, meaning we have no idea who you are.');
		}

		// UID and logged in? Just attach this authentication to a user
		if ($strategy->adapter->is_logged_in())
		{
			$user_id = $strategy->adapter->get_user_id();
			
			$num_linked = Model_Authentication::count_by_user_id($user_id);
		
			// Allowed multiple providers, or not authed yet?
			if ($num_linked === 0 or \Config::get('ninjauth.link_multiple_providers') === true)
			{
				// Attach this account to the logged in user
				Model_Authentication::forge(array(
					'user_id' 		=> $user_id,
					'provider' 		=> $strategy->provider->name,
					'uid' 			=> $user_hash['uid'],
					'access_token' 	=> isset($token->access_token) ? $token->access_token : null,
					'secret' 		=> isset($token->secret) ? $token->secret : null,
					'expires' 		=> isset($token->expires) ? $token->expires : null,
					'refresh_token' => isset($token->refresh_token) ? $token->refresh_token : null,
					'created_at' 	=> time(),
				))->save();

				// Attachment went ok so we'll redirect
				\Response::redirect(\Config::get('ninjauth.urls.logged_in'));
			}
			
			else
			{
				$auth = Model_Authentication::find_by_user_id($user_id);
				throw new Exception(sprintf('This user is already linked to "%s".', $auth->provider));
			}
		}
		
		// The user exists, so send him on his merry way as a user
		else if ($authentication = Model_Authentication::find_by_uid($user_hash['uid']))
		{
			// Force a login with this username
			if ($strategy->adapter->force_login((int) $authentication->user_id))
			{
			    // credentials ok, go right in
			    \Response::redirect(\Config::get('ninjauth.urls.logged_in'));
			}
		}
		
		// Not an existing user of any type, so we need to create a user somehow
		else
		{
			// Did the provider return enough information to log the user in?
			if ($strategy->adapter->can_auto_login($user_hash))
			{
				// Make a user with what we have (password is made for them)
				$user_id = $strategy->adapter->create_user($user_hash);

				// Attach this authentication to the new user
				$saved = Model_Authentication::forge(array(
					'user_id' 		=> $user_id,
					'provider' 		=> $strategy->provider->name,
					'uid' 			=> $user_hash['uid'],
					'access_token' 	=> isset($token->access_token) ? $token->access_token : null,
					'secret' 		=> isset($token->secret) ? $token->secret : null,
					'expires' 		=> isset($token->expires) ? $token->expires : null,
					'refresh_token' => isset($token->refresh_token) ? $token->refresh_token : null,
					'created_at' 	=> time(),
				))->save();

				// Force a login with this users id
				if ($saved and $strategy->adapter->force_login($user_id))
				{
				    // credentials ok, go right in
				    \Response::redirect(\Config::get('ninjauth.urls.logged_in'));
				}

				exit('We tried automatically creating a user but that just really did not work. Not sure why...');
			}

			// They aren't a user and cant be automatically registerd, so redirect to registration page
			else
			{
				\Session::set('ninjauth', array(
					'user' => $user_hash,
					'authentication' => array(
						'provider' 		=> $strategy->provider->name,
						'uid' 			=> $user_hash['uid'],
						'access_token' 	=> isset($token->access_token) ? $token->access_token : null,
						'secret' 		=> isset($token->secret) ? $token->secret : null,
						'expires' 		=> isset($token->expires) ? $token->expires : null,
						'refresh_token' => isset($token->refresh_token) ? $token->refresh_token : null,
					),
				));

				\Response::redirect(\Config::get('ninjauth.urls.registration'));
			}
		}
	}

	abstract public function authenticate();
}
