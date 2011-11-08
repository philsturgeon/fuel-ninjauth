<?php

namespace Fuel\Migrations;

class Create_authentications {

	public function up()
	{
		\DBUtil::create_table('authentications', array(
			'id' => array('type' => 'int unsigned', 'auto_increment' => true),
			'user_id' => array('type' => 'int unsigned'),
			'provider' => array('constraint' => 50, 'type' => 'varchar'),
			'uid' => array('constraint' => 255, 'type' => 'varchar'),
			'token' => array('constraint' => 255, 'type' => 'varchar'),
			'secret' => array('constraint' => 255, 'type' => 'varchar'),
			'created_at' => array('constraint' => 11, 'type' => 'int'),
			'updated_at' => array('constraint' => 11, 'type' => 'int'),
		), array('id'));
		
		\DBUtil::create_index('authentications', 'token', 'token');
		\DBUtil::create_index('authentications', 'user_id', 'user_id');
	}

	public function down()
	{
		\DBUtil::drop_table('authentications');
	}
}