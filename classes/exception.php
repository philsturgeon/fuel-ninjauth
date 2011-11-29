<?php
/**
 * NinjAuth Exception
 */

namespace NinjAuth;

class Exception extends \Fuel_Exception
{
	public function __construct($msg)
	{
		\Log::error($msg);
		
		parent::__construct($msg);
	}
}

class CancelException extends Exception {}