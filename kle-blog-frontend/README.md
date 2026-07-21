KLE Blog - Frontend Client 🌐
Bu proje, KLE Blog uygulamasının kullanıcı dostu, modern ve tamamen duyarlı (responsive) ön yüz (frontend) uygulamasıdır. Backend API servisleri ile tamamen asenkron şekilde haberleşir ve kendi üzerinde hiçbir veritabanı bağlantısı barındırmaz.

🛠️ Kullanılan Teknolojiler
Framework: Laravel 11

Dinamik Arayüz: Livewire v3 (Full-Page Components)

Tasarım & Stil: Tailwind CSS

API İletişimi: Laravel HTTP Client (Guzzle)

🚀 Docker ile Ayağa Kaldırma
Projeyi Klonlayın ve Klasöre Girin:


cd kle-blog-frontend
Docker Konteynerlerini Başlatın:


docker compose up -d
Uygulama Önbelleğini Temizleyin:


docker compose exec frontend-app php artisan view:clear
Tarayıcıdan Erişin:
👉 http://localhost:8001