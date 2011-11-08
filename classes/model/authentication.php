<?php

namespace NinjAuth;

class Model_Authentication extends \Orm\Model {
	
	protected static $_properties = array('id', 'user_id', 'provider', 'uid', 'token', 'secret', 'created_at', 'updated_at');
	
}