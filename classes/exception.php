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

class Exception extends \FuelException
{

}

class CancelException extends Exception {}
class ResponseException extends Exception {}