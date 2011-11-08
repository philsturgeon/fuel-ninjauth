# NinjAuth v0.2.0

Use the Auth, OAuth and OAuth2 packages to authenticate users with an array of third-party services in a totally integrated and abstracted fashion.

Implementation requires only one controller and one database table (two if you count users).

NinjAuth comes from the company behind the project: [HappyNinjas](http://happyninjas.com/).

## Supported Strategies

- OAuth
- OAuth2

## TODO

- OpenID - pull request somebody?
- XAuth - anyone?
- More flexible registration (view files as properties, or config options)

## Installation

    # Create users if this table does not exist already
    $ oil g migration create_users username:varchar[50] password:string group:int email:string last_login:integer login_hash:string profile_fields:text
    $ oil refine migrate
	
	# Run migrations in the package to create "authentications" table
	$ oil refine migrate --packages=ninjauth

## Usage Example

http://example.com/auth/session/facebook

```php
class Controller_Auth extends \NinjAuth\Controller {}
```