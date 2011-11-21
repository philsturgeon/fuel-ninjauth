<?php

namespace Fuel\Migrations;

class Add_refresh_tokens {

	public function up()
	{
		\DBUtil::add_fields('authentications', array(
			'access_token' => array('constraint' => 255, 'type' => 'varchar', 'null' => true),
			'expires' => array('constraint' => 12, 'type' => 'int', 'default' => 0, 'null' => true),
			'refresh_token' => array('constraint' => 255, 'type' => 'varchar', 'null' => true),
		));
		
		\DBUtil::drop_fields('authentications', array('token'));
	}

	public function down()
	{
		\DBUtil::add_fields('authentications', array(
			'token' => array('constraint' => 255, 'type' => 'varchar', 'null' => true),
		));

		\DBUtil::drop_fields('authentications', array('access_token', 'expires', 'refresh_token'));
	}
}