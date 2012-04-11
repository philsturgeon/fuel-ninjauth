<?php

namespace NinjAuth;

class Strategy_OAuth extends Strategy {
	
	public $provider;
	
	public function authenticate()
	{
		// Create an consumer from the config
		$consumer = \OAuth\Consumer::forge($this->config);
		
		// Load the provider
		$provider = \OAuth\Provider::forge($this->provider);
		
		if ( ! $callback = \Arr::get($this->config, 'callback'))
		{
			// Turn /whatever/controller/session/facebook into /whatever/controller/callback/facebook
			$callback = \Uri::create(str_replace('/session/', '/callback/', \Request::active()->route->path));
		}

		// Add the callback URL to the consumer
		$consumer->callback($callback);	

		// Get a request token for the consumer
		$token = $provider->request_token($consumer);

		// Store the token
		\Cookie::set('oauth_token', base64_encode(serialize($token)));

		return $provider->authorize_url($token, array(
			'oauth_callback' => $callback,
		));
	}
	
	
	public function callback()
	{
		// Create an consumer from the config
		$this->consumer = \OAuth\Consumer::forge($this->config);

		// Load the provider
		$this->provider = \OAuth\Provider::forge($this->provider);
		
		if ($token = \Cookie::get('oauth_token'))
		{
			// Get the token from storage
			$this->token = unserialize(base64_decode($token));
		}
			
		if ($this->token AND $this->token->access_token !== \Input::get_post('oauth_token'))
		{
			// Delete the token, it is not valid
			\Cookie::delete('oauth_token');

			// Send the user back to the beginning
			exit('invalid token after coming back to site');
		}

		// Get the verifier
		$verifier = \Input::get_post('oauth_verifier');

		// Store the verifier in the token
		$this->token->verifier($verifier);

		// Exchange the request token for an access token
		return $this->provider->access_token($this->consumer, $this->token);
	}
	
}