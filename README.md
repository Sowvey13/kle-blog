# KLE Blog Monorepo

Bu proje, modern web mimarisi standartlarına uygun olarak geliştirilmiş modüler bir blog platformudur. Sistem, RESTful API mimarisi sunan Laravel 11 Backend servisi ve bu API'yi tüketen Livewire 3 tabanlı Frontend istemcisinden oluşmaktadır.

---

## 🛠 Teknoloji Yığını

* **Backend:** Laravel 11, FilamentPHP v3 (Admin Paneli), Laravel Sanctum (API Token Auth), SQLite / MySQL
* **Frontend:** Laravel 11, Livewire v3, Tailwind CSS (Vite Entegrasyonu)
* **Konteynırlaştırma:** Docker & Docker Compose

---

## 🚀 Hızlı Kurulum & Çalıştırma

Projenin her iki bileşeni de Dockerize edilmiştir. 

### 1. Backend Servisini Ayağa Kaldırma (Port: 8000)

```bash
cd kle-blog-backend
cp .env.example .env
docker compose up -d --build
docker exec -it kle-blog-backend-app php artisan key:generate
docker exec -it kle-blog-backend-app php artisan migrate:fresh --seed
API Adresi: http://localhost:8000/api

Admin Paneli: http://localhost:8000/admin

2. Frontend İstemcisini Ayağa Kaldırma (Port: 8001)
Bash
cd ../kle-blog-frontend
cp .env.example .env
docker compose up -d --build
docker exec -it kle-blog-frontend-app php artisan key:generate
Uygulama Adresi: http://localhost:8001