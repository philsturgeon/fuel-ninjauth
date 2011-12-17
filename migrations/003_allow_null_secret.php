<?php

namespace Fuel\Migrations;

class Allow_null_secret {

	public function up()
	{
		\DBUtil::modify_fields('users2', array(
			'secret' => array('constraint' => 255, 'type' => 'varchar', 'null' => true),
		));
	}

	public function down()
	{
		\DBUtil::modify_fields('users2', array(
			'secret' => array('constraint' => 255, 'type' => 'varchar', 'null' => false),
		));
	}
}