php artisan test
php -m | grep gd
app@54a74575c50b:~$ 
app@54a74575c50b:~$ docker compose build --no-cache php
docker compose up -d
exit
php artisan migrate
php artisan db:seed
exit
php artisan route:list | group trade
php artisan route:list | grep trade
exit
php artisan migrate:fresh --seed
exit
