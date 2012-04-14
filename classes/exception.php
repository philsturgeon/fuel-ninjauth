<?php

namespace NinjAuth;

/**
 * NinjAuth Exception
 *
 * @package    FuelPHP/NinjAuth
 * @category   Exceptions
 * @author     Phil Sturgeon
 * @copyright  (c) 2012 HappyNinjas Ltd
 * @license    http://philsturgeon.co.uk/code/dbad-license
 */

class Exception extends \Fuel_Exception
{
	public function __construct($msg)
	{
		\Log::error($msg);
		
		parent::__construct($msg);
	}
}

class CancelException extends Exception {}