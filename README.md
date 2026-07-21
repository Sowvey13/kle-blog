Bu depo, KLE Blog uygulamasının hem Backend (API & Admin Paneli) hem de Frontend (Ön Yüz İstemcisi) projelerini tek bir çatı altında barındıran ana depodur.

📁 Proje Yapısı
kle-blog-backend/ -> Laravel 11 + FilamentPHP v3 + Sanctum API mimarisi

kle-blog-frontend/ -> Laravel 11 + Livewire v3 + Tailwind CSS mimarisi (%100 API Odaklı)

🚀 Hızlı Başlatma Rehberi
Her iki proje de Dockerize edilmiştir. Yerel ortamda çalıştırmak için ilgili klasörlere girip Docker container'larını başlatmanız yeterlidir:

1. Backend Servisini Çalıştırma (Port: 8000)

cd kle-blog-backend
docker compose up -d
docker compose exec backend-app php artisan migrate:fresh --seed
2. Frontend Servisini Çalıştırma (Port: 8001)

cd kle-blog-frontend
docker compose up -d
🔗 Erişim Adresleri
Blog Ön Yüzü: http://localhost:8001

Admin Paneli: http://localhost:8000/admin

API Giriş Noktası: http://localhost:8000/api/posts

Detaylı teknik dokümantasyon ve ister karşılanma listeleri için alt klasörlerdeki README.md dosyalarını inceleyebilirsiniz.