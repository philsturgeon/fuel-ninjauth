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

	public function action_session($provider)
	{
		// Create an consumer from the config
		$this->consumer = \OAuth\OAuth_Consumer::factory(
			\Config::get("oauth.{$provider}")
		);

		// Load the provider
		$this->provider = \OAuth\OAuth_Provider::factory($provider);
		
		// Create the URL to return the user to
		$callback = \Uri::create($this->request->controller.'/callback/'.$provider);
		
		
		// Add the callback URL to the consumer
		$this->consumer->callback($callback);	

		// Get a request token for the consumer
		$token = $this->provider->request_token($this->consumer);

		// Store the token
		\Cookie::set('oauth_token', base64_encode(serialize($token)));

		// Redirect to the twitter login page
		\Response::redirect($this->provider->authorize_url($token, array(
			'oauth_callback' => $callback,
		)));
	}

	public function action_callback($provider)
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
		
		// The user exists, so send him on his merry way as a user
		if ($user = Model_Authentication::find_by_token_and_secret($token->token, $token->secret))
		{
			// first of all, let's get a auth object
			$auth = \Auth::instance();

			// Force a login with this username
			if ($auth->force_login($user->username))
			{
			    // credentials ok, go right in
			    \Response::redirect(\Config::get('ninjauth.urls.logged_in'));
			}
		}
		
		// They aren't a user, so redirect to registration page
		else
		{
			$user_hash = $this->provider->get_user_info($this->consumer, $token);
			
			\Session::set('ninjauth', $user_hash);
		}
		
		\Response::redirect(\Config::get('ninjauth.urls.registration'));
	}
	

	public function action_register()
	{
		$user_hash = \Session::get('ninjauth');
		
		$full_name = \Input::post('full_name') ?: \Arr::get($user_hash, 'name');
		$username = \Input::post('username') ?: \Arr::get($user_hash, 'nickname');
		$email = \Input::post('email') ?: \Arr::get($user_hash, 'email');
		$password = \Input::post('password');
		
		if ($username and $full_name and $email and $password)
		{
			$user_id = \Auth::create_user($username, $password, $email, 1, array(
				'full_name' => $full_name,
			));
			
			if ($user_id)
			{
				Model_Authentication::forge(array(
					'user_id' => $user_id,
					'provider' => $user_hash['credentials']['provider'],
					'uid' => $user_hash['credentials']['uid'],
					'token' => $user_hash['credentials']['token'],
					'secret' => $user_hash['credentials']['secret'],
					'created_at' => time(),
				))->save();
			}
			
			\Response::redirect(\Config::get('ninjauth.urls.registered'));
		}
		
		$this->response->body = \View::forge('register', array(
			'user' => (object) compact('username', 'full_name', 'email', 'password')
		));
	}
	
	
}