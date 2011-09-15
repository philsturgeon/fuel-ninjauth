# NinjAuth

Use the Auth, oAuth and oAuth2 packages to authenticate users with an array of third-party services in a totally integrated and abstracted fashion.

Implementation requires only one controller and one database table (two if you count users).

NinjAuth comes from the company behind the project: [HappyNinjas](http://happyninjas.com/).

## Supported Strategies

- oAuth
- oAuth2

## TODO

- OpenID - pull request somebody?
- XAuth - anyone?
- More flexible registration (view files as properties, or config options)

## Installation

```sql
CREATE TABLE `authentications` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `provider` varchar(50) DEFAULT NULL,
  `uid` varchar(255) DEFAULT NULL,
  `token` varchar(255) DEFAULT NULL,
  `secret` varchar(255) DEFAULT NULL,
  `created_at` int(11) unsigned DEFAULT NULL,
  `updated_at` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `token` (`token`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `users` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
    `username` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
    `password` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
    `group` INT NOT NULL DEFAULT 1 ,
    `email` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
    `last_login` VARCHAR( 25 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
    `login_hash` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
    `profile_fields` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
    UNIQUE ( `username` , `email` ),
	KEY `username` (`username`)
);
````

## Usage Example

http://example.com/auth/session/facebook

```php

class Controller_Auth extends \NinjAuth\Controller {}
```