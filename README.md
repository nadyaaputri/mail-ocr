git clone https://github.com/Przmrn/mail.git
cd mail
cp .env.example .env
composer install
npm install
php artisan key:generate
php artisan migrate   # kalau pakai DB lokal
php artisan storage:link
