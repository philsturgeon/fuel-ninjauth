<?php echo Form::open(array('id' => 'register')); ?>
	<p>
		<label for="username">Username</label>
		<?php echo Form::input('username', $user->username) ?>
	</p>
	<p>
		<label for="full_name">Full Name</label>
		<?php echo Form::input('full_name', $user->full_name) ?>
	</p>
	<p>
		<label for="email">Email</label>
		<?php echo Form::input('email', $user->email) ?>
	</p>
	<p>
		<label for="password">Password</label>
		<?php echo Form::password('password') ?>
	</p>
	<?php echo Form::submit('submit') ?>

<?php echo Form::close() ?>