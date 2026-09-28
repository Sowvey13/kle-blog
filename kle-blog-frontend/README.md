# KLE Blog Frontend

Laravel 13 ve Livewire 4 istemcisi, Tailwind CSS (Vite) ile derlenir. PHP 8.4+ gerektirir.

Tercih edilen çalışma şekli kök dizindeki `docker-compose.yml` dosyasıdır. Uygulama adresi: http://localhost:8080 (`FRONTEND_PORT` ile değiştirilebilir).

Backend API'ye sunucu tarafında `BACKEND_API_URL` (`http://backend:8000`) üzerinden bağlanır. `App\Services\ApiService` tüm isteklere `Accept: application/json` ve oturumdaki token için `Authorization: Bearer {token}` ekler; bağlantı zaman aşımı 5 sn, istek zaman aşımı 8 sn'dir. Ağ hataları loglanır ve bileşenlere `status: 503` ile jenerik bir mesaj döner; backend'in ham hata metinleri kullanıcıya gösterilmez.

Listeleme sayfaları (`Home`, `Dashboard`, `CategoryDetail`) API'nin `meta` bilgisini okur, `?page=` parametresini URL ile senkron tutar ve yalnızca "Önceki" / "Sonraki" ile sayfa numaralarını gösterir. `CategoryDetail` ayrıca `?per_page=` kabul eder (varsayılan 9, en fazla 50) ve her iki değeri API isteğine iletir.

## Komutlar

```bash
docker compose run --rm frontend composer install
docker compose run --rm frontend npm ci
docker compose run --rm frontend npm run build
docker compose run --rm frontend php artisan test
docker compose run --rm frontend vendor/bin/pint
```
