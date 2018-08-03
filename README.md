Datagator
=========

Requirements
------------

* apache/nginx (see [foobar](https://foobar) for nginx config file)
* php >= 7.0
* mysql
* opcode (Memcache or APC)
* composer
* mcrpyt
* zip

Installation
------------

1. ```$ git clone gitolite@naala.com.au:datagator```
2. If Production:
   1. ```$ cd datagator```
   2. ```$ rm -R README.md CHANGELOG.md codeception.yml resources tests```
3. ```$ chmod 775 uploads```
4. Install [Composer](https://getcomposer.org/).
5. ```$ composer install```
6. ```$ composer dump-autoload```
7. Create an empty database and user. Give the user full permission for the DB.
8. Update ```includes/config.php```:
   1. Set the server role by editing the ```$_server``` array so that the LHS values contain the server hostname, and the RHS indicate the server role (development, staging or production).
   2. Set the database credentials that you created in step 5 in the server role function you defined in step 5.1.
   3. Set any other desired values you require in the role function you defined in ```$_server``` (see Config section for details)
9. Run ```includes/scripts/db/dbBase.sql```.
10. Update ```php.ini``` (if using non-apache server, see [Hardening your HTTP response headers](https://scotthelme.co.uk/hardening-your-http-response-headers/#removingheaders)):
    1. ```expose_php = Off```
11. Update ```httpd.conf```
    2. ```ServerSignature Off```
    3. ```ServerTokens Prod```

Config
------

The ```includes/config.php``` file is set up so that the same file can be used on multiple servers.

### $_server

This array indicates what a server's role is. A server can only have one of three possible values:

* development
* staging
* production

The indexes in the array contain the hostname of the server, and the value contains the value of the role.

### everywhere()

The values set in this function apply to all server roles, however, these values can overridden in the role functions (see below) or in the URL if $_allow_override has been set to true (see below).

#### $defaultFormat

Sets the default output format for API calls, if no ```Accept``` header value is received

#### $tokenLife

Sets the life of API tokens. Use format used by [strtotime](http://php.net/manual/en/function.strtotime.php) (e.g. "```+1 day```")

#### $dirVendor

The directory where composer installs the 3rd party files. You should need to edit this.

#### $dirYaml

The directory where you store your yaml files. You should not need to change this.

### development(), staging() and production()

#### $debug

Standard debug level:

0. None
1. Low
2. Medium
3. High
4. Everything

#### $debugDb

Debug level for any cli scripts:

0. None
1. Low
2. Medium
3. High
4. Everything

#### $debugCLI

Debug level for Database calls and DB instantiation:

0. None
1. Low
2. Medium
3. High
4. Everything

#### $_allow_override

if true, an api caller can override any of the config settings by assigning values to them in the URL. This should never be true on production servers.

#### $debugInterface

Where the debug data will be output. There are only two possible values:
* LOG
* HTML

#### $cache

Set to true to enable caching on the server (opcode service automatically discovered), set to false to disable.

If you have multiple services available on the server, you instruct Datagator to use a specific opcode by setting this value to ```apc``` for APC or ```memcache``` for MemCache.

#### $dbdriver

The db driver, e.g. ```mysqli```.

#### $dbhost

The DB hostname, e.g. ```localhost```.

#### $dbname

The DB name.

#### $dbuser

The DB user.

#### $dbpass

The DB password.

#### $dboptions

See [ADOdb documentation](http://phplens.com/lens/adodb/docs-adodb.htm) for possible values.

#### $errorLog

Path to the system error log.

#### Miscellaneous settings

You can set any server scpecific settings with init_set, date_default_timezone_set, etc within these functions.

Caching
-------

If you set the $cache setting in config (see above) to true, you need to have installed APC or Memcache. The system will automatically discover which opcode service has been installed and use the correct one.

Response error codes
--------------------

0. Core error
1. Processor format error
2. DB error
3. Invalid API call
4. Authorisation error
5. External error
6. Invalid processor input
7. Invalid application

Testing
-------

Testing is done with [Codeception](http://codeception.com/).
From Docroot, run:

```$ vendor/bin/codecept -v```

If you are running testcase first time in api suite, then in your api directory you will not have api tester file. You need to generate that so run following command:

```$ vendor/bin/codecept build```

### Testing user

The following testing credentials  are stored in /tests/_support/Helper/api.php

* Account: Datagator
* Application: Testing
* Username: tester
* Password: tester_pass

### Creating Tests

```$ vendor/bin/codecept generate:cept TestName```

### Create api test

```$ vendor/bin/codecept generate:cept api TestName```

### Test YAML files

These are in ```/tests/_data/```

### Running tests

```$ vendor/bin/codecept run --env staging|local|prod```

```$ vendor/bin/codecept run --env staging|local|prod api```

```$ vendor/bin/codecept run --env staging|local|prod api testName```

If only testing API locally:

```$ vendor/bin/codecept run api```

```$ vendor/bin/codecept run api testName```
