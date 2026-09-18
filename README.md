KLE Blog Monorepo
Bu proje, modern web mimarisi standartlarına uygun olarak geliştirilmiş modüler bir blog platformudur. Sistem, RESTful API mimarisi sunan Laravel 13 Backend servisi ve bu API'yi tüketen Livewire 4 tabanlı Frontend istemcisinden oluşmaktadır.

🛠 Teknoloji Yığını
Backend: Laravel 13 (^13.8), FilamentPHP v3 (Admin Paneli), Laravel Sanctum (API Token Auth), MySQL

Frontend: Laravel 13 (^13.8), Livewire v4 (^4.3), Tailwind CSS (Vite Entegrasyonu)

Konteynırlaştırma: Docker & Docker Compose (PHP 8.4, ext-zip aktif)

Hızlı Kurulum & Çalıştırma
Projenin her iki bileşeni de Dockerize edilmiştir. Temiz kurulum için bağımlılıklar, uygulama container'ı ayağa kalkmadan `docker compose run` ile kurulmalıdır.

1. Backend Servisini Ayağa Kaldırma (Port: 8000)
cd kle-blog-backend
cp .env.example .env
docker compose build
docker compose run --rm backend-app composer install
docker compose up -d
docker compose exec backend-app php artisan key:generate
docker compose exec backend-app php artisan migrate:fresh --seed
API Adresi: http://localhost:8000/api

Admin Paneli: http://localhost:8000/admin
**Varsayılan Giriş Bilgileri:**
- E-posta: `admin@kleblog.com`
- Şifre: `password`

2. Frontend İstemcisini Ayağa Kaldırma (Port: 8001)
cd ../kle-blog-frontend
cp .env.example .env
docker compose build
docker compose run --rm frontend-app composer install
docker compose up -d
docker compose exec frontend-app php artisan key:generate
Uygulama Adresi: http://localhost:8001

Testler ve Kod Standartları
Her iki servis için testleri ve kod formatlama (Pint) kontrollerini aşağıdaki komutlarla çalıştırabilirsiniz:

# Backend Testleri & Pint Kontrolü
docker compose exec backend-app php artisan test
docker compose exec backend-app ./vendor/bin/pint --test

# Frontend Testleri & Pint Kontrolü
docker compose exec frontend-app php artisan test
docker compose exec frontend-app ./vendor/bin/pint --test
