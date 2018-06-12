(c) 2018. NRW Taylor

These are the current build instructions as of May 2018.

1. Install Ubuntu latest.
2. Install LAMP stack.

3. Set-up InnoDB.

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


4. Verify localhost serving to local-wide TCP/IP

php -S localhost:8080 -t public public/index.php

5. Verify Ping, Latency
6. Verify Roll PNG

7. Install PHP extensions
sudo apt install php7.1-bcmath
sudo apt-get install php-bcmath
restart.

and curl
sudo apt-get install php-curl
sudo service apache2 restart

8. Set-up cron

* * * * * cd /var/www/stackr.test && /usr/bin/php -q /var/www/stackr.test/agents/Cron.php >/dev/null 2>&1

9. Verify Snowflake (web, PNG, PDF) - Failing

Some stuff to get the stack whirring.

For the mb string functions
sudo apt-get install php-mbstring

http://www.hostingadvice.com/how-to/install-gearman-ubuntu-14-04/

sudo apt-get install software-properties-common
sudo add-apt-repository ppa:gearman-developers/ppa
sudo apt-get update

sudo apt-get install gearman-job-server libgearman-dev
sudo apt-get upgrade

10. Install Gearman.

---
Folder Permissions

https://askubuntu.com/questions/767504/permissions-problems-with-var-www-html-and-my-own-home-directory-for-a-website

---
Install Gearman
https://gist.github.com/himelnagrana/9758209

sudo apt-get update
sudo apt-get upgrade
sudo apt-get install gcc autoconf bison flex libtool make libboost-all-dev libcurl4-openssl-dev curl libevent-dev uuid-dev
cd ~
wget https://launchpad.net/gearmand/1.2/1.1.12/+download/gearmand-1.1.12.tar.gz
tar -xvf gearmand-1.1.12.tar.gz
cd gearmand-1.1.12
./configure
sudo make
sudo make install
sudo apt-get install gearman-job-server
sudo pecl install gearman
sudo nano /etc/php5/conf.d/gearman.ini [and then write extension=gearman.so as content of the file, save it and close it]
sudo service apache2 restart

-

sudo apt install gearman-tools

-

Needed apt-get install gperf

Then ./configure etc.

sudo pecl channel-update pecl.php.net

-
https://hasin.me/2013/10/30/installing-gearmand-libgearman-and-pecl-gearman-from-source/

[sourcecode language=”shell”]
wget https://launchpad.net/gearmand/1.2/1.1.11/+download/gearmand-1.1.11.tar.gz
tar -zxvf gearmand-1.1.11.tar.gz
cd gearmand-1.1.11
./configure
[/sourcecode]

Failure 1:
At this step, configure stopped showing the following error

[sourcecode language=”shell”]
configure: error: cannot find Boost headers version >= 1.39.0
[/sourcecode]

To fix this, I had to install “libboost-all-dev” by following command
[sourcecode language=”shell”]
apt-get install libboost-all-dev
[/sourcecode]

And I tried to compile gearman and it failed again

Failure 2:
At this step, configure stopped showing that it cannot find gperf. That’s fine – I have installed gperf and tried to configure gearman again

[sourcecode language=”shell”]
apt-get install gperf
[/sourcecode]

Failure 3:
Now it failed again, showing that libevent is missing. Hmm! Had to fix it anyway

[sourcecode language=”shell”]
apt-get install libevent-dev
[/sourcecode]

Failure 4:
Heck! Another failure. Now it’s showing that it can’t find libuuid. This part was a little tricky to solve, but finally fixed with the following package

[sourcecode language=”shell”]
apt-get install uuid-dev
[/sourcecode]

Let’s configure again. And sweet that the configure script ran smoothly. Let’s compile using make

Failure 5:
Grrr! At this point the make script failed with a bunch of text, where the following lines were at the top

[sourcecode language=”shell”]
libgearman/backtrace.cc: In function ‘void custom_backtrace()’:
libgearman/backtrace.cc:64:6: sorry, unimplemented: Graphite loop optimizations can only be used if the libcloog-ppl0 package is installed
[/sourcecode]

So it cannot find a library named libcloog-ppl. Let’s fix this problem by

[sourcecode language=”shell”]
apt-get install libcloog-ppl-dev
[/sourcecode]

Now I’ve tried to run the make script, and it was good. So i also ran make install to complete the installation.

[sourcecode language=”shell”]
make
make install
[/sourcecode]

Now gearmand and libgearman both are installed. So I tried to install pecl-gearman with the following extension and voila! it worked. No more missing libgearman anymore.

[sourcecode language=”shell”]
pecl install gearman
[/sourcecode]

Now all I had to do is add the line “extension=gearman.so” in my php.ini .

The process was tedious and boring and took me more time than writing this article. If you have seen “Despicable Me 2” when Lucy and Gru went to ElMacho’s restaurant and were attacked by that crazy chicken and finally Lucy exclaimed “What’s wrong with that chicken!”

I really wanted to say “What’s wrong with this chicken” after gearman was installed at last.

Enjoy!

-
https://www.techearl.com/php/installing-gearman-module-for-php7-on-ubuntu

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

---
Install Supervisor
http://masnun.com/2011/11/02/gearman-php-and-supervisor-processing-background-jobs-with-sanity.html

#sudo apt-get install python-setuptools
#sudo easy_install supervisor

https://code.tutsplus.com/tutorials/making-things-faster-with-gearman-and-supervisor--cms-29337

sudo apt-get install supervisor
sudo nano /etc/supervisor/conf.d/supervisor.conf

[program:gearman-worker]
command=php /var/www/stackr.test/vendor/nrwtaylor/stack-agent-thing/src/worker.php
autostart=true
autorestart=true
numprocs=3
process_name=gearman-worker-%(process_num)s

sudo supervisorctl reload
---

Change php/ini
/etc/php7.1/apache2 and
/etc/php/7.1/cli$ php.ini
extension=gearman.so

(No apparent effect)

---
Running multiple supervisor workers
http://nileshzemase.blogspot.ca/2013/07/gearman-and-supervisor-to-run-multiple.html

- 

Remove Namespace from worker.php file[check?]

sudo apt-get install php7.1-fpm

-

https://stackoverflow.com/questions/23635746/htaccess-redirect-from-site-root-to-public-folder-hiding-public-in-url

Make .htaccess in stackr.test

user@server:/var/www/stackr.test$ 
sudo nano .htaccess

RewriteEngine On
RewriteBase /My-Project/

RewriteCond %{THE_REQUEST} /public/([^\s?]*) [NC]
RewriteRule ^ %1 [L,NE,R=302]

RewriteRule ^((?!public/).*)$ public/$1 [L,NC]

sudo service apache2 restart #urgh

---

Add template files ... there are sample index, thing (and eventually email)
templates in there.

cd /var/www/stackr.test
cp -r /var/www/stackr.test/vendor/nrwtaylor/stackr/templates templates

Or make your own.
Thing takes the $thing_report and display it.
Index is a standalone non db page.  With no thing access


---
Add resource files

Copy in resource files ... there are sample resource files for 
some of the agents provided.

cd /var/www/stackr.test  
cp -r /var/www/stackr.test/vendor/nrwtaylor/stackr/resources resources


--- 
Get the Clock ticking

sudo crontab -e

Copy and paste this in as the last line.
* * * * * cd /var/www/stackr.test && /usr/bin/php -q /var/www/stackr.test/vendor/nrwtaylor/stack-agent-thing/agents/Cron.php >/dev/null 2>&1

Watch the database for Cron things.
And then check the error logs :/
grep CRON /var/log/syslog

If you run into trouble, test this bit out.  For the correct absolute paths.
Test this bit
/usr/bin/php -q /var/www/stackr.test/vendor/nrwtaylor/stack-agent-thing/agents/Cron.php

Once ticking, you'll see a cron tick every 60s in the database.

Install MYSQL

Problem #1
Increase Max connections
https://www.rfc3092.net/2017/06/mysql-max_connections-limited-to-214-on-ubuntu-foo/


Posted on June 13, 2017 by peter
MySQL max_connections limited to 214 on Ubuntu Foo

After moving a server to a new machine with Ubuntu 16.10 I received some strange Postfix SMTP errors. Which turned out to be a connection issue to the MySQL server:

postfix/cleanup[30475]: warning: connect to mysql server 127.0.0.1: Too many connections

Oops, did I forgot to up max_connections during the migration:

# grep max_connections /etc/mysql/mysql.conf.d/mysqld.cnf
max_connections = 8000

Nope, I didn’t. Did we all of a sudden have a surge in clients accessing the database. Let me check and ask MySQL, and the process list looked fine. But something was off. So let’s check the value in the SQL server itself:

mysql> show variables like 'max_connections';
+-----------------+-------+
| Variable_name | Value |
+-----------------+-------+
| max_connections | 214 |
+-----------------+-------+
1 row in set (0.01 sec)

Wait, what?! A look into the error log gave the same result:

# grep max_connections /var/log/mysql/error.log
2017-06-14T01:23:29.804684Z 0 [Warning] Changed limits: max_connections: 214 (requested 8000)

Something is off here and ye olde oracle Google has quite some hits on that topic. And the problem lies with the maximum allowed number of open files. You can’t have more connections, than open files. Makes sense. Some people suggest to solve it using /etc/security/limits.conf to fix it. Which is not so simple on Ubuntu anymore, because you have to first enable pam_limits.so. And even then it doesn’t work, because since Ubuntu is using systemd (15.04 if I am not mistaken) this configuration is only valid for user sessions and not services/demons.

So let’s solve it using systemd’s settings to allow for more connections/open files. First you have to copy the configuration file, so that you can make the changes we need:

cp /lib/systemd/system/mysql.service /etc/systemd/system/

Append the following lines to the new file using vi (or whatever editor you want to use):

vi /etc/systemd/system/mysql.service

LimitNOFILE=infinity
LimitMEMLOCK=infinity

Reload systemd:

systemctl daemon-reload

After restarting MySQL it was finally obeying the setting:

mysql> show variables like 'max_connections';
