<?php

namespace NinjAuth;

use Sentry;
use Session;
use Str;

class Adapter_Sentry extends Adapter
{
	public function is_logged_in()
	{
		return Sentry::check();
	}

	public function get_user_id()
	{
		return Sentry::check() ? Sentry::user()->get('id') : false;
	}

	public function force_login($user_id)
	{
		return Sentry::force_login($user_id);
	}

	public function create_user(array $user)
	{
		try
		{
		    $user_id = Sentry::user()->create(array(
		    	'username' => isset($user['username']) ? $user['username'] : '',
		    	'email'    => isset($user['email']) ? $user['email'] : '',
		    	'password' => isset($user['password']) ? $user['password'] : Str::random(),
		    	'metadata' => array(
		    		'first_name' => isset($user['first_name']) ? $user['first_name'] : '',
		    		'last_name'  => isset($user['last_name']) ? $user['last_name'] : '',
		    	)
		    ));

		    return $user_id ?: false;
		}
		catch (SentryUserException $e)
		{
		    Session::set_flash('ninjauth.error', $e->getMessage());
		}

		return false;
	}

	public function can_auto_login(array $user)
	{
		// To automatically register with sentry you only need one or the other
		return isset($user['username']) or isset($user['email']);
	}
}
