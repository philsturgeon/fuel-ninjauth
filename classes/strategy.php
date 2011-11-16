<?php

namespace NinjAuth;

abstract class Strategy {
	
	public $name;
	
	protected static $providers = array(
		'facebook' => 'OAuth2',
		'twitter' => 'OAuth',
		'dropbox' => 'OAuth',
		'flickr' => 'OAuth',
		'google' => 'OAuth2',
		'github' => 'OAuth2',
		'linkedin' => 'OAuth',
		'unmagnify' => 'OAuth2',
		'youtube' => 'OAuth',
	);
	
	public function __construct($provider)
	{
		$this->provider = $provider;
		
		$this->config = \Config::get("ninjauth.providers.{$provider}");
		
		if ( ! $this->name)
		{
			// Attempt to guess the name from the class name
			$this->name = strtolower(str_replace('NinjAuth\Strategy_', '', get_class($this)));
		}
	}
	
	public static function factory($provider)
	{
		// If a strategy has been specified use it, otherwise look it up
		$strategy = \Config::get("ninjauth.providers.{$provider}.strategy") ?: \Arr::get(static::$providers, $provider);
		
		if ( ! $strategy)
		{
			throw new Exception(sprintf('Provider "%s" has no strategy.', $provider));
		}
		
		$class = "NinjAuth\\Strategy_{$strategy}";
		return new $class($provider);
	}
	
	public static function login_or_register($strategy)
	{
		$response = $strategy->callback();
		
		if (\Auth::check())
		{
			$user_id = end(\Auth::instance()->get_user_id());
			
			$num_linked = Model_Authentication::count_by_user_id($user_id);
		
			// Allowed multiple providers, or not authed yet?
			if ($num_linked === 0 or \Config::get('ninjauth.link_multiple_providers') === true)
			{
				switch ($strategy->name)
				{
				 	case 'oauth':
						$user_hash = $strategy->provider->get_user_info($strategy->consumer, $response);
					break;

					case 'oauth2':
						$user_hash = $strategy->provider->get_user_info($response->token);
					break;
				}
				
				// Attach this account to the logged in user
				Model_Authentication::forge(array(
					'user_id' => $user_id,
					'provider' => $user_hash['credentials']['provider'],
					'uid' => $user_hash['credentials']['uid'],
					'token' => $user_hash['credentials']['token'],
					'secret' => $user_hash['credentials']['secret'],
					'created_at' => time(),
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
		else if ($authentication = Model_Authentication::find_by_token_and_secret($response->token, $response->secret))
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
			switch ($strategy->name)
			{
			 	case 'oauth':
					$user_hash = $strategy->provider->get_user_info($strategy->consumer, $response);
				break;
				
				case 'oauth2':
					$user_hash = $strategy->provider->get_user_info($response->token);
				break;
				
				default:
					exit('Ummm....');
			}
			
			\Session::set('ninjauth', $user_hash);

			\Response::redirect(\Config::get('ninjauth.urls.registration'));
		}
	}

	abstract public function authenticate();
}
