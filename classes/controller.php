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
		$user_hash = \Session::get('ninjauth');
		
		$full_name = \Input::post('full_name') ?: \Arr::get($user_hash, 'name');
		$username = \Input::post('username') ?: \Arr::get($user_hash, 'nickname');
		$email = \Input::post('email') ?: \Arr::get($user_hash, 'email');
		$password = \Input::post('password');
		
		if ($username and $full_name and $email and $password)
		{
			try
			{
				$user_id = \Auth::create_user($username, $password, $email, \Config::get('ninjauth.default_group'), array(
					'full_name' => $full_name,
				));
			}
			catch (SimpleUserUpdateException $e)
			{
				\Session::set_flash('ninjauth.error', $e->getMessage());
				goto display;
			}
			
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
		
		display:
		
		$this->response->body = \View::forge('register', array(
			'user' => (object) compact('username', 'full_name', 'email', 'password')
		));
	}
	
	
}