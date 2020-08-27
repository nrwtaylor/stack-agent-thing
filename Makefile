SHELL=/bin/bash
SERVERNAME=stackr.test
mpm-servers=4
mpm-spares-min=3
mpm-spares-max=40
mpm-workers-max=200
mpm-child-cnxns=10000
YOUR_EMAIL=myaddress@example.com
MYSQLPASSWORD=Stack_1user
AGENT_LOCATION=../agent

.PHONY: help
help: ## Show this help
	@egrep -h '\s##\s' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

all: init lamp mysql php apachefiling agent resources gearman supervisor cron tailoring memcached ## Do everything, in order

init:  ## Update system
	@echo "===== Updating System ==============="
	sudo -- bash -c 'apt-get update; apt-get --assume-yes upgrade'

lamp:  ## Install LAMP stack
	@echo "===== Installing LAMP stack ==============="
	sudo apt --assume-yes install apache2
	-sudo apt --assume-yes install mysql-server
	sudo apt --assume-yes install php7.2 libapache2-mod-php php-mysql
	sudo apt --assume-yes install php-curl php-json php-cgi
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

mysql: ## Set up MySQL
	@echo "===== Setting up MySQL ==============="
	-mysql -u root -p -e "CREATE USER 'stackuser'@'%' IDENTIFIED BY '$(MYSQLPASSWORD)'" || (@echo "Could not create stackuser $$?";)
#ifeq ("$$?", 0)
#	@echo "ok - $$?"
#else
#	@echo "not ok - $$?"
#endif
	# stackuser setup and passwords need improvement
	@echo "----- -- Set up Stack DB MySQL user: ---------------"
	-mysql -u root -p -e "GRANT ALL PRIVILEGES ON *.* TO 'stackuser'@'%' WITH GRANT OPTION" || (@echo "Could not grant permissions to stackuser $$?";)
	-mysql -u stackuser --password=$(MYSQLPASSWORD) -e "CREATE DATABASE stack_db" || (@echo "Could not create database $$?";)
	-mysql -u stackuser --password=$(MYSQLPASSWORD) stack_db < templates/database_schema.sql || (@echo "Could not add schemas $$?";)

# innodb:
# innodb performance settings

php: ## Set up PHP
	@echo "===== Setting up PHP ==============="
	sudo apt-get --assume-yes install -f php-mbstring
	sudo apt-get --assume-yes install -f php7.2-xml
	sudo apt-get --assume-yes install -f php-intl
	sudo apt-get --assume-yes install -f php7.2-bcmath
	#	sudo apt install php7.0-gd
	sudo apt-get --assume-yes install -f php7.2-gd
	sudo apt-get --assume-yes  install -f php-curl
	sudo apt-get --assume-yes install -f php-fpm
	sudo service apache2 restart

apachefiling: ## Create and assemble filing for Apache2 server
	@echo "===== Creating filesystem for Apache2 server ==============="
	sudo mkdir /var/www/$(SERVERNAME); \
	case "$$?" in \
	esac; \
	# establish server file area - as root vs as user?
	cd /var/www/$(SERVERNAME); \
	sudo usermod -a -G www-data `whoami`; \
	sudo chown root:root /var/www; \
	sudo chmod 755 /var/www/; \
	sudo chown -R www-data:www-data /var/www/$(SERVERNAME); \
	sudo chmod -R 774 /var/www/$(SERVERNAME); \
	wget https://raw.githubusercontent.com/nrwtaylor/agent/master/composer.json
	cd /var/www/$(SERVERNAME); \
	sudo apt-get --assume-yes install composer; composer install	
	sudo cp -r /var/www/$(SERVERNAME)/vendor/nrwtaylor/stack-agent-thing/public /var/www/$(SERVERNAME)/public/; \
	sudo cp -r /var/www/$(SERVERNAME)/vendor/nrwtaylor/stack-agent-thing/private /var/www/$(SERVERNAME)/private/
	# sudo cp -r . /var/www/$(SERVERNAME)

agent: ## Add commandline shell interface to call Stackr
	cd /var/www/$(SERVERNAME); \
	wget https://raw.githubusercontent.com/nrwtaylor/agent/master/agent; \
	touch agent; \
	chmod u+x agent

#agent:  $(AGENT_LOCATION)/agent ## Add commandline shell interface to call Stackr
#ifneq ("$(wildcard $(AGENT_LOCATION))","")	
#FILE_EXISTS = 1
#else
#FILE_EXISTS = 0
#endif
#ifeq ("$(FILE_EXISTS)","0")
#	mkdir $(AGENT_LOCATION)
#endif
#	sudo -- bash -c "touch $(AGENT_LOCATION)/agent; chmod +X $(AGENT_LOCATION)/agent"
#	get PATH and add $AGENT_LOCATION to the PATH:
#	https://unix.stackexchange.com/questions/11530/adding-directory-to-path-through-makefile
#	not possible from within make to get persistence

resources: ## Set up resources
	@echo "===== Setting up resources ==============="
	sudo cp -r /var/www/$(SERVERNAME)/vendor/nrwtaylor/stack-agent-thing/resources /var/www/$(SERVERNAME)/resources/

gearman: ## Install Gearman
	@echo "===== Installing Gearman ==============="
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

supervisor: ## Install Supervisor
	@echo "===== Installing Supervisor ==============="
	sudo apt-get install supervisor; \
	sudo cp scripts/supervisor.conf /etc/supervisor/conf.d
	sudo sed 's/SERVERNAME/$(SERVERNAME)/g' /etc/supervisor/conf.d

cron: ## Set up scheduled events
	@echo "===== Setting up scheduled events (cron) ==============="
	line="* * * * * cd /var/www/$(SERVERNAME) && /usr/bin/php -q /var/www/$(SERVERNAME)/vendor/nrwtaylor/stack-agent-thing/agents/Cron.php >/dev/null 2>&1"; \
	(sudo crontab -u root -l; echo "$line" ) | sudo crontab -u root -

tailoring: ## Set your servername in system files
	@echo "===== Setting your server name in system files ==============="
	sudo sed -i 's/stackr.test/$(SERVERNAME)/g' /var/www/$(SERVERNAME)/vendor/nrwtaylor/stack-agent-thing/agents/Cron.php
	sudo sed -i 's/stackr.test/$(SERVERNAME)/g' /var/www/$(SERVERNAME)/vendor/nrwtaylor/stack-agent-thing/agents/Tick.php
	sudo sed -i 's/stackr.test/$(SERVERNAME)/g' /var/www/$(SERVERNAME)/vendor/nrwtaylor/stack-agent-thing/src/Thing.php
	sudo sed -i 's/stackr.test/$(SERVERNAME)/g' /var/www/$(SERVERNAME)/vendor/nrwtaylor/stack-agent-thing/src/worker.php
	sudo sed -i 's/stackr.test/$(SERVERNAME)/g' /var/www/$(SERVERNAME)/vendor/nrwtaylor/stack-agent-thing/agents/Emailhandler.php

#verify:

#postfix:

memcached: ## Install MemCache Daemon
	@echo "===== Installing MemCache Daemon =============="
	sudo apt-get update; sudo apt-get install memcached; \
	sudo apt-get install -y php-memcached

clean: ## Clean up the web folders and settings
	@echo "===== Cleaning up: removing web folders and settings ==============="
	rm -Rvf /var/www/$(SERVERNAME)
	rm -f /etc/apache2/sites-available/$(SERVERNAME).conf
	
#	rm -f /etc/apache2/sites-available/000-default.conf
#	rm -f apache settings for SERVERNAME
# perhaps also:  mysql? php?

patch: ## Activate a patch
	sudo sh scripts/patch_local.sh
