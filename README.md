# KLE Blog

Laravel 13 API, Filament v3 yönetim paneli ve Livewire 4 frontend içeren monorepo blog platformu.

## Teknoloji yığını

| Katman | Sürüm |
| --- | --- |
| PHP | 8.4+ |
| Backend | Laravel 13, Filament v3, Laravel Sanctum, MySQL 8 |
| Frontend | Laravel 13, Livewire 4, Tailwind CSS (Vite) |
| Altyapı | Docker Compose, PHP 8.4 CLI imajları (`ext-zip`, `ext-intl` etkin) |

## Servisler

Kök dizindeki `docker-compose.yml` tüm stack'i tanımlar:

| Servis | Adres | Not |
| --- | --- | --- |
| `mysql` | `127.0.0.1:${MYSQL_PORT:-3306}` | Yalnızca host loopback'e açılır |
| `backend` | http://localhost:8000/api, http://localhost:8000/admin | Açılışta migrate + seed çalışır |
| `frontend` | http://localhost:8080 | API'ye Docker ağı içinden `http://backend:8000/api` ile bağlanır |

Portlar `.env` içindeki `BACKEND_PORT`, `FRONTEND_PORT`, `MYSQL_PORT` ile değiştirilebilir.

## Ortam değişkenleri

MySQL parolaları Compose dosyasında hiçbir yerde sabit değildir ve varsayılan değerleri yoktur:

```yaml
MYSQL_PASSWORD: ${MYSQL_PASSWORD:?MYSQL_PASSWORD is required}
MYSQL_ROOT_PASSWORD: ${MYSQL_ROOT_PASSWORD:?MYSQL_ROOT_PASSWORD is required}
```

Bu değişkenler tanımlı değilse `docker compose` herhangi bir komutu çalıştırmadan hata verir. Healthcheck parolayı komut satırına yazmaz; konteynerin kendi ortamındaki `MYSQL_PASSWORD` değerini `MYSQL_PWD` üzerinden `mysqladmin`'e verir. Backend servisi `DB_PASSWORD` değerini aynı `MYSQL_PASSWORD` değişkeninden alır, dolayısıyla uygulama ve veritabanı her zaman aynı kimlik bilgisini kullanır.

| Değişken | Zorunlu | Açıklama |
| --- | --- | --- |
| `MYSQL_PASSWORD` | Evet | Uygulama kullanıcısının parolası |
| `MYSQL_ROOT_PASSWORD` | Evet | MySQL root parolası |
| `MYSQL_DATABASE` | Hayır | Varsayılan `kle_blog` |
| `MYSQL_USER` | Hayır | Varsayılan `kle_blog` |
| `MYSQL_PORT`, `BACKEND_PORT`, `FRONTEND_PORT` | Hayır | Host portları |

MySQL parolaları yalnızca veri volume'u ilk oluşturulurken uygulanır. Parolaları sonradan değiştirdiyseniz `docker compose down -v` ile volume'u silip yeniden kurun ya da parolayı MySQL içinde `ALTER USER` ile güncelleyin.

## Sıfırdan kurulum

```bash
git clone <repo-url> kle-blog
cd kle-blog
make fresh-install
```

`make fresh-install` sırasıyla şunları yapar:

1. `.env` yoksa `.env.example` dosyasından oluşturur ve `MYSQL_PASSWORD` / `MYSQL_ROOT_PASSWORD` için `openssl rand` ile rastgele parola üretir.
2. İmajları derler.
3. Konteynerleri başlatmadan `docker compose run --rm --no-deps` ile backend ve frontend Composer bağımlılıklarını, frontend npm bağımlılıklarını ve Vite build'ini kurar; her iki uygulamada `.env` ve `APP_KEY` hazırlar.
4. `php artisan migrate:fresh --seed --force` çalıştırır.
5. `docker compose up -d` ile stack'i ayağa kaldırır.

Make kullanmadan aynı akış:

```bash
cp .env.example .env    # MYSQL_PASSWORD ve MYSQL_ROOT_PASSWORD değerlerini doldurun
docker compose build
docker compose run --rm backend composer install
docker compose run --rm frontend composer install
docker compose run --rm frontend npm ci
docker compose run --rm frontend npm run build
docker compose up -d
```

`docker compose run --rm <servis> <komut>` hiçbir konteyner çalışmıyorken de kullanılabilir. Entrypoint yalnızca varsayılan `serve` komutunda uygulamayı hazırlayıp sunucuyu başlatır; diğer tüm komutları (`composer`, `php artisan`, `npm`, `vendor/bin/pint`) doğrudan çalıştırır. `backend` servisi `mysql` sağlıklı olana kadar bekler; veritabanı gerektirmeyen komutlarda beklememek için `--no-deps` ekleyin.

`vendor` klasörü olmadan `docker compose up -d` çalıştırılırsa entrypoint önce `composer install` yapar, bu nedenle backend restart döngüsüne girmez.

### Varsayılan admin

- E-posta: `admin@example.com`
- Şifre: `password`

## Make hedefleri

| Komut | Açıklama |
| --- | --- |
| `make fresh-install` | `.env` üretimi, build, bağımlılıklar, `migrate:fresh --seed`, `up -d` |
| `make install` | Backend ve frontend bağımlılıklarını `run --rm` ile kurar |
| `make up` / `make down` | Stack'i başlatır / durdurur |
| `make seed` | `migrate` ve `db:seed` |
| `make test` | Backend ve frontend test paketleri |
| `make pint` | Pint ile kodu biçimlendirir |
| `make pint-check` | Pint `--test` (CI için) |
| `make logs` | Servis logları |

## Testler

```bash
make test
```

veya:

```bash
docker compose run --rm --no-deps backend php artisan test
docker compose run --rm --no-deps frontend php artisan test
```

Testler `RefreshDatabase` ile her zaman in-memory SQLite üzerinde çalışır. `phpunit.xml` içindeki `DB_CONNECTION` ve `DB_DATABASE` değerleri `force="true"` ile tanımlıdır; Compose'un konteynere verdiği `DB_CONNECTION=mysql` değeri testlerde yok sayılır ve geliştirme veritabanı hiçbir zaman silinmez.

Backend paketi slug üretimi, yorum sahipliği, kategori yetkilendirmesi, aktif/pasif kategori ve sözleşme görünürlüğü, çoklu filtreler, sayfalama sınırları, login rate limit ve Sanctum token iptalini kapsar. Frontend paketi API yanıtlarını `Http::fake()` ile taklit ederek sayfalama, boş liste, 4xx/5xx ve bağlantı zaman aşımı akışlarını doğrular.

## API dokümantasyonu

- OpenAPI 3.0: `docs/openapi.yaml`
- Postman koleksiyonu: `docs/kle-blog.postman_collection.json`

Her iki doküman da `routes/api.php` içindeki 26 endpoint'in tamamını aynı yöntem, yol, gövde ve yanıt şemalarıyla tanımlar. Postman isteklerinin açıklamasında ilgili OpenAPI `operationId` değeri yer alır.

| Konu | Sözleşme |
| --- | --- |
| Başlıklar | Tüm isteklerde `Accept: application/json`; gövdeli isteklerde `Content-Type: application/json` |
| Kimlik doğrulama | `Authorization: Bearer {token}`; token `/login` veya `/register` yanıtındaki `token` alanıdır |
| Sayfalama | `page` (en az 1), `per_page` (1-50). Varsayılan: `/posts` ve `/categories/{slug}` için 15, `/categories` ve `/my-posts` için 10. Aralık dışı değer 422 döner |
| Sayfalı yanıt | `{ data: [], links: { first, last, prev, next }, meta: { current_page, from, last_page, links, path, per_page, to, total } }` |
| Kategori detayı | `{ category: Category, posts: { data, links, meta } }` |
| Güncelleme | `PUT`/`PATCH /posts/{id}`, `/categories/{id}`, `/comments/{id}`, `/profile` |
| Hata gövdesi | `{ message }`; validasyon hatalarında ek olarak `errors: { alan: [mesaj] }` |

Koleksiyon değişkenleri: `baseUrl` (`http://localhost:8000/api`), `token` (login/register yanıtından otomatik yazılır, logout sonrası temizlenir), `postId`, `postSlug`, `categoryId`, `categorySlug`, `commentId` (oluşturma isteklerinden otomatik güncellenir).

## Production ortamında secret yönetimi

`.env` dosyası yalnızca yerel geliştirme içindir ve `.gitignore` kapsamındadır. Production ortamında parolalar repoya, imaja veya Compose dosyasına yazılmamalıdır.

### Docker Secrets (Swarm)

Parolaları secret olarak oluşturun:

```bash
openssl rand -hex 32 | docker secret create kle_blog_mysql_password -
openssl rand -hex 32 | docker secret create kle_blog_mysql_root_password -
```

Resmi MySQL imajı `_FILE` sonekli değişkenleri destekler. Production için ayrı bir override dosyasında parolaları dosyadan okutun:

```yaml
# docker-compose.prod.yml
services:
  mysql:
    environment:
      MYSQL_PASSWORD_FILE: /run/secrets/kle_blog_mysql_password
      MYSQL_ROOT_PASSWORD_FILE: /run/secrets/kle_blog_mysql_root_password
    secrets:
      - kle_blog_mysql_password
      - kle_blog_mysql_root_password
  backend:
    secrets:
      - kle_blog_mysql_password

secrets:
  kle_blog_mysql_password:
    external: true
  kle_blog_mysql_root_password:
    external: true
```

Backend tarafında `DB_PASSWORD` değerini başlangıçta `/run/secrets/kle_blog_mysql_password` dosyasından ortam değişkenine aktaran bir entrypoint adımı kullanın. Böylece parola `docker inspect` çıktısında görünmez. Parolayı döndürmek için yeni bir secret sürümü oluşturup servisi `docker service update --secret-rm ... --secret-add ...` ile güncelleyin.

### CI/CD

- Parolaları pipeline'ın şifreli secret deposunda saklayın (GitHub Actions `secrets`, GitLab CI masked + protected variables, Vault, AWS Secrets Manager vb.).
- Deploy adımında değerleri yalnızca ortam değişkeni olarak aktarın; `.env` dosyası üretmeniz gerekiyorsa işlem sonunda silin ve artifact olarak yüklemeyin.
- `${VAR:?}` sözdizimi sayesinde eksik secret deploy'u ilk adımda durdurur; yanlışlıkla boş parolayla veritabanı oluşturulamaz.
- Log maskelemeyi açık tutun, `docker compose config` çıktısını log'a yazdırmayın (çıktı çözümlenmiş parolaları içerir).

GitHub Actions örneği:

```yaml
- name: Deploy
  env:
    MYSQL_PASSWORD: ${{ secrets.MYSQL_PASSWORD }}
    MYSQL_ROOT_PASSWORD: ${{ secrets.MYSQL_ROOT_PASSWORD }}
  run: docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d --build
```

Production'da ayrıca backend ve frontend için `APP_ENV=production`, `APP_DEBUG=false` ve ortama özel `APP_KEY` tanımlayın, MySQL portunu host'a hiç açmayın ve varsayılan admin parolasını ilk girişte değiştirin.

## Dizinler

- `kle-blog-backend/` — REST API ve Filament paneli
- `kle-blog-frontend/` — Livewire istemcisi
- `docs/` — OpenAPI ve Postman
