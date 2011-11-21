# NinjAuth

Use the Auth, OAuth and OAuth2 packages to authenticate users with an array of third-party services in a totally integrated and abstracted fashion.

Implementation requires only one controller and one database table (two if you count users).

NinjAuth comes from the company behind the project: [HappyNinjas](http://happyninjas.com/).

## Supported Strategies

- OAuth
- OAuth2
- OpenID (by [krtek4](https://github.com/krtek4)) 

## TODO

- XAuth - anyone?
- More flexible registration (view files as properties, or config options)

## Installation

    # Create users if this table does not exist already
    $ oil g migration create_users username:varchar[50] password:string group:int email:string last_login:integer login_hash:string profile_fields:text
    $ oil refine migrate
	
	# Run migrations in the package to create "authentications" table
	$ oil refine migrate --packages=ninjauth
	
## Upgrade

Just the usual submodule update, and when you're done run:

	$ oil refine migrate --packages=ninjauth

## Usage Example

### Controller

http://example.com/auth/session/facebook

```php
class Controller_Auth extends \NinjAuth\Controller {}
```

### Configuration

	'somewhere' => array(
		'id' => '9cd980e0d883ERG42974b6cd78175135',
		'secret' => '19d874DW43534SDFfce025d9bba4423452',
		
		// Specify a specific callback
		'callback' => 'http://example.com/foo/bar',
	),

	'google' => array(
		'key' => 'yourkey',
		'secret' => 'yoursecret',
		
		// Provide a string or array for the API scope
		'scope' => array('https://www.google.com/analytics/feeds', 'https://www.google.com/m8/feeds'),
		
		// Google supports OAuth and OAuth2. Pick a specific
		'strategy' => 'OAuth',
	),
