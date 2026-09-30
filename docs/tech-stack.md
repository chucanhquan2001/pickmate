# PickMate — Technology Stack

PickMate là một app web Laravel. Giao diện nằm trong cùng repo, không có client riêng và không có API JSON công khai.

Định hướng sản phẩm, module và database nghiệp vụ nằm ở [pickmate-project-plan.md](pickmate-project-plan.md).

---

## 1. Stack

```text
PHP 8.3+
Laravel 13
Inertia.js
Vue 3
Tailwind CSS
Vite
Laravel Socialite
MySQL 8
```

Dùng thêm khi cần, không phải nền của MVP:

```text
Redis          queue và cache khi tải tăng
S3-compatible  avatar và logo
Laravel Pint   format code
Pest           test
```

Laravel phục vụ trang Inertia. Vue nằm ở `resources/js`. Tailwind và Vite build giao diện trong cùng project.

---

## 2. Cách tổ chức code

Controller mỏng. Logic nghiệp vụ nằm ở service, đúng các service đã nêu trong project plan.

```text
Route web
  ↓
Form Request
  ↓
Controller
  ↓
Service / Action
  ↓
Model
  ↓
Inertia::render
```

Quy ước:

- Route trang là route web, dùng session.
- Validate bằng Form Request.
- Controller trả `Inertia::render` kèm props cho trang Vue.
- Không nhét tính điểm, đóng minigame hay ghép trận vào controller.
- Repository chỉ thêm khi một query thực sự cần tách. MVP chưa cần repository cho mọi model.

Trang Vue:

```text
resources/js/Pages        mỗi màn hình
resources/js/Components   phần dùng lại
resources/css             Tailwind
```

---

## 3. Đăng nhập

Chỉ có hai cách:

```text
Google
Facebook
```

Không có form email và mật khẩu. Bảng `users` không dùng mật khẩu để đăng nhập.

Người dùng bấm nút trên trang login. Laravel chuyển sang Google hoặc Facebook qua Socialite, nhận callback, rồi mở session.

```text
Trang login
  ↓
Redirect Socialite
  ↓
Google hoặc Facebook
  ↓
Callback về Laravel
  ↓
Tìm social account, hoặc tạo user mới
  ↓
Session cookie
```

Khớp tài khoản:

1. Tìm theo `provider` + `provider_user_id`. Thấy thì đăng nhập user đó.
2. Chưa thấy, nhưng email đã được nhà cung cấp xác minh và đã thuộc một user: gắn provider mới vào user đó.
3. Chưa thấy email: tạo user mới và một dòng social account.

Không tạo user thứ hai khi cùng một email đã xác minh đăng nhập bằng provider còn lại.

Route auth:

```text
GET  /auth/google
GET  /auth/google/callback
GET  /auth/facebook
GET  /auth/facebook/callback
POST /logout
```

`APP_URL` là URL public của app, dùng làm gốc cho redirect OAuth.

### Dữ liệu auth

`users` không có mật khẩu dùng để login.

```text
id
club_id nullable
name
email nullable
avatar nullable
role
status
created_at
updated_at
```

`social_accounts`

```text
id
user_id
provider          google | facebook
provider_user_id
email nullable
created_at
updated_at
```

Một user có thể có cả Google và Facebook. Cặp `provider` + `provider_user_id` là duy nhất.

Role vẫn là `owner`, `admin`, `member` như project plan. Đăng nhập chỉ xác định danh tính. Quyền thao tác CLB, minigame và kết quả dựa trên role và `club_id`.

MVP không bắt mọi thành viên trong kho member phải đăng nhập. Admin vẫn tạo member trước. Khi một người đăng nhập, hệ thống gắn user với member cùng CLB nếu email trùng.

---

## 4. Giao diện

Mobile-first. Phần lớn thao tác xảy ra tại sân, bằng một tay.

Inertia giữ điều hướng trong app: sang trang khác không tải lại cả document. Vue giữ trạng thái tạm ở những chỗ cần phản hồi ngay, như chọn roster, chia đội và nhập tỉ số.

Nguyên tắc màn hình, bottom navigation và cách tránh modal lồng nhau nằm ở mục UI / UX của [pickmate-project-plan.md](pickmate-project-plan.md).

---

## 5. Hạ tầng tối thiểu

```text
App        PHP 8.3, Laravel 13, Inertia, Vue 3, Tailwind, Vite
Database   MySQL 8
Queue      database driver ở MVP
Cache      database hoặc file ở MVP
File       disk local ở MVP, avatar lấy URL từ Google hoặc Facebook trước
```

Biến môi trường auth:

```text
APP_URL
GOOGLE_CLIENT_ID
GOOGLE_CLIENT_SECRET
FACEBOOK_CLIENT_ID
FACEBOOK_CLIENT_SECRET
```

Redirect URI đăng ký với Google và Facebook trỏ về callback của `APP_URL`.
