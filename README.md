# mail-ocr git
git clone https://nadyaaputri/mail-ocr.git
cd mail
cp .env.example .env
composer install
npm install
php artisan key:generate
php artisan migrate   # kalau pakai DB lokal
php artisan storage:link
