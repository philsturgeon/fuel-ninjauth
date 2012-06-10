<?php

namespace NinjAuth;

use OAuth2\Provider;

use Input;
use Uri;
use Request;

class Strategy_OAuth2 extends Strategy
{	
	public $provider;
	
	public function authenticate()
	{
		// Load the provider
		$provider = Provider::forge($this->provider, $this->config);
		
		// Grab a callback from the config
		if ($provider->callback === null)
		{
			// Turn /whatever/controller/session/facebook into /whatever/controller/callback/facebook
			$provider->callback = Uri::create(str_replace('/session/', '/callback/', Request::active()->route->path));
		}
		
		return $provider->authorize(array(
			'redirect_uri' => $provider->callback
		));
	}
	
	public function callback()
	{
		// Load the provider
		$this->provider = Provider::forge($this->provider, $this->config);
		
		if (Input::get('code'))
		{
			return $this->provider->access(Input::get('code'));
		}

		else
		{
			switch (Input::get('error'))
			{
				case 'access_denied':
					throw new CancelException('The resource owner or authorization server denied the request.');

				case 'invalid_request':
					throw new ResponseException('The request is missing a required parameter, includes an invalid 
						parameter value, includes a parameter more than once, or is otherwise malformed.');

				case 'unauthorized_client':
            		throw new ResponseException('The client is not authorized to request an authorization code 
            			using this method.');

				case 'unsupported_response_type':
            		throw new ResponseException('The authorization server does not support obtaining an
               			authorization code using this method.');

				case 'invalid_scope':
            		throw new ResponseException('The requested scope is invalid, unknown, or malformed.');

				case 'server_error':
            		throw new ResponseException('The authorization server encountered an unexpected
               condition which prevented it from fulfilling the request.');

				case 'temporarily_unavailable':
            		throw new ResponseException('The authorization server is currently unable to handle
               the request due to a temporary overloading or maintenance
               of the server.');
			}
		}
	}
}
