<?php

namespace NinjAuth;

abstract class Strategy {
	
	protected static $providers = array(
		'facebook' => 'OAuth2',
		'twitter' => 'OAuth',
		'dropbox' => 'OAuth',
		'flickr' => 'OAuth',
		'google' => 'OAuth',
		'linkedin' => 'OAuth',
		'youtube' => 'OAuth',
	);
	
	public function __construct($provider)
	{
		$this->provider = $provider;
		
		$this->config = \Config::get("ninjauth.providers.{$provider}");
	}
	
	public static function factory($provider)
	{
		$strategy = \Arr::get(static::$providers, $provider);
		
		if ( ! $strategy)
		{
			throw new Exception(sprint('Provider "%s" has no strategy.', $provider));
		}
		
		$class = "NinjAuth\\Strategy_{$strategy}";
		return new $class($provider);
	}

	abstract public function authenticate();
}