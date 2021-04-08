Stack-Agent-Thing
=================

Stack Agent Thing frame work for Thing based Agent processing with Stack serialization and deserialization.

To share a Thing's state.  With other Things.

Copyright 2018-2019. Stackr Interactive Ltd.

Requirements
============

* PHP >= 7
* BCMath
* Gearman

Installation
============

To install all the required parts:  

```shell
composer require nrwtaylor/stack-agent-thing
```

Stack build recipe. See `BUILD` in root folder.

## 1.  Install Ubuntu.

## 2.  Install LAMP stack.

## 3.  Create MySQL database.

```shell
mysql> DESCRIBE stack;
+--------------+---------------+------+-----+-------------------+-------+
| Field        | Type          | Null | Key | Default           | Extra |
+--------------+---------------+------+-----+-------------------+-------+
| id           | int(11)       | YES  |     | NULL              |       |
| uuid         | char(36)      | YES  | UNI | NULL              |       |
| task         | varchar(200)  | YES  |     | NULL              |       |
| status       | tinyint(1)    | NO   |     | 1                 |       |
| created_at   | datetime      | NO   |     | CURRENT_TIMESTAMP |       |
| nom_to       | varchar(80)   | YES  |     | NULL              |       |
| nom_from     | varchar(80)   | YES  | MUL | NULL              |       |
| associations | varchar(998)  | YES  |     | NULL              |       |
| message0     | varchar(998)  | YES  |     | NULL              |       |
| message1     | varchar(998)  | YES  |     | NULL              |       |
| message2     | varchar(998)  | YES  |     | NULL              |       |
| message3     | varchar(998)  | YES  |     | NULL              |       |
| message4     | varchar(998)  | YES  |     | NULL              |       |
| message5     | varchar(998)  | YES  |     | NULL              |       |
| message6     | varchar(998)  | YES  |     | NULL              |       |
| message7     | varchar(998)  | YES  |     | NULL              |       |
| settings     | varchar(998)  | YES  |     | NULL              |       |
| variables    | varchar(3998) | YES  | MUL | NULL              |       |
+--------------+---------------+------+-----+-------------------+-------+
```

## 4.  Install PHP extensions
    
```shell 
sudo apt install php7.1-bcmath
sudo apt-get install php-bcmath
```

## 5.  Configure private/settings.php

Not all the settings need to be set. But you will need to enter the MySQL database settings.

## 6.  Test localhost serving to local-wide TCP/IP

```shell
php -S localhost:8080 -t public public/index.php
```
## 7.  Install PHP extensions

```shell
sudo apt install php7.1-bcmath
sudo apt-get install php-bcmath
restart
```

## 8.  Set-up and verify cron

```shell
* * * * * cd /var/www/html/<site name> && /usr/bin/php -q /var/www/html/<site name>/agents/Cron.php >/dev/null 2>&1
```

## 9.  Install Gearman.

## 10. Test stack. 

Browse to: 
```
http://localhost
http://localhost/privacy.  Select 'Start'.
```

## 11.  Test Roll PNG

```shell
agent roll
agent ping
```

## 12.  Test Snowflake (web, PNG, PDF)

```shell
agent snowflake
```

Usage
=====

Make a Thing
```php
    $thing = new Thing(null); // creates a UUID for a thing

    Show a Thing's UUID
    echo $thing->uuid; // display UUID

    Instantiate a Thing
    $thing->Create("from","to","test message");

    Run an agent on a Thing
    $agent = new Start($thing); // runs the Start agent 
```

Run the start agent from a browser: 

```
http://www.stackr.test/thing/67a8038d-4c19-4777-9b5f-18b8b74d8f1e/start
```

Credits
=======

* Rob Allen for Slim
* Jeff Atwood and Joel Spolsky for Stack Overflow
* Alan Turing for On Computable Numbers

Dev
===
`https://stack-agent-thing.slack.com/archives/C01PT2V6B8U` Slack invitation
