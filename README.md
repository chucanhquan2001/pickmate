# PickMate

Web app for a pickleball club. Laravel, Inertia, and Vue live in this one repository.

## Requirements

- PHP 8.3+
- Composer
- MySQL 8

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Create the MySQL database named in `.env`, then:

```bash
php artisan migrate --seed
php artisan serve
```

The seeder creates one club from `PICKMATE_CLUB_NAME`, `PICKMATE_CLUB_TIMEZONE`, and `PICKMATE_CLUB_LANGUAGE`.

Set `PICKMATE_OWNER_EMAIL` to the Google or Facebook email that should become the club owner on first login.

Auth settings:

```text
APP_URL
GOOGLE_CLIENT_ID
GOOGLE_CLIENT_SECRET
FACEBOOK_CLIENT_ID
FACEBOOK_CLIENT_SECRET
```

`APP_URL` is the public URL. Google and Facebook redirect URIs point at that host.

## Login

Sign in with Google or Facebook. Laravel uses Socialite and a session cookie. There is no password login.

## Docker trên server

Trên server cần Docker và Docker Compose. Tạo `.env` từ `.env.example`, rồi đặt mật khẩu database khác rỗng và `APP_KEY`:

```bash
cp .env.example .env
php artisan key:generate --show
```

Dán chuỗi `base64:...` vào `APP_KEY`. Đặt `DB_PASSWORD` và `DB_ROOT_PASSWORD`. Compose luôn nối app vào MySQL bằng user `pickmate` và host `mysql`, không dùng `DB_USERNAME=root`.

Điền thêm `PICKMATE_OWNER_EMAIL`, Google và Facebook như phần setup. `APP_URL` phải là URL public, ví dụ `https://pickmate.example.com`.

```bash
docker compose up -d --build
```

App lắng nghe cổng `APP_PORT` (mặc định 80). Health check là `GET /up`. Container `app` tự migrate và seed CLB khi khởi động. Container `queue` chạy queue database. Dữ liệu MySQL và `storage` nằm trong volume, không mất khi tạo lại container.

Đặt HTTPS ở reverse proxy phía trước (Caddy, Nginx, hoặc Cloudflare). App tin header `X-Forwarded-Proto`.

Cập nhật bản mới:

```bash
docker compose up -d --build
```

## Tests

```bash
php artisan test
```
