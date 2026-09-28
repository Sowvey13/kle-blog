# KLE Blog Backend

Laravel 13, Filament v3 ve Sanctum tabanlı REST API. PHP 8.4+ gerektirir. Livewire 4 istemcisi `../kle-blog-frontend` dizinindedir.

Tercih edilen çalışma şekli kök dizindeki `docker-compose.yml` dosyasıdır; kurulum ve secret yönetimi için kök `README.md` dosyasına bakın.

- API: http://localhost:8000/api
- Filament paneli: http://localhost:8000/admin
- Varsayılan admin: `admin@example.com` / `password`

## Komutlar

```bash
docker compose run --rm backend composer install
docker compose run --rm backend php artisan migrate --seed --force
docker compose run --rm --no-deps backend php artisan test
docker compose run --rm --no-deps backend vendor/bin/pint
```

## Slug üretimi

`Post`, `Category` ve `Contract` modelleri `App\Traits\HasSlug` trait'ini kullanır. Slug; oluşturmada ve kaynak alan (`title` veya `name`) değiştiğinde Türkçe karakterler dönüştürülerek üretilir, çakışmada `-1`, `-2` ... soneki alır. Elle girilen slug normalize edilir ve benzersizleştirilir; boş bırakılan slug kaynak alandan yeniden üretilir. Filament formları, seeder ve factory'ler slug üretmez.

## Endpoint sözleşmesi

Tüm isteklerde `Accept: application/json` gönderilir. 🔒 işaretli endpoint'ler `Authorization: Bearer {token}` ister. Ayrıntılı şemalar `../docs/openapi.yaml`, örnek istekler `../docs/kle-blog.postman_collection.json` içindedir.

| Yöntem | Yol | Yetki | İstek | Başarılı yanıt |
| --- | --- | --- | --- | --- |
| POST | `/register` | Herkes | `name`, `email`, `password`, `password_confirmation` | 201 `{ message, user, token }` |
| POST | `/login` | Herkes, 6 istek/dk | `email`, `password` | 200 `{ message, user, token }`; 401, 429 |
| POST | `/logout` | 🔒 | | 200 `{ message }`; yalnızca mevcut token silinir |
| GET | `/me`, `/profile` | 🔒 | | 200 `{ data: User }` |
| PUT, PATCH | `/profile` | 🔒 | `name`, `email` (ikisi de zorunlu) | 200 `{ message, data: User }` |
| GET | `/my-posts` | 🔒 | `page`, `per_page` (varsayılan 10) | 200 sayfalı `Post` listesi |
| GET | `/posts` | Herkes | `page`, `per_page` (varsayılan 15), `search`, `category_id`, `user_id`, `date` | 200 sayfalı `Post` listesi |
| GET | `/posts/{slug}` | Herkes | | 200 `{ data: Post + comments }`; 404 |
| POST | `/posts` | 🔒 | `title`, `category_id` (aktif), `content` | 201 `{ message, data: Post }` |
| PUT, PATCH | `/posts/{id}` | 🔒 sahip veya admin | `title`, `category_id`, `content`; admin için `is_approved`, `published_at` | 200 `{ message, data: Post }`; 403 |
| DELETE | `/posts/{id}` | 🔒 sahip veya admin | | 200 `{ message }`; 403 |
| GET | `/categories` | Herkes | `page`, `per_page` (varsayılan 10) | 200 sayfalı aktif `Category` listesi |
| GET | `/categories/{slug}` | Herkes | `page`, `per_page` (varsayılan 15) | 200 `{ category, posts: { data, links, meta } }`; pasifse 404 |
| POST | `/categories` | 🔒 admin | `name`, `is_active` | 201 `{ message, data: Category }`; 403 |
| PUT, PATCH | `/categories/{id}` | 🔒 admin | `name`, `is_active` | 200 `{ message, data: Category }`; 403 |
| DELETE | `/categories/{id}` | 🔒 admin | | 200 `{ message }`; 403 |
| POST | `/comments` | 🔒 | `post_id` (yayınlanmış), `content` | 201 `{ message, data: Comment }` |
| PUT, PATCH | `/comments/{id}` | 🔒 sahip veya admin | `content` | 200 `{ message, data: Comment }`; 403 |
| DELETE | `/comments/{id}` | 🔒 sahip veya admin | | 200 `{ message }`; 403 |
| GET | `/contracts` | Herkes | | 200 `{ data: Contract[] }`, yalnızca aktifler |
| GET | `/contracts/{slug}` | Herkes | | 200 `{ data: Contract }`; pasifse 404 |

`per_page` 1 ile 50 arasında olmalıdır; aralık dışı değerler, geçersiz `category_id`/`user_id` ve hatalı `date` 422 döner. Yetkisiz kullanıcı için yetkilendirme validasyondan önce kontrol edilir, bu nedenle geçersiz gövdeyle de 403 alınır.
