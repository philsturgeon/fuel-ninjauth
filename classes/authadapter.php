<?php
/**
 * @author Zarunor Zanzibar
 *
 * Base class for an authentication adapter
 * Abstracts authentication methods used by Controller and Strategy
 *
 * Release Notes:
 * The authentication adapter abstracts the interface to authentication methods. 
 * Currently, adapters exist for the FuelPHP Auth package and Cartalyst's Sentry package.
 *
 *
 * Ninjauth configuration:
 * - The auth adapter is set in Ninjauth config via ninjauth.auth_adapter, possible values are 'Auth' and 'Sentry'
 * - If ninjauth.auth_adapter is not set, then the Auth adapter will be used by default, just like current functionality
 *
 * Sentry configuration:
 * - The AuthAdapter_Sentry adapter class relies on several Sentry config entries. 
 * - These entries are not required, but they add flexibility to the adapter
 * - sentry.default_activation determines whether users are created with an activation requirement or not. 
 * - if sentry.default_activation is not set, then the Sentry adapter creates users with activation requirement set to false
 * - sentry.users_primary_key sets the primary key of the users table, in case it's not 'id'
 * - if sentry.users_primary_key is not set, then the primary key is assumed to be 'id'
 *
 * Exceptions:
 * Auth exceptions and Sentry exceptions are caught and their message is re-thrown as Ninjauth\Exception. 
 * The client of the adapter remains agnostic to the class of exceptions thrown, and catches them as Ninjauth\Exception
 */

namespace NinjAuth;

abstract class AuthAdapter {
	
	/**
	 * @var  string  Strategy name
	 */
	public $name;
	
	public function __construct($adapter, $provider)
	{
		$this->adapter = $adapter;
		$this->provider = $provider;
		
		if ( ! $this->name)
		{
			// Attempt to guess the name from the class name
			$this->name = strtolower(str_replace('NinjAuth\AuthAdapter_', '', get_class($this)));
		}
	}
	
	public static function forge($adapter, $provider)
	{		
		if (empty($adapter))
		{
			throw new Exception(sprintf('Adapter "%s" not specified.', $adapter));
		}
		
		$class = "NinjAuth\\AuthAdapter_{$adapter}";
		
		return new $class($adapter, $provider);
	}
	
	abstract public function check();
	
	abstract public function create_user($username, $password, $email, $group, $user_hash);
	
	abstract public function force_login($user_id);
	
	abstract public function get_user_id();	
}