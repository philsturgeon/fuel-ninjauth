<?php

namespace NinjAuth;

abstract class Adapter
{
	/**
	 * @var  string  Adapter name
	 */
	public $name;
	
	public function __construct()
	{
		if ( ! $this->name)
		{
			// Attempt to guess the name from the class name
			$this->name = strtolower(str_replace('NinjAuth\Adapter_', '', get_class($this)));
		}
	}
	
	public static function forge($adapter)
	{
		$class = 'NinjAuth\\Adapter_'.ucfirst($adapter);
		
		return new $class;
	}
	
	public abstract function is_logged_in();
	public abstract function get_user_id();
	public abstract function force_login($user_id);
	public abstract function create_user(array $user);
	public abstract function can_auto_login(array $user);
}
