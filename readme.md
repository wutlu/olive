### Yazılım Kurulumları

~~~~
// Sunucumuzun güncelleyelim.
$ sudo apt-get update
$ sudo apt-get upgrade


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
maxmemory 128mb
maxmemory-policy allkeys-lru

$ sudo systemctl restart redis-server.service
$ sudo systemctl enable redis-server.service

// PostgreSQL
$ sudo -u postgres psql

postgres=# \password
postgres=# (New Password)
postgres=# (New Password Repeat)
postgres=# \q
~~~~

### Java Kurulumu
sudo add-apt-repository ppa:webupd8team/java
sudo apt update
sudo apt install oracle-java8-installer
javac -version
sudo apt install oracle-java8-set-default

### Elasticsearch Kurulumu
wget -qO - https://artifacts.elastic.co/GPG-KEY-elasticsearch | sudo apt-key add -
echo "deb https://artifacts.elastic.co/packages/6.x/apt stable main" | sudo tee -a /etc/apt/sources.list.d/elastic-6.x.list

sudo apt update
sudo apt install elasticsearch -y

### Sistemin Kurulumu Öncesi Yapılandırma

~~~~
$ mkdir \var\www\olive.veri.zone
$ nano \etc\apache2\sites-available\olive.veri.zone.conf
<VirtualHost *:80>
    ServerAdmin root@veri.zone
    DocumentRoot /var/www/olive.veri.zone

    ServerName olive.veri.zone
#   ServerAlias www.olive.veri.zone

    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>

$ sudo a2ensite olive.veri.zone.conf
$ sudo service apache2 reload
~~~~

### Sistemin Kurulumu

~~~~
$ cd /var/www/
$ git clone https://www.github.com/4lper/olive
$ mv olive olive.veri.zone
$ cd olive.veri.zone
$ composer install
$ cp .env-example .env
$ php artisan key:generate
$ php artisan migrate --seed
$ php artisan storage:link

// Otomatik işlemler için Supervisor yapılandırması.
$ nano /etc/supervisor/supervisord.conf

// [include] programının altına ekleyin.
files = /var/www/olive.veri.zone/supervisor/olive-worker.conf

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
*/1 * * * * php /var/www/olive.veri.zone/artisan schedule:run >> /dev/null 2>&1
~~~~
