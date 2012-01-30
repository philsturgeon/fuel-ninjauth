<?php
/**
 * Module controller in the Mymodule module
 */

namespace NinjAuth;

class Controller extends \Controller {

	public function before()
	{
		parent::before();

		// Load the configuration for this provider
		\Config::load('ninjauth', true);
	}

	public function action_session($provider)
	{
		Strategy::forge($provider)->authenticate();
	}

	public function action_callback($provider)
	{
		$strategy = Strategy::forge($provider);
		
		Strategy::login_or_register($strategy);
	}

	public function action_register()
	{
		$user_hash = \Session::get('ninjauth.user');
		$authentication = \Session::get('ninjauth.authentication');
		
		$full_name = \Input::post('full_name') ?: \Arr::get($user_hash, 'name');
		$username = \Input::post('username') ?: \Arr::get($user_hash, 'nickname');
		$email = \Input::post('email') ?: \Arr::get($user_hash, 'email');
		$password = \Input::post('password');
		
		// Use auth adapter to interface the auth class specified in the config file
		$auth_adapter = AuthAdapter::forge(\Config::get('ninjauth.auth_adapter', 'Auth'), $authentication['provider']);
		
		if ($username and $full_name and $email and $password)
		{
			try
			{
				// Just give the adapter the user hash, since different adapter might care about different keys
				$user_id = $auth_adapter->create_user($username, $password, $email, \Config::get('ninjauth.default_group'), $user_hash);										
			}
			catch (Exception $e)
			{
				\Session::set_flash('ninjauth.error', $e->getMessage());
				goto display;
			}
			
			if ($user_id)
			{				
				Model_Authentication::forge(array(
					'user_id' => $user_id,
					'provider' => $authentication['provider'],
					'uid' => $authentication['uid'],
					'access_token' => $authentication['access_token'],
					'secret' => $authentication['secret'],
					'refresh_token' => $authentication['refresh_token'],
					'expires' => $authentication['expires'],
					'created_at' => time(),
				))->save();
				
				// No reason not to log the new user in
				$auth_adapter->force_login($user_id);
			}
			
			\Response::redirect(\Config::get('ninjauth.urls.registered'));
		}
		
		display:
		
		$this->response->body = \View::forge('register', array(
			'user' => (object) compact('username', 'full_name', 'email', 'password')
		));
	}
	
	
}