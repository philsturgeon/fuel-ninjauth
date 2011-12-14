<?php

namespace NinjAuth;

abstract class Strategy {
	
	/**
	 * @var  string  Strategy name
	 */
	public $name;
	
	protected static $providers = array(
		'facebook' => 'OAuth2',
		'twitter' => 'OAuth',
		'dropbox' => 'OAuth',
		'flickr' => 'OAuth',
		'google' => 'OAuth2',
		'github' => 'OAuth2',
		'linkedin' => 'OAuth',
		'openid' => 'OpenId',
		'unmagnify' => 'OAuth2',
		'windowslive' => 'OAuth2',
		'youtube' => 'OAuth2',
	);
	
	public function __construct($provider)
	{
		$this->provider = $provider;
		
		$this->config = \Config::get("ninjauth.providers.{$provider}");
		
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
		
		if (\Auth::check())
		{
			list($driver, $user_id) = \Auth::instance()->get_user_id();
			
			$num_linked = Model_Authentication::count_by_user_id($user_id);
		
			// Allowed multiple providers, or not authed yet?
			if ($num_linked === 0 or \Config::get('ninjauth.link_multiple_providers') === true)
			{
				// If there is no uid we can't remember who this is
				if ( ! isset($user_hash['uid']))
				{
					throw new Exception('No uid in response.');
				}
				
				// Attach this account to the logged in user
				Model_Authentication::forge(array(
					'user_id' 		=> $user_id,
					'provider' 		=> $this->provider,
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
			if (\Auth::instance()->force_login($authentication->user_id))
			{
			    // credentials ok, go right in
			    \Response::redirect(\Config::get('ninjauth.urls.logged_in'));
			}
		}
		
		// They aren't a user, so redirect to registration page
		else
		{	
			\Session::set('ninjauth', $user_hash);

			\Response::redirect(\Config::get('ninjauth.urls.registration'));
		}
	}

	abstract public function authenticate();
}
