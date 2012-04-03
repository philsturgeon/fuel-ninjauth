<?php

namespace NinjAuth;

/**
 * NinjAuth Controller
 *
 * @package    FuelPHP/NinjAuth
 * @category   Controller
 * @author     Phil Sturgeon
 * @copyright  (c) 2012 HappyNinjas Ltd
 * @license    http://philsturgeon.co.uk/code/dbad-license
 */

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

		// Working with what?
		$strategy = Strategy::forge($authentication['provider']);
		
		$full_name = \Input::post('full_name') ?: \Arr::get($user_hash, 'name');
		$username = \Input::post('username') ?: \Arr::get($user_hash, 'nickname');
		$email = \Input::post('email') ?: \Arr::get($user_hash, 'email');
		$password = \Input::post('password');
		
		if ($username and $full_name and $email and $password)
		{
			$user_id = $strategy->adapter->create_user(array(
				'username' => $username,
				'email' => $email,
				'full_name' => $full_name,
				'password' => $password,
			));

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

				\Response::redirect(\Config::get('ninjauth.urls.registered'));
			}
		}
		
		$this->response->body = \View::forge('register', array(
			'user' => (object) compact('username', 'full_name', 'email', 'password')
		));
	}
	
	
}