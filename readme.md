### Yazılım Kurulumları

~~~~
// Sunucumuzun güncelleyelim.
$ sudo apt-get update
$ sudo apt-get upgrade

$ sudo apt install software-properties-common

// Ondrej php paketini tanımlayalım.
$ sudo add-apt-repository ppa:ondrej/php
$ sudo apt-get -y update

// apache2, git, php 7.1, curl, postgresql, redis-server ve supervisor kurulumu
$ sudo apt-get -y install apache2 git php7.1 curl postgresql redis-server supervisor

// Sistem için gerekecek PHP alt kütüphanelerini kuralım ve apache için izin verelim.
$ sudo apt-get -y install php7.1-mbstring php7.1-curl php7.1-cli php7.1-gd php7.1-intl php7.1-tidy php7.1-xsl php7.1-zip php7.1-pgsql php-redis

$ sudo a2enmod rewrite php7.1
$ sudo service apache2 restart

// PHP versiyon paketleyicisi olan Composer'ı kuralım.
$ curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer
~~~~

### Yazılım Yapılandırmaları

~~~~
// Redis
$ sudo nano /etc/redis/redis.conf
maxmemory 2048mb
maxmemory-policy allkeys-lru

$ sudo systemctl restart redis-server.service
$ sudo systemctl enable redis-server.service

// PostgreSQL
$ sudo -u postgres psql

postgres=# \password
postgres=# (New Password)
postgres=# (New Password Repeat)
postgres=# \q

$ sudo nano /etc/postgresql/9.5/main/postgresql.conf
max_connections = 5000

$ sudo nano /etc/postgresql/9.5/main/pg_hba.conf
host    all             all              0.0.0.0/0                       md5
host    all             all              ::/0                            md5

~~~~

### Open SSL

~~~~

$ sudo apt-get update
$ sudo apt-get install openssl
$ openssl genrsa -out veri.zone.key 2048
$ openssl req -new -sha256 -key veri.zone.key -out veri.zone.csr

~~~~

### Java Kurulumu
~~~~
$ sudo add-apt-repository ppa:webupd8team/java
$ sudo apt update
$ sudo apt install oracle-java8-installer
$ javac -version
$ sudo apt install oracle-java8-set-default

$ JAVA_HOME="/usr/lib/jvm/java-8-oracle"
~~~~

### Elasticsearch Kurulumu
~~~~
$ wget -qO - https://artifacts.elastic.co/GPG-KEY-elasticsearch | sudo apt-key add -
$ echo "deb https://artifacts.elastic.co/packages/6.x/apt stable main" | sudo tee -a /etc/apt/sources.list.d/elastic-6.x.list

$ sudo apt update
$ sudo apt install elasticsearch -y
~~~~

### Sistemin Kurulumu Öncesi Yapılandırma

~~~~
$ mkdir /var/www/veri.zone
$ nano /etc/apache2/sites-available/veri.zone.conf
<VirtualHost *:80>
        ServerName veri.zone
        ServerAlias olive.veri.zone
        ServerAlias www.veri.zone

        ServerAdmin webmaster@localhost
        DocumentRoot /var/www/veri.zone/public

        ErrorLog ${APACHE_LOG_DIR}/error.log
        CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>

$ sudo a2ensite veri.zone.conf

$ nano /etc/apache2/apache2.conf
<Directory /var/www/>
        Options Indexes FollowSymLinks
        AllowOverride all 
        Require all granted
</Directory>

$ sudo service apache2 reload
~~~~

### Sistemin Kurulumu

~~~~
$ cd /var/www/
$ git clone https://www.github.com/qhudabaksh/olive
$ mv olive veri.zone
$ cd veri.zone
$ composer install
$ cp .env-example .env
$ php artisan key:generate
$ php artisan migrate --seed
$ php artisan storage:link
$ chmod 777 -R storage

// Kelimelerde güncelleme yapılırsa bu işlem tekrarlanmalı.
$ cp -R /var/www/veri.zone/database/analysis /etc/elasticsearch/analysis

// Otomatik işlemler için Supervisor yapılandırması.
$ nano /etc/supervisor/supervisord.conf

// [include] programının altına ekleyin.
files = /var/www/veri.zone/supervisor/olive-worker.conf

$ sudo supervisorctl reread
$ sudo supervisorctl reload
$ sudo supervisorctl update
$ sudo supervisorctl stop
$ sudo supervisorctl start
$ sudo supervisorctl restart

$ sudo supervisorctl start olive-trigger:*
$ sudo supervisorctl start olive-elasticsearch:*
$ sudo supervisorctl start olive-email:*
$ sudo supervisorctl start olive-crawler:*
~~~~

### Zamanlanmış Görevler Yapılandırması

~~~~
$ crontab -e
*/1 * * * * php /var/www/veri.zone/artisan schedule:run >> /dev/null 2>&1

@reboot nohup su - elasticsearch /data/elasticsearch-n1/bin/elasticsearch >> /data/elasticsearch-n1/logs/start.out 2>&1 &
~~~~
