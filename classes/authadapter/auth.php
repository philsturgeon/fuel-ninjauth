<?php
/**
 * Adapter class for Fuel's Auth package
 */
 
namespace NinjAuth;

class Adapter_Auth extends AuthAdapter {
  
  public function check()
  {
    return \Auth::check();
  }
  
  public function create_user($username, $password, $email, $group, $metadata)
  {
    try
    {
      $result = \Auth::create_user($username, $password, $email, $group, $metadata);
    }
    catch (SimpleUserUpdateException $e)
		{
		  // Re-throw as a NinjAuth\Exception so we can catch it more easily
		  throw new Exception($e->getMessage());
	  }
	  
	  return $result;
  }
  
  public function force_login($user_id)
  {
    return \Auth::instance()->force_login($user_id);
  }
  
  public function get_user_id()
  {
    return \Auth::instance()->get_user_id();
  }

}