<?php
/**
 * Module controller in the Mymodule module
 */

namespace NinjAuth;

class Controller extends \Controller {

	protected $provider = null;

	protected $consumer;

	protected $token;

	public function before()
	{
		parent::before();

		// Load the configuration for this provider
		\Config::load("ninjauth", true);
		\Config::load("oauth", true);
	}

	public function action_login($provider)
	{
		// Create an consumer from the config
		$this->consumer = \OAuth\OAuth_Consumer::factory(
			\Config::get("oauth.{$provider}")
		);

		// Load the provider
		$this->provider = \OAuth\OAuth_Provider::factory($provider);
		
		// Add the callback URL to the consumer
		$this->consumer->callback(\Uri::create($this->request->controller.'/authorise/'.$provider));

		// Get a request token for the consumer
		$token = $this->provider->request_token($this->consumer);

		// Store the token
		\Cookie::set('oauth_token', base64_encode(serialize($token)));

		// Redirect to the twitter login page
		\Response::redirect($this->provider->authorize_url($token));
	}

	public function action_authorise($provider)
	{
		$config = \Config::get("oauth.{$provider}");

		// Create an consumer from the config
		$this->consumer = \OAuth\OAuth_Consumer::factory($config);

		// Load the provider
		$this->provider = \OAuth\OAuth_Provider::factory($provider);
		
		if ($token = \Cookie::get('oauth_token'))
		{
			// Get the token from storage
			$this->token = unserialize(base64_decode($token));
		}
			
		if ($this->token AND $this->token->token !== \Input::get_post('oauth_token'))
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
		$token = $this->provider->access_token($this->consumer, $this->token);
		
		// Store the token
		\Cookie::set('ninjauth', base64_encode(serialize($token)));
		

		\Response::redirect(\Config::get('ninjauth.urls.registration'));
	}
	
	public function action_index()
	{
		$this->provider = \OAuth\OAuth_Provider::factory('twitter');
		
		$token = unserialize(base64_decode(\Cookie::get('ninjauth')));
		
		if ( ! Model_Authentication::count_by_token_and_secret($token->token, $token->secret))
		{
			// $data = $this->provider->get_user_data($token)->execute();

			
			\Debug::dump($token);
		}
		
		else
		{
			echo "yes";
		}
	}
}