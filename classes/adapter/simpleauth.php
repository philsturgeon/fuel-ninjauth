<?php

namespace NinjAuth;

use Auth;

class Adapter_SimpleAuth extends Adapter
{
	public function is_logged_in()
	{
		return Auth::check();
	}

	public function get_user_id()
	{
		list($driver, $user_id) = Auth::instance()->get_user_id();
		return $user_id;
	}

	public function force_login($user_id)
	{
		return Auth::instance()->force_login($authentication->user_id);
	}

	public function create_user(array $user)
	{
		try
		{
			$user_id = Auth::create_user(

				// Username
				isset($user['username']) ? $user['username'] : null,

				// Password (random string will do if none provided)
				isset($user['password']) ? $user['password'] : \Str::random(),

				// Email address 
				isset($user['username']) ? $user['username'] : null,

				// Which group are they?
				\Config::get('ninjauth.default_group'), 

				// Extra information
				array(

					// Got their full name? Or first and last to make up a full name?
					'full_name' => isset($user['full_name']) ? $user['full_name'] : (
						isset($user['first_name'], $user['last_name']) ? $user['first_name'].' '.$user['last_name'] : null
					),
				)
			);
			
		    return $user_id ?: false;
		}
		catch (SimpleUserUpdateException $e)
		{
		    \Session::set_flash('ninjauth.error', $e->getMessage());
		}

		return false;
	}
}
