# Simple chat application
This simple chat application is written by Ahmad Sadeghi with Slim Framework

## Features
* Users creation (This project doesn't have authentication system)
* Create groups
* Join groups
* Send messages
* Receive messages

## Tech stack
The project uses the following tech stack:
* Slim Framework
* SQLite DBMS
* Phinx (to handle database migrations)
* PHPUnit (To run tests)
* jQuery (To handle the simple front-end)

## Install and run

You will need PHP 7.4 or higher version.

Run the following in console to install all dependencies:

```bash
composer update
```

To run all migrations, execute the following:
```bash
vendor/bin/phinx migrate
```
At last, to run the project in your local environment, execute the following:
```bash
php -S localhost:8888 -t public/
```

To check the front-end application, navigate to ``http://localhost:8888/chat``

## Testing
To test, run the following:

```bash
vendor/bin/phpunit test
```
In order to have fresh database every time we start testing, Phinx is responsible to rolling back ang migrating the migrations before we start the testing.
