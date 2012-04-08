<?php

namespace NinjAuth;

use Arr;
use Config;
use Session;

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
		'blooie' 		=> 'OAuth2',
		'dropbox' 		=> 'OAuth',
		'facebook' 		=> 'OAuth2',
		'foursquare' 	=> 'OAuth2',
		'flickr' 		=> 'OAuth',
		'google' 		=> 'OAuth2',
		'github' 		=> 'OAuth2',
		'linkedin' 		=> 'OAuth',
		'openid' 		=> 'OpenId',
		'paypal' 		=> 'OAuth2',
		'soundcloud' 	=> 'OAuth2',
		'twitter' 		=> 'OAuth',
		'windowslive' 	=> 'OAuth2',
	);
	
	public function __construct($provider)
	{
		$this->provider = $provider;
		
		// Take config from the ninjauth.php
		$this->config = Config::get("ninjauth.providers.{$provider}");

		// Adapters interact with user systems and whatnot
		$this->adapter = Adapter::forge(Config::get('ninjauth.adapter'));
		
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
		if ( ! ($strategy = Config::get("ninjauth.providers.{$provider}.strategy")))
		{
			if (isset(static::$providers[$provider]))
			{
				$strategy = static::$providers[$provider];
			}
			else
			{
				throw new Exception(sprintf('Provider "%s" has no strategy.', $provider));
			}
		}
		
		$class = "NinjAuth\\Strategy_{$strategy}";
		
		return new $class($provider);
	}
	
	public function login_or_register()
	{
		$token = $this->callback();
		
		switch ($this->name)
		{
		 	case 'oauth':
				$user_hash = $this->provider->get_user_info($this->consumer, $token);
			break;

			case 'oauth2':
				$user_hash = $this->provider->get_user_info($token);
			break;

			case 'openid':
				$user_hash = $this->get_user_info($token);
			break;

			default:
				throw new Exception("Unsupported Strategy: {$this->name}");
		}
		
		// If there is no uid we don't know who this is
		if (empty($user_hash['uid']))
		{
			throw new Exception('No uid in response from the provider, meaning we have no idea who you are.');
		}

		// UID and logged in? Just attach this authentication to a user
		if ($this->adapter->is_logged_in())
		{
			$user_id = $this->adapter->get_user_id();

			$num_linked = count(Model_Authentication::find_one_by_user_id($user_id));
		
			// Allowed multiple providers, or not authed yet?
			if ($num_linked === 0 or Config::get('ninjauth.link_multiple_providers') === true)
			{
				// Attach this account to the logged in user
				Model_Authentication::forge(array(
					'user_id' 		=> $user_id,
					'provider' 		=> $this->provider->name,
					'uid' 			=> $user_hash['uid'],
					'access_token' 	=> isset($token->access_token) ? $token->access_token : null,
					'secret' 		=> isset($token->secret) ? $token->secret : null,
					'expires' 		=> isset($token->expires) ? $token->expires : null,
					'refresh_token' => isset($token->refresh_token) ? $token->refresh_token : null,
					'created_at' 	=> time(),
				))->save();

				// Attachment went ok so we'll redirect
				return 'linked';
			}
			
			else
			{
				$auth = Model_Authentication::find_one_by_user_id($user_id);
				throw new Exception(sprintf('This user is already linked to "%s".', $auth->provider));
			}
		}
		
		// The user exists, so send him on his merry way as a user
		elseif (($authentication = Model_Authentication::find_one_by_uid($user_hash['uid'])))
		{
			// Force a login with this username
			if ($this->adapter->force_login((int) $authentication->user_id))
			{
			    // credentials ok, go right in
			    return 'logged_in';
			}

			throw new Exception('Force login failed');
		}
		
		// Not an existing user of any type, so we need to create a user somehow
		else
		{
			// Did the provider return enough information to log the user in?
			if ($this->adapter->can_auto_login($user_hash))
			{
				// Make a user with what we have (password is made for them)
				$user_id = $this->adapter->create_user($user_hash);

				// Attach this authentication to the new user
				$saved = Model_Authentication::forge(array(
					'user_id' 		=> $user_id,
					'provider' 		=> $this->provider->name,
					'uid' 			=> $user_hash['uid'],
					'access_token' 	=> isset($token->access_token) ? $token->access_token : null,
					'secret' 		=> isset($token->secret) ? $token->secret : null,
					'expires' 		=> isset($token->expires) ? $token->expires : null,
					'refresh_token' => isset($token->refresh_token) ? $token->refresh_token : null,
					'created_at' 	=> time(),
				))->save();

				// Force a login with this users id
				if ($saved and $this->adapter->force_login((int) $user_id))
				{
				    // credentials ok, go right in
				    return 'registered';
				}

				exit('We tried automatically creating a user but that just really did not work. Not sure why...');
			}

			// They aren't a user and cant be automatically registerd, so redirect to registration page
			else
			{
				Session::set('ninjauth', array(
					'user' => $user_hash,
					'authentication' => array(
						'provider' 		=> $this->provider->name,
						'uid' 			=> $user_hash['uid'],
						'access_token' 	=> isset($token->access_token) ? $token->access_token : null,
						'secret' 		=> isset($token->secret) ? $token->secret : null,
						'expires' 		=> isset($token->expires) ? $token->expires : null,
						'refresh_token' => isset($token->refresh_token) ? $token->refresh_token : null,
					),
				));

				return 'register';
			}
		}
	}

	abstract public function authenticate();
}
