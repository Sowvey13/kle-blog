# KLE Blog

Laravel 13 API, Filament v3 yönetim paneli ve Livewire 4 frontend içeren bir monorepo blog platformudur.

## Teknoloji yığını

| Katman | Sürüm |
| --- | --- |
| PHP | 8.4+ |
| Backend | Laravel 13, Filament v3, Laravel Sanctum, MySQL 8 |
| Frontend | Laravel 13, Livewire 4, Tailwind CSS (Vite) |
| Altyapı | Docker Compose, PHP 8.4 (`ext-zip` etkin) |

## Sıfırdan kurulum

Kök dizinde tek Compose dosyası tüm servisleri ayağa kaldırır: `mysql`, `backend` (port 8000), `frontend` (port 8080). Frontend, API'ye Docker ağı üzerinden `http://backend:8000/api` adresinden erişir. Backend entrypoint ilk açılışta Composer, `APP_KEY`, migrate ve seed çalıştırır.

```bash
git clone <repo-url> kle-blog
cd kle-blog
cp .env.example .env
cp kle-blog-backend/.env.example kle-blog-backend/.env
cp kle-blog-frontend/.env.example kle-blog-frontend/.env
docker compose up -d
```

Eşdeğer Make hedefi:

```bash
make up
```

İlk ayağa kalkışta imaj derlemesi, Composer ve (frontend) npm build biraz sürebilir. Servisler hazır olduğunda:

- API: http://localhost:8000/api
- Filament: http://localhost:8000/admin
- Frontend: http://localhost:8080

Seed bir kez daha çalıştırmak için:

```bash
make seed
```

veya

```bash
docker compose exec backend php artisan migrate --force
docker compose exec backend php artisan db:seed --force
```

### Varsayılan admin

- E-posta: `admin@example.com`
- Şifre: `password`

## Testler

```bash
make test
```

veya ayrı ayrı:

```bash
docker compose exec backend php artisan test
docker compose exec frontend php artisan test
docker compose exec backend ./vendor/bin/pint --test
docker compose exec frontend ./vendor/bin/pint --test
```

## API dokümantasyonu

- OpenAPI 3.0: `docs/openapi.yaml`
- Postman koleksiyonu: `docs/kle-blog.postman_collection.json`

Koleksiyon değişkenleri: `baseUrl` (`http://localhost:8000/api`), `token` (login/register yanıtından otomatik yazılır). Kimlik gerektiren istekler `Authorization: Bearer {{token}}` kullanır. Liste endpoint'leri `page` ve `per_page` (en fazla 50) kabul eder.

## Make hedefleri

| Komut | Açıklama |
| --- | --- |
| `make up` | `docker compose up -d --build` |
| `make seed` | migrate + seed |
| `make test` | backend ve frontend testleri |
| `make pint` | Pint `--test` |
| `make down` | stack'i durdur |

## Dizinler

- `kle-blog-backend/` — REST API ve Filament
- `kle-blog-frontend/` — Livewire istemcisi
- `docs/` — OpenAPI ve Postman
