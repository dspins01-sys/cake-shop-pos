ping wa-service
exit
apt-get update && apt-get install curl -y
exit
curl http://172.20.0.3:3000/health
exit
curl http://cake-wa:3000/health
curl http://wa-service:3000/health
exit
composer require laravel/horizon
php artisan horizon:install
php artisan migrate
php artisan migrate:status
exit
exit
php artisan tinker
php -m | grep redis
exit
php -m | grep redis
php artisan tinker
exit
php artisan config:clear
php artisan tinker
php -r "$r=new Redis(); $r->connect('redis',6379); echo 'OK';"
php -r '$r = new Redis(); $r->connect("redis", 6379); echo "OK\n";'
php artisan tinker
exit
php artisan config:clear
php artisan cache:clear
php artisan config:cache
exit
rm -f bootstrap/cache/config.php
rm -f bootstrap/cache/services.php
rm -f bootstrap/cache/packages.php
php artisan optimize:clear
php artisan config:clear
php artisan cache:clear
php artisan config:cache
php artisan tinker
exit
php artisan tinker
exit
php artisan tinker
exit
php artisan tinker
exit
php artisan tinker
php artisan queue:failed
php artisan queue:failed-table
php artisan migrate
php artisan tinker
exit
php artisan queue:failed
exit
php artisan config:clear
php artisan cache:clear
docker compose up -d horizon
exit
php queue_debug.php
pwd
exit
php queue_debug.php
exit
php queue_debug.php
php artisan make:command QueueDebug
exit
exit
