<?php

namespace NinjAuth;

use Warden;
use Model_User;
use Model_Profile;
use Orm;
use Config;
use Session;
use Str;

class Adapter_Warden extends Adapter
{
	public function is_logged_in()
	{
		return Warden::check();
	}

	public function get_user_id()
	{
		return Warden::check() ? Warden::current_user()->id : false;
	}

	public function force_login($user_id)
	{
		return Warden::force_login($user_id);
	}

	public function create_user(array $user)
	{
		try
		{
			$new_user = Model_User::forge(array(
				'username' => isset($user['username']) ? $user['username'] : '',
				'email'    => isset($user['email']) ? $user['email'] : '',
				'password' => isset($user['password']) ? $user['password'] : Str::random()
			));
			
			if (Config::get('warden.profilable') === true) {
				$new_user->profile = Model_Profile::forge(array(
					'first_name' => isset($user['first_name']) ? $user['first_name'] : '',
					'last_name'  => isset($user['last_name']) ? $user['last_name'] : ''
				));
			}
			
			$new_user->save();
			
		    return $new_user->id ?: false;
		}
		catch (Orm\ValidationFailed $e)
		{
		    Session::set_flash('ninjauth.error', $e->getMessage());
		}

		return false;
	}

	public function can_auto_login(array $user)
	{
		// To automatically register with warden you need both to be set
		return isset($user['username']) && isset($user['email']);
	}
}
