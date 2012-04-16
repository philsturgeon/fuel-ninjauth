<?php

namespace NinjAuth;

use Arr;
use Config;
use Input;
use Response;
use Session;

/**
 * NinjAuth Controller
 *
 * @package    FuelPHP/NinjAuth
 * @category   Controller
 * @author     Phil Sturgeon
 * @copyright  (c) 2012 HappyNinjas Ltd
 * @license    http://philsturgeon.co.uk/code/dbad-license
 */

class Controller extends \Controller
{
	public static $linked_redirect = '/auth/linked';
	public static $login_redirect = '/';
	public static $register_redirect = '/auth/register';
	public static $registered_redirect = '/';

	public function before()
	{
		parent::before();

		// Load the configuration for this provider
		Config::load('ninjauth', true);
	}

	public function action_session($provider)
	{
		$url = Strategy::forge($provider)->authenticate();
		
		Response::redirect($url);
	}

	public function action_callback($provider)
	{
		// Whatever happens, we're sending somebody somewhere
		$status = Strategy::forge($provider)->login_or_register();

		// Stuff should go with each type of response
		switch ($status)
		{
			case 'linked':
				$message = 'You have linked '.$provider.' to your account.';
				$url = static::$linked_redirect;
			break;

			case 'logged_in':
				$message = 'You have logged in.';
				$url = static::$login_redirect;
			break;

			case 'registered':
				$message = 'You have logged in with your new account.';
				$url = static::$registered_redirect;
			break;

			case 'register':
				$message = 'Please fill in any missing details and add a password.';
				$url = static::$register_redirect;
			break;

			default:
				throw new Exception('Strategy::login_or_register() has come up with a result that we dont know how to handle.');
		}

		Response::redirect($url);
	}

	public function action_register()
	{
		$user_hash = Session::get('ninjauth.user');
		$authentication = Session::get('ninjauth.authentication');

		// Working with what?
		$strategy = Strategy::forge($authentication['provider']);
		
		$full_name = Input::post('full_name') ?: Arr::get($user_hash, 'name');
		$username = Input::post('username') ?: Arr::get($user_hash, 'nickname');
		$email = Input::post('email') ?: Arr::get($user_hash, 'email');
		$password = Input::post('password');
		
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

				Response::redirect(static::$registered_redirect);
			}
		}
		
		$this->response->body = \View::forge('register', array(
			'user' => (object) compact('username', 'full_name', 'email', 'password')
		));
	}
}