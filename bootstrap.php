<?php
/**
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * @package    Fuel
 * @version    1.0
 * @OAuthor     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2011 Fuel Development Team
 * @link       http://fuelphp.com
 */

Autoloader::add_classes(array(
	'NinjAuth\\Controller'           	=> __DIR__.'/classes/controller.php',
	'NinjAuth\\Exception'  				=> __DIR__.'/classes/exception.php',
	'NinjAuth\\Model_Authentication'  	=> __DIR__.'/classes/model/authentication.php',

	'NinjAuth\\Strategy'  				=> __DIR__.'/classes/strategy.php',
	'NinjAuth\\Strategy_OAuth'  		=> __DIR__.'/classes/strategy/oauth.php',
	'NinjAuth\\Strategy_OAuth2'  		=> __DIR__.'/classes/strategy/oauth2.php',
	'NinjAuth\\Strategy_OpenId'  		=> __DIR__.'/classes/strategy/openid.php',
));

/* End of file bootstrap.php */