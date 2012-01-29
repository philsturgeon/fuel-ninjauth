<?php
/**
 * Adapter class for Cartalyst's Sentry package
 */
 
namespace NinjAuth;

class Adapter_Sentry extends AuthAdapter {
  
  public function check()
  {
    return \Sentry::check();
  }
  
  public function create_user($username, $password, $email, $group, $user_hash)
  {
    $user_data = array(
      'username' => $username, 
      'password' => $password, 
      'email' => $email
    );
    
    // Sentry expects metadata to be a first name and a last name by default
    // yet the user hash will give us a full name, so we need to extract the first and last name
    
    if($full_name = \Arr::get($user_hash, 'name'))
    {
      $name_segments = explode(' ', $full_name);
      $metadata = false;
	  
  	  // Try to figure out the first name and last name from the full name
  	  if(count($name_segments) == 2)
  	  {
  	    $metadata = array('first_name' => $name_segments[0], 'last_name' => $name_segments[1]);
      }
      elseif(count($name_segments) > 0);
      {
        // If we got a name that isn't two words, then just stick it in both first name and last name
        // Better preserve user data than lose it
        $metadata = array('first_name' => $full_name, 'last_name' => $full_name);
      }
      
      if($metadata) // Set the metadata if we have any
      {
        $user_data['metadata'] = $metadata;
      }
    }
    
    try
    {
      $user_data = \Sentry::user()->create($user_data, \Config::get('sentry.default_activation', false));
    
      // Sentry user create() can return either a user id, or an array of user id and activation
      // hash depending on whether activation is set or not
      $user_id = is_array($user_data) ? $user_data[0] : $user_data;
    
      // add the user to a group if necessary
      if($group)
      {
        \Sentry::user($user_id)->add_to_group($group);
      }
    }
    catch(SentryUserException $e)
    {
      throw new Exception($e->getMessage());
    }  
    
    return $user_id;  
  }
  
  public function force_login($user_id)
  {
    // The provider will get stored in the sentry session
    try
    {
      $result = \Sentry::force_login($user_id, $this->provider ?: 'Sentry-Forced');
    }
    catch(SentryAuthException $e)
    {
      throw new Exception($e->getMessage());
    }
    
    return $result;
  }
  
  public function get_user_id()
  {
    try
    {
      $user_id = \Sentry::user()->get(\Config::get('sentry.users_primary_key', 'id'));
    }
    catch (SentryUserException $e)
    {
      throw new Exception($e->getMessage());
    }
    
    return $user_id;
  }

}