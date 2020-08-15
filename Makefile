SHELL=/bin/bash
SERVERNAME=stackr-make.test
mpm-servers=4
mpm-spares-min=3
mpm-spares-max=40
mpm-workers-max=200
mpm-child-cnxns=10000
YOUR_EMAIL=myaddress@example.com

all: lamp mysql php apachefiling resources gearman supervisor cron tailoring memcached

# remember to update your system

lamp:  
	sudo apt install apache2
	sudo apt install mysql-server
	sudo apt install php7.2 libapache2-mod-php php-mysql
	sudo apt install php-curl php-json php-cgi
# check the default state in apache2.conf
	sudo sed -i 's/^KeepAlive Off/KeepAlive On/g' /etc/apache2/apache2.conf
# write sed statement to insert mpm_prefork.conf values  -----!!!!
#	sudo sed -i '/?????????/$(mpm-servers)' /etc/apache2/mods-available/mpm_prefork.conf
#	sudo sed -i '/?????????/$(mpm-spares-min)' /etc/apache2/mods-available/mpm_prefork.conf
#	sudo sed -i '/?????????/$(mpm-spares-max)' /etc/apache2/mods-available/mpm_prefork.conf
#	sudo sed -i '/?????????/$(mpm-workers-max)' /etc/apache2/mods-available/mpm_prefork.conf
#	sudo sed -i '/?????????/$(mpm-child-cnxns)' /etc/apache2/mods-available/mpm_prefork.conf
	sudo ufw allow in "Apache Full"
	sudo a2dismod mpm_event; \
	sudo a2enmod mpm_prefork; \
	sudo systemctl restart apache2
#	sudo cp /etc/apache2/sites-available/000-default.conf /etc/apache2/sites-available/$(SERVERNAME).conf
# write sed rule to edit configuration file
#	sudo sed -i 's/example.com/$(SERVERNAME)/g' /etc/apache2/sites-available/$(SERVERNAME).conf
#	sudo cp scripts/000-default.conf /etc/apache2/sites-available; \
#	sudo sed -i 's/SERVERNAME/$(SERVERNAME)/g' /etc/apache2/sites-available/000-default.conf; \
#	sudo sed -i 's/YOUR_EMAIL/$(YOUR_EMAIL)/g' /etc/apache2/sites-available/000-default.conf
	sudo cp scripts/stackr.test.conf /etc/apache2/sites-available/$(SERVERNAME).conf; \
	sudo sed -i 's/SERVERNAME/$(SERVERNAME)/g' /etc/apache2/sites-available/$(SERVERNAME).conf; \
	sudo sed -i 's/YOUR_EMAIL/$(YOUR_EMAIL)/g' /etc/apache2/sites-available/$(SERVERNAME).conf
	sudo mkdir -p /var/www/$(SERVERNAME)/{public_html,logs}
	sudo chown root:root /var/www
	sudo chmod 755 /var/www/
	sudo chown -R www-data:www-data /var/www/$(SERVERNAME)
	sudo chmod -R 774 /var/www/$(SERVERNAME)
	sudo a2ensite $(SERVERNAME).conf
	sudo cp scripts/.htaccess /var/www/$(SERVERNAME)
	install mod_rewrite module; sudo a2enmod rewrite; \
	sudo service apache2 reload
#	sudo a2dissite 000-default.conf
#	sudo systemctl reload apache2
	
mysql: 
	mysql -u root -p -e "CREATE USER 'stackuser'@'%' IDENTIFIED BY 'stackuser'"
	mysql -u root -p -e "GRANT ALL PRIVILEGES ON *.* TO 'stackuser'@'%' WITH GRANT OPTION"
	mysql -u stackuser -p -e "CREATE DATABASE stack_db"
	mysql -u stackuser -p stack_db < templates/database_schema.sql
	
# innodb:
# innodb performance settings

php: 
	sudo apt-get update
	sudo apt-get install php-mbstring
	sudo apt-get install php7.2-xml
	sudo apt-get install php-intl
	sudo apt install php7.2-bcmath
#	sudo apt install php7.0-gd
	sudo apt-get install php7.2-gd
	sudo apt-get install php-curl
	sudo apt-get install php-fpm
	sudo service apache2 restart

apachefiling:
	mkdir /var/www/$(SERVERNAME)
	# establish server file area
	cd /var/www/$(SERVERNAME); \
	sudo usermod -a -G www-data `whoami`; \
	sudo chown root:root /var/www; \
	sudo chmod 755 /var/www/; \
	sudo chown -R www-data:www-data /var/www/$(SERVERNAME); \
	sudo chmod -R 774 /var/www/$(SERVERNAME); \
	wget https://raw.githubusercontent.com/nrwtaylor/stack-agent-thing/master/composer.json; \
	sudo apt install composer; composer install
	cp -r /var/www/$(SERVERNAME)/vendor/nrwtaylor/stack-agent-thing/public /var/www/$(SERVERNAME)/public/; \
	cp -r /var/www/$(SERVERNAME)/vendor/nrwtaylor/stack-agent-thing/private /var/www/$(SERVERNAME)/private/

resources:
	cp -r /var/www/$(SERVERNAME)/vendor/nrwtaylor/stack-agent-thing/resources /var/www/$(SERVERNAME)/resources/

gearman:
	sudo apt-get install php-gearman
	sudo apt install gearman-tools
#replaces:
#	sudo -- bash -c 'apt update; apt upgrade'
#	sudo apt-get install gcc autoconf bison flex libtool make libboost-all-dev libcurl4-openssl-dev curl libevent-dev uuid-dev
#	cd ~; wget https://github.com/gearman/gearmand/releases/download/1.1.18/gearmand-1.1.18.tar.gz; tar -xvf gearmand-1.1.18.tar.gz; \
#	cd gearmand-1.1.18; sudo apt-get install gperf; \
#	./configure
#	sudo make  # this takes a while and throws a lot of output
#	sudo make install
#	sudo apt-get install gearman-job-server
#	sudo apt-get install php-pear
#	sudo pecl install gearman
#	sudo nano /etc/php5/conf.d/gearman.ini #[and then write extension=gearman.so as content of the file, save it and close it]
	sudo service apache2 restart

supervisor:
	sudo apt-get install supervisor; \
	sudo cp scripts/supervisor.conf /etc/supervisor/conf.d
	sudo sed 's/SERVERNAME/$(SERVERNAME)/g' /etc/supervisor/conf.d
	
cron:
	line="* * * * * cd /var/www/$(SERVERNAME) && /usr/bin/php -q /var/www/$(SERVERNAME)/vendor/nrwtaylor/stack-agent-thing/agents/Cron.php >/dev/null 2>&1"; \
	(sudo crontab -u root -l; echo "$line" ) | sudo crontab -u root -

tailoring:
	sudo sed -i 's/stackr.test/$(SERVERNAME)/g' /var/www/$(SERVERNAME)/vendor/nrwtaylor/stack-agent-thing/agents/Cron.php
	sudo sed -i 's/stackr.test/$(SERVERNAME)/g' /var/www/$(SERVERNAME)/vendor/nrwtaylor/stack-agent-thing/agents/Tick.php
	sudo sed -i 's/stackr.test/$(SERVERNAME)/g' /var/www/$(SERVERNAME)/vendor/nrwtaylor/stack-agent-thing/src/Thing.php
	sudo sed -i 's/stackr.test/$(SERVERNAME)/g' /var/www/$(SERVERNAME)/vendor/nrwtaylor/stack-agent-thing/src/worker.php
	
#verify:

#postfix:

memcached:
	sudo apt-get update; sudo apt-get install memcached; \
	sudo apt-get install -y php-memcached

clean:
	rm -Rvf /var/www/$(SERVERNAME)
	rm -f /etc/apache2/sites-available/$(SERVERNAME).conf
#	rm -f /etc/apache2/sites-available/000-default.conf
# perhaps also:  mysql? php? 

