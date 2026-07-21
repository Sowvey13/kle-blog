KLE Blog Backend 🚀
Laravel 11, Filament v3 ve Docker tabanlı geliştirilmiş blog backend uygulaması. Tüm veri yönetimini ve ön yüzü besleyen API endpoint'lerini barındırır.

🚀 Hızlı Kurulum Adımları
Docker Konteynerlerini Başlatın:


docker compose up -d
Bağımlılıkları Yükleyin:


docker compose exec backend-app composer install
Veritabanı Tablolarını Oluşturun ve Varsayılan Verileri Yükleyin:


docker compose exec backend-app php artisan migrate:fresh --seed
Filament Admin Panel Kullanıcısı Oluşturun:


docker compose exec backend-app php artisan make:filament-user
🖥️ Erişim Bilgileri
Backend Durum Kontrolü: http://localhost:8000

Frontend Blog Uygulaması: http://localhost:8001

Filament Admin Paneli: http://localhost:8000/admin

🌐 API Rotaları (Endpoints)
🔓 Herkese Açık Rotalar (Token Gerekmez)
GET http://localhost:8000/api/posts - Aktif blog yazıları listesi

GET http://localhost:8000/api/categories - Sistemde kayıtlı kategoriler

GET http://localhost:8000/api/contracts - Aktif sözleşmeler (KVKK, Kullanıcı Sözleşmesi vb.)

POST http://localhost:8000/api/login - Kullanıcı girişi (Bearer Token döner)

POST http://localhost:8000/api/register - Yeni kullanıcı kaydı

🔒 Korumalı Rotalar (Bearer Token Gerektirir)
POST http://localhost:8000/api/posts - Yeni blog yazısı oluşturma (Onaya düşer)

DELETE http://localhost:8000/api/posts/{id} - Blog yazısını silme

POST http://localhost:8000/api/categories - Dinamik olarak yeni kategori ekleme

POST http://localhost:8000/api/comments - Seçilen yazıya yorum ekleme (Onaya düşer)

DELETE http://localhost:8000/api/comments/{id} - Yorum silme

POST http://localhost:8000/api/logout - Oturumu kapatma ve token iptali