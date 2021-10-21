(c) 2018-2021. NRW Taylor

These are the current build instructions as of October 2019.

## 1. Install Ubuntu latest.

Which finds you here. 

## 2. Install LAMP stack.

[LAMP (PHP version)][1]
: Linux + Apache + MySQL + PHP

[1]: https://www.qwant.com/?q=lamp+stack

Curated instructions at link below. Stop at configuration. Use the Install Packages Separately.  
https://www.linode.com/docs/web-servers/lamp/install-lamp-stack-on-ubuntu-18-04/

## 3. MySQL
3 (cont). Set-up InnoDB.

```
mysql -u root -p

// GRANT ALL PRIVILEGES ON *.* TO 'stackuser'@'localhost' IDENTIFIED BY 'password';
// https://stackoverflow.com/questions/50177216/how-to-grant-all-privileges-to-root-user-in-mysql-8-0
mysql> CREATE USER 'stackuser'@'%' IDENTIFIED BY 'stackuser';
mysql> GRANT ALL PRIVILEGES ON *.* TO 'stackuser'@'%' WITH GRANT OPTION;

mysql -u stackuser -p

CREATE DATABASE stack_db;

wget https://raw.githubusercontent.com/nrwtaylor/stack-agent-thing/master/templates/database_schema.sql
mysql -u stackuser -p stack_db < database_schema.sql

mysql> USE  stack_db;

mysql> DESC stack;
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
Useful commands.
```mysql
USE stack_db;
pager less -SFX;
SELECT * FROM stack ORDER BY created_at DESC limit 99;
DELETE FROM stack WHERE nom_from='null@<mail_postfix>' and nom_to='choice' and created_at < NOW() - INTERVAL 1 WEEK;
DELETE FROM stack WHERE nom_from='null@stackr.ca' and created_at < NOW() - INTERVAL 1 WEEK;
```
Configure `my.cnf`
```
innodb_buffer_pool_size=1G
```

## 3. Setup PHP
3 cont. Install PHP extensions
```shell
sudo apt-get update
sudo apt-get install php-mbstring
sudo apt-get install php7.2-xml
sudo apt-get install php-intl

sudo apt install php7.2-bcmath
sudo apt install php7.0-gd
(ignore php7.0 module already enabled, not enabling PHP 7.2)
sudo apt-get install php7.2-gd
sudo apt-get install php-curl
sudo apt-get install php7.4-zip
sudo service apache2 restart
```
## 4. Setup Apache 2
```shell
mkdir /var/www/stackr.test
cd /var/www/stackr.test
```
https://stackoverflow.com/questions/22390001/runtimeexception-vendor-does-not-exist-and-could-not-be-created/43513522#43513522
```shell
sudo usermod -a -G www-data `whoami`

sudo chown root:root /var/www
sudo chmod 755 /var/www/

sudo chown -R www-data:www-data /var/www/stackr.test
sudo chmod -R 774 /var/www/stackr.test
```
More on folder permissions.  
https://askubuntu.com/questions/767504/permissions-problems-with-var-www-html-and-my-own-home-directory-for-a-website
```shell
wget https://raw.githubusercontent.com/nrwtaylor/stack-agent-thing/master/composer.json
sudo apt install composer
composer install
```

More recently tested this way. You may need to manually create a `vendor` folder, and give that folder permissions for `composer` to access.

```shell
composer config minimum-stability dev
cd /var/www
sudo git clone https://github.com/nrwtaylor/agent.git stackr.test
composer install
```


Load in template public and private configuration files.
```shell
cp -r /var/www/stackr.test/vendor/nrwtaylor/stack-agent-thing/public /var/www/stackr.test/
cp -r /var/www/stackr.test/vendor/nrwtaylor/stack-agent-thing/private /var/www/stackr.test/
```
Load in resources under `/var/www/stackr.test/resources`.
```shell
cp -r /var/www/stackr.test/vendor/nrwtaylor/stack-agent-thing/resources /var/www/stackr.test/
```

> Can stop here if there is no need to serve stack web requests.
```shell
cd /etc/apache2/sites-available
sudo nano 000-default.conf
```
```
<VirtualHost *:80>

<Directory /var/www/stackr.test/public>
    Options FollowSymLinks
    DirectoryIndex index.php
    AllowOverride All
        Require all granted
</Directory>

    ServerAdmin nrwtaylor@gmail.com
    DocumentRoot /var/www/stackr.test/public

    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```
```shell
sudo service apache2 restart
```
No changes to `apache2.conf`
```shell
sudo nano stackr.test.conf
```
```
<Directory /var/www/stackr.test/public>
        Require all granted
</Directory>

<VirtualHost *:80>
    # The ServerName directive sets the request scheme, hostname and port that
    # the server uses to identify itself. This is used when creating
    # redirection URLs. In the context of virtual hosts, the ServerName
    # specifies what hostname must appear in the request's Host: header to
    # match this virtual host. For the default virtual host (this file) this
    # value is not decisive as it is used as a last resort host regardless.
    # However, you must set it for any further virtual host explicitly.
    #ServerName www.example.com

    ServerName stackr.test
    ServerAlias www.stackr.test
    ServerAdmin webmaster@localhost


    DocumentRoot /var/www/stackr.test/public/

#    <Directory /var/www/stackr.test/public/>
#        Options Indexes FollowSymLinks MultiViews
#        AllowOverride All
#        Require all granted
#    </Directory>

    # Available loglevels: trace8, ..., trace1, debug, info, notice, warn,
    # error, crit, alert, emerg.
    # It is also possible to configure the loglevel for particular
    # modules, e.g.
    #LogLevel info ssl:warn

    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```
```shell
sudo a2ensite stackr.test.conf
```
Copy `.htaccess` from `public` to `/var/www/stackr.test/public`
Set permissions

`.htaccess` in `var/www/stackr.test`
```
RewriteEngine On
RewriteBase /My-Project/

RewriteCond %{THE_REQUEST} /public/([^\s?]*) [NC]
RewriteRule ^ %1 [L,NE,R=302]

RewriteRule ^((?!public/).*)$ public/$1 [L,NC]
```
Set `public/.htaccess` permissions and ownership
```shell
sudo chown www-data:www-data .htaccess
chmod 644 .htaccess
```
And set `public/index.php` permissions and ownership
```shell
sudo chown www-data:www-data index.php
sudo chmod 644 index.php
```
You may get an error about `Invalid command: RewriteEngine`.
So you need.
```shell
sudo a2enmod rewrite
```
Which apparently is the same as:
```shell
? ln -s /etc/apache2/mods-available/rewrite.load /etc/apache2/mods-enabled/rewrite.load
```

May not need this.
```shell
install mod_rewrite module

systemctl restart apache2

sudo service apache2 reload
```

## 6. Build resources

The resources folder contains custom resources for this stack.
```shell
mkdir resources
```
Add in agent resources as available.

## 7. Verify localhost serving to local-wide TCP/IP
```shell
php -S localhost:8080 -t public public/index.php
```
## 8. Verify Ping, Latency

In `/var/www/stackr.test/`:
```shell
./agent ping
./agent latency
```

## 9. Verify Roll PNG



## 10. Install Gearman.

---
```shell
sudo nano vendor/nrwtaylor/stack-agent-thing/src/worker.php
```
Update require path.


---

### Install Gearman
http://gearman.org/

- gearmand-1.1.18.tar.gz
- https://github.com/gearman/gearmand/releases/download/1.1.18/gearmand-1.1.18.tar.gz

---

### 10 (cont.) More on installing Gearman
https://gist.github.com/himelnagrana/9758209
```shell
sudo apt-get update
sudo apt-get upgrade
sudo apt-get install gcc autoconf bison flex libtool make libboost-all-dev libcurl4-openssl-dev curl libevent-dev uuid-dev
cd ~
wget https://github.com/gearman/gearmand/releases/download/1.1.18/gearmand-1.1.18.tar.gz
tar -xvf gearmand-1.1.18.tar.gz
cd gearmand-1.1.18

sudo apt-get install gperf

./configure
sudo make
```
this takes a while and throws a lot of output
```shell
sudo make install
sudo apt-get install gearman-job-server

sudo apt-get install php-pear
sudo pecl install gearman
sudo nano /etc/php5/conf.d/gearman.ini 
```
and then write `extension=gearman.so` as content of the file, save it and close it

```shell
sudo service apache2 restart
```
perhaps just
```shell
sudo apt-get install php-gearman
```
Test with gearman scripts

--

Lots of gearman stuff follows because gearman is tricky to get up and running.

--
```shell
sudo apt install gearman-tools
```
--

Needed 
```shell
apt-get install gperf
```
Then `./configure` etc.
```shell
sudo pecl channel-update pecl.php.net
```
--

https://hasin.me/2013/10/30/installing-gearmand-libgearman-and-pecl-gearman-from-source/

<!--[sourcecode language=”shell”]-->
```shell
wget https://launchpad.net/gearmand/1.2/1.1.11/+download/gearmand-1.1.11.tar.gz
tar -zxvf gearmand-1.1.11.tar.gz
cd gearmand-1.1.11
./configure
```
<!--[/sourcecode]-->

### Failure 1:
At this step, configure stopped showing the following error

<!--[sourcecode language=”shell”]-->
```shell
configure: error: cannot find Boost headers version >= 1.39.0
```
<!--[/sourcecode]-->

To fix this, I had to install `libboost-all-dev` by following command
<!--sourcecode language=”shell”-->
```shell
apt-get install libboost-all-dev
```
<!--/sourcecode-->

And I tried to compile gearman and it failed again

### Failure 2:
At this step, configure stopped showing that it cannot find gperf. That’s fine – I have installed gperf and tried to configure gearman again

<!--[sourcecode language=”shell”]--->
```shell
apt-get install gperf
```
<!--[/sourcecode]-->

### Failure 3:
Now it failed again, showing that `libevent` is missing. Hmm! Had to fix it anyway

<!--[sourcecode language=”shell”]-->
```shell
apt-get install libevent-dev
```
<!--[/sourcecode]-->

### Failure 4:
Heck! Another failure. Now it’s showing that it can’t find `libuuid`. This part was a little tricky to solve, but finally fixed with the following package

<!--[sourcecode language=”shell”]-->
```shell
apt-get install uuid-dev
```
<!--[/sourcecode]-->

Let’s configure again. And sweet that the configure script ran smoothly. Let’s compile using `make`

### Failure 5:
Grrr! At this point the make script failed with a bunch of text, where the following lines were at the top

<!--[sourcecode language=”shell”]-->
```shell
libgearman/backtrace.cc: In function ‘void custom_backtrace()’:
libgearman/backtrace.cc:64:6: sorry, unimplemented: Graphite loop optimizations can only be used if the libcloog-ppl0 package is installed
```
<!--[/sourcecode]-->

So it cannot find a library named `libcloog-ppl`. Let’s fix this problem by

<!--[sourcecode language=”shell”]-->
```shell
apt-get install libcloog-ppl-dev
```
<!--[/sourcecode]-->

Now I’ve tried to run the make script, and it was good. So i also ran `make install` to complete the installation.

<!--[sourcecode language=”shell”]-->
```shell
make
make install
```
<!--[/sourcecode]-->

Now gearmand and libgearman both are installed. So I tried to install `pecl-gearman` with the following extension and voila! it worked. No more missing libgearman anymore.

<!--[sourcecode language=”shell”]-->
```shell
pecl install gearman
```
<!--[/sourcecode]-->

Now all I had to do is add the line `extension=gearman.so` in my `php.ini`.

The process was tedious and boring and took me more time than writing this article. If you have seen “Despicable Me 2” when Lucy and Gru went to ElMacho’s restaurant and were attacked by that crazy chicken and finally Lucy exclaimed “What’s wrong with that chicken!”

I really wanted to say “What’s wrong with this chicken” after gearman was installed at last.

Enjoy!

--

https://www.techearl.com/php/installing-gearman-module-for-php7-on-ubuntu

```shell
apt-get install php-dev #phpize not in ubuntu standard

cd /tmp/
sudo wget https://github.com/wcgallego/pecl-gearman/archive/master.zip
unzip master.zip
cd pecl-gearman-master
sudo phpize
./configure
sudo make
sudo make install
echo "extension=gearman.so" | sudo tee /etc/php/7.1/mods-available/gearman.ini
sudo phpenmod -v ALL -s ALL gearman
```
---

### Another way the gearman install can go awry.

`PHP Startup: Unable to load dynamic library 'gearman.so'`  
https://stackoverflow.com/questions/36423929/gearman-is-missing-from-your-system


## 10 (cont.) Installing Supervisor for Gearman

Install Supervisor  
http://masnun.com/2011/11/02/gearman-php-and-supervisor-processing-background-jobs-with-sanity.html


```shell
#sudo apt-get install python-setuptools
#sudo easy_install supervisor
```
https://code.tutsplus.com/tutorials/making-things-faster-with-gearman-and-supervisor--cms-29337

```shell
sudo apt-get install supervisor
sudo nano /etc/supervisor/conf.d/supervisor.conf
```

[program:gearman-worker]
```
command=php /var/www/stackr.test/vendor/nrwtaylor/stack-agent-thing/src/worker.php
autostart=true
autorestart=false
numprocs=3
process_name=gearman-worker-%(process_num)s
```
```shell
sudo supervisorctl reload
```
--

http://supervisord.org/configuration.html#supervisord-section-values
```
supervisord.conf
add to supervisord section
minfds = 10000
```
--

Helpful
http://nileshzemase.blogspot.com/2013/07/gearman-and-supervisor-to-run-multiple.html

---

Change `php/ini`  
`/etc/php7.1/apache2` and  
`/etc/php/7.1/cli$ php.ini`
```
extension=gearman.so
```
(No apparent effect)

---

Running multiple supervisor workers  
http://nileshzemase.blogspot.ca/2013/07/gearman-and-supervisor-to-run-multiple.html

-- 

Remove Namespace from `worker.php` file[check?]
```shell
sudo apt-get install php7.1-fpm
```
--


http://www.hostingadvice.com/how-to/install-gearman-ubuntu-14-04/
```shell
sudo apt-get install software-properties-common
sudo add-apt-repository ppa:gearman-developers/ppa
sudo apt-get update

sudo apt-get install gearman-job-server libgearman-dev
sudo apt-get upgrade
```

## 11. Set-up cron
```shell
sudo crontab -e
```
```
* * * * * cd /var/www/stackr.test && /usr/bin/php -q /var/www/stackr.test/vendor/nrwtaylor/stack-agent-thing/agents/Cron.php >/dev/null 2>&1
```
```shell
sudo nano /var/www/stackr.test/vendor/nrwtaylor/stack-agent-thing/agents/Cron.php
```
Correct require line for the current environment.
```shell
sudo nano /var/www/stackr.test/vendor/nrwtaylor/stack-agent-thing/agents/Tick.php
```
Remove forward slash from /Gearman in line 62.

## 12. Verify Snowflake (web, PNG, PDF)

Run each as a test:
```shell
agent web
agent png
agent pdf
```


---

### Templates control how reports look

Add template files ... there are sample index, thing (and eventually email) templates in there.
```shell
cd /var/www/stackr.test
cp -r /var/www/stackr.test/vendor/nrwtaylor/stackr/templates templates
```
Or make your own.

`Thing` takes the `$thing_report` and displays it.
`Index` is a standalone non db page.  With no `thing` access


---

### Resource files form what they system "knows"

Copy in resource files ... there are sample resource files for 
some of the agents provided.
```shell
cd /var/www/stackr.test  
cp -r /var/www/stackr.test/vendor/nrwtaylor/stackr/resources resources
```

---

### Adding a `cron` job gives the system an idea of passing time
 
Get the Clock ticking
```shell
sudo crontab -e
```
Copy and paste this in as the last line.
```
* * * * * cd /var/www/stackr.test && /usr/bin/php -q /var/www/stackr.test/vendor/nrwtaylor/stack-agent-thing/agents/Cron.php >/dev/null 2>&1
```

Watch the database for Cron things.
And then check the error logs :/
```shell
grep CRON /var/log/syslog
```
If you run into trouble, test this bit out.  For the correct absolute paths.
Test this bit
```shell
/usr/bin/php -q /var/www/stackr.test/vendor/nrwtaylor/stack-agent-thing/agents/Cron.php
```
Once ticking, you'll see a `cron` tick every 60s in the database.

### Solve problems installing MYSQL

#### MySQL Problem #1
Increase Max connections  
https://www.rfc3092.net/2017/06/mysql-max_connections-limited-to-214-on-ubuntu-foo/
```shell
sudo nano /etc/mysql/mysqld.cnf
```
```
max_connection = 1000
```
**Posted on June 13, 2017 by peter**  
> MySQL max_connections limited to 214 on Ubuntu Foo

After moving a server to a new machine with Ubuntu 16.10, I received some strange Postfix SMTP errors. Which turned out to be a connection issue to the MySQL server:
```
postfix/cleanup[30475]: warning: connect to mysql server 127.0.0.1: Too many connections
```
Oops, did I forgot to up `max_connections` during the migration:
```shell
# grep max_connections /etc/mysql/mysql.conf.d/mysqld.cnf
```
```
max_connections = 8000
```
Nope, I didn’t. Did we all of a sudden have a surge in clients accessing the database. Let me check and ask MySQL, and the process list looked fine. But something was off. So let’s check the value in the SQL server itself:
```
mysql> show variables like 'max_connections';
+-----------------+-------+
| Variable_name | Value |
+-----------------+-------+
| max_connections | 214 |
+-----------------+-------+
1 row in set (0.01 sec)
```
Wait, what?! A look into the error log gave the same result:
```shell
# grep max_connections /var/log/mysql/error.log
```
```
2017-06-14T01:23:29.804684Z 0 [Warning] Changed limits: max_connections: 214 (requested 8000)
```
Something is off here and ye olde oracle Google has quite some hits on that topic. And the problem lies with the maximum allowed number of open files. You can’t have more connections, than open files. Makes sense. Some people suggest to solve it using `/etc/security/limits.conf` to fix it. Which is not so simple on Ubuntu anymore, because you have to first enable `pam_limits.so`. And even then it doesn’t work, because since Ubuntu is using systemd (15.04 if I am not mistaken) this configuration is only valid for user sessions and not services/demons.

So let’s solve it using systemd’s settings to allow for more connections/open files. First you have to copy the configuration file, so that you can make the changes we need:
```shell
cp /lib/systemd/system/mysql.service /etc/systemd/system/
```
Append the following lines to the new file using `vi` (or whatever editor you want to use):
```shell
vi /etc/systemd/system/mysql.service
```
```
LimitNOFILE=infinity
LimitMEMLOCK=infinity
```

Reload `systemd`:
```shell
systemctl daemon-reload
```
After restarting MySQL it was finally obeying the setting:
```
mysql> show variables like 'max_connections';
```
--

`my.cnf` - change this to avoid long queries every so often
```
#
# * Query Cache Configuration
#/
query_cache_limit   = 1M
# 19 June 2018 query_cache_size        = 16M
query_cache_size        = 0
# added this
query_cache_type = 0
```

---

### Give the system a way to answer: INSTALL POSTFIX

Test connection with.
```shell
sudo apt-get install mailutils
echo "test winlink message 2" | mailx -s 'TEST whiskey india november lima india november kilo' < example email address >

sudo apt-get update
sudo DEBIAN-PRIORITY=low apt-get install postfix
sudo dpkg-reconfigure postfix
```
```shell
cd /etc/postfix
sudo nano master.cf
```
```
mytransportname   unix  -       n       n       -       -       pipe
  flags=FR user=<username> argv=<path>/src/emailhandler.php
  ${nexthop} ${user}
```
To check status
```shell
sudo postfix status
```

---

### Fix PhpSerial
```php
        //https://www.phpclasses.org/discuss/package/3679/thread/13/
        //    "custom"   => "-brkint -icrnl -imaxbel -opost -isig -icanon -iexten -echo",
        // "custom" => "ignbrk -brkint -icrnl -imaxbel -opost -onlcr -isig -icanon -iexten -echo -echoe -echok -echoctl -echoke raw",

// last working         "custom"   => "-brkint -icrnl -imaxbel -opost -isig -icanon -iexten -echo",


        $linuxModes = array(
            "custom" => "ignbrk -brkint -icrnl -imaxbel -opost -onlcr -isig -icanon -iexten -echo -echoe -echok -echoctl -echoke raw",
            "none"     => "clocal -crtscts -ixon -ixoff",
            "rts/cts"  => "-clocal crtscts -ixon -ixoff",
            "xon/xoff" => "-clocal -crtscts ixon ixoff"
        );
        $windowsModes = array(
            "none"     => "xon=off octs=off rts=on",
            "rts/cts"  => "xon=off octs=on rts=hs",
            "xon/xoff" => "xon=on octs=off rts=on",
        );

        if ($mode !== "custom"  and $mode !== "none" and $mode !== "rts/cts" and $mode !== "xon/xoff") {
            trigger_error("Invalid flow control mode specified", E_USER_ERROR);
```
---

### Configure nano to make editing easier

Set `nano` to 4 space indenting
```shell
sudo nano /etc/nanorc (others?)
```
```
set tabsize 4
```
Convert typed tabs to spaces.
```
set tabstospaces
```
Ctrl-3,Shift-3
: a way to show linke numbers in `nano`

--

#### MySQL Problem 2

Why is the `ibdata1` file continuously growing in MySQL

https://www.percona.com/blog/2013/08/20/why-is-the-ibdata1-file-continuously-growing-in-mysql/

 The only way is to start the database with fresh `ibdata1`. To do that you would need to take a full logical backup with `mysqldump`. Then stop MySQL and remove all the databases, `ib_logfile`* and `ibdata`* files. When you start MySQL again it will create a new fresh shared tablespace. Then, recover the logical dump.
```shell
mysqldump project_stack_dev_db -u root -p | gzip -c | ssh nick@ash "cat > /home/nick/snapshots/project_stack_dev_db_snapshot_2019-21-01.sql"
```
Which created at 31 Mb file.
```shell
sudo apt install ncdu
```
And work through removing files.

#

### Enable Soap
```shell
# /etc/php5/apache2/php.ini
# sudo apt-get install php7.3-soap
ln -s /etc/apache2/mods-available/rewrite.load /etc/apache2/mods-enabled/rewrite.load
# /etc/php/7.3/apache/php.ini
# uncomment ;extension=soap
```
Check `phpinfo`.
```
/etc/php/7.2/apache2/php.ini
```
uncomment `;extension=soap`
```shell
sudo apt-get install php7.2-soap
sudo service apache2 restart
```
### Install bcmath extension
```shell
#sudo apt install yu
#yum install php-bcmath
```
https://ourcodeworld.com/articles/read/679/how-to-solve-the-requested-php-extension-bcmath-is-missing-from-your-system-when-installing-a-library-via-composer-in-ubuntu-16-04
```shell
sudo apt install php7.3-bcmath
```

## useful commands

Some stuff to get the stack whirring.

For the mb string functions
```shell
sudo apt-get install php-mbstring
```


## 13. install composer
13 (cont). Get composer dependencies

**composer.json**
```json
{
    "name": "test project",
    "description": "A PHP CLI agent to communicate with the PHP stack-agent-thing framework.",
    "require": {
        "slim/slim": "^3.0",
        "slim/php-view": "*",
        "ramsey/uuid": "^3.7",
        "monolog/monolog": "^1.23",
        "hyperthese/php-serial": "*",
        "linkorb/tty": "dev-master",
        "nrwtaylor/stack-agent-thing": "*",
        "setasign/fpdi-fpdf": "^2.0",
        "monolog/monolog": ">=1.10",
        "endroid/qr-code": "^3.2",
        "ekinhbayar/brill-tagger": "^0.3.1",
        "vanderlee/syllable": "^1.5",
        "vanderlee/php-sentence": "^1.0",
        "webignition/robots-txt-file": "^2.1",
        "symfony/console": "^4.3"
        "johngrogg/ics-parser": "^2.2",
        "zbateson/mail-mime-parser": "^1.3"
    }
}
```

## 15. Syllables

**Use the vanderlee composer package.**  
You will need to get the .tex file which has Thomas Kroll's name on it.
Otherwise was you will get these errors.
```
APACHE > PHP Warning:  file(/var/www/html/stackr.ca/vendor/vanderlee/syllable/languages/hyph-en-ca.tex): failed to open stream: No such file or directory in /var/www/stackr.test/vendor/vanderlee/syllable/src/Source/File.php on line 59
```

## 16. Memcached (optional)

To speed up agents that refer to large lookup files.  

```shell
sudo apt-get update
sudo apt-get install memcached
```
Memcached will speed up agents which call on it to store large text lookup files locally.
```shell
sudo apt-get install -y php-memcached
```
--

PHP-FPM

```shell
sudo apt-get update
sudo apt-get install php-fpm
```
