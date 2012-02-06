<?php

namespace NinjAuth;

/**
 * Add OpenID Strategy to Ninjauth. Requires the LightOpenID library.
 * The library can be found at http://gitorious.org/lightopenid and must
 * be accessible through the global namespace
 */
class Strategy_OpenId extends Strategy
{
	/**
	 * @var LightOpenID our OpenId library instance
	 */
	private $openid;

	/**
	 * Create the LightOpenId instance.
	 * @param string $provider The provider name, in our case openid
	 */
	public function __construct($provider)
	{
		parent::__construct($provider);
		$this->openid = new \LightOpenID(\Input::server('HTTP_HOST'));
		
		// Add the provider name
		$this->provider = (object) array('name' => $provider);
	}

	/**
	 * Set all the config needed then redirect to the OpenId provider to
	 * authenticate the user.
	 * The post variable set in the configuration must contains the OpenId
	 * identity !
	 */
	public function authenticate()
	{
		$identity = \Input::post(\Config::get('ninjauth.providers.openid.identifier_form_name'));
		if (empty($identity))
		{
			throw new Exception('No identity provided');
		}

		$this->openid->identity = $identity;
		$this->openid->required = \Config::get('ninjauth.providers.openid.ax_required');
		$this->openid->optional = \Config::get('ninjauth.providers.openid.ax_optional');
		$this->openid->returnUrl = \Uri::create(\Config::get('ninjauth.urls.callback', \Request::active()->route->segments[0].'/callback').'/'.$this->provider);

		try
		{
			header('Location: '.$this->openid->authUrl());
		}
		catch(Exception $e)
		{
			throw new Exception('Unable to find OpenId provider URL', 404, $e);
		}
		exit(); // must exit here since we do a redirection.
	}

	/**
	 * Check the OpenId return status and returns the  necessary information
	 * to continue the login process
	 * @return mixed
	 */
	public function callback()
	{
		if ($this->openid->mode == 'cancel')
		{
			throw new CancelException('User canceled the process');
		}
		if (! $this->openid->validate())
		{
			throw new Exception('Invalid OpenId response');
		}

		return (object) array(
			'access_token' => $this->openid->identity,
		);
	}

	/**
	 * @var array Mapping between required fields and their OpenId AX Schema counterpart
	 */
	private static $mapping = array(
		'nickname' => 'namePerson/friendly',
		'name' => array('namePerson/first', 'namePerson/last'),
		'email' => 'contact/email',
		'location' => 'contact/country/home'
	);

	/**
	 * @param mixed $map The index we want to retrieve or an array of index
	 * @param array $data The array containing the OpenId data
	 * @return string content of the given indexe(s) or ''
	 */
	private function get_data($map, $data)
	{
		$r = '';
		if (is_array($map))
		{
			foreach ($map as $m)
			{
				$r .= $this->get_data($m, $data);
			}
		}
		else if (array_key_exists($map, $data))
		{
			$r = $data[$map];
		}
		return $r;
	}

	/**
	 * @param mixed $response The response returned by callback()
	 * @return array Relevent information to create a new authentication entry
	 */
	public function get_user_info($response)
	{
		$ret['uid'] = $this->openid->identity;

		$data = $this->openid->getAttributes();
		
		foreach (static::$mapping as $name => $map)
		{
			$ret[$name] = $this->get_data($map, $data);
		}

		return $ret;
	}
}
