FinBud — Hướng dẫn cài đặt & chạy

Mô tả
- FinBud là ứng dụng quản lý tài chính cá nhân (PHP + MySQL). Giao diện chính: `index.php`.

Yêu cầu
- PHP >= 8.0 (khuyến nghị PHP 8.2)
- MySQL hoặc MariaDB
- PHP extension: `mysqli` (bắt buộc)
- Web server: Apache / Nginx hoặc PHP built-in server

Cài đặt nhanh (Windows, dùng XAMPP/WAMP)
1. Cài XAMPP hoặc WAMP (Apache + MySQL + PHP).
2. Copy toàn bộ thư mục dự án vào thư mục web root (ví dụ `C:\xampp\htdocs\finbud-main`).
3. Bật Apache và MySQL trong XAMPP Control Panel.
4. Tạo và import database:
	 - Mở phpMyAdmin (http://localhost/phpmyadmin), tạo database tên `finbud`, sau đó Import file `finbud.sql` (file nằm trong thư mục dự án).
	 - Hoặc dùng dòng lệnh (thay đường dẫn cho phù hợp):

```powershell
"C:\xampp\mysql\bin\mysql.exe" -u root -p
CREATE DATABASE finbud;
exit
"C:\xampp\mysql\bin\mysql.exe" -u root finbud < "C:\xampp\htdocs\finbud-main\finbud.sql"
```

5. Cấu hình kết nối DB: mở [db.php](db.php) và chỉnh `\$servername`, `\$username`, `\$password`, `\$dbname` cho đúng (mặc định trong project là `localhost`, `root`, mật khẩu rỗng, database `finbud`).

6. Mở trình duyệt và truy cập: http://localhost/finbud-main/index.php

Chạy nhanh bằng PHP built-in server (phát triển):

```powershell
cd C:\path\to\finbud-main
php -S localhost:8000
# Mở: http://localhost:8000/index.php
```

Cấu trúc chính của dự án
- `index.php`, `login.php`, `signup.php`, `dashboard.php`, `budget.php`, `reportpage.php`, ...
- Thư mục API:
	- `budget_api/` — API liên quan budget
	- `expense_api/` — API liên quan expense (gồm export, filter, chatbot helper)
	- `income_api/` — API liên quan income
	- `financial_goal_api/` — API liên quan mục tiêu tài chính
	- `home_feature/` — scripts cho charts và giao diện
- File SQL: `finbud.sql` (schema + data mẫu + triggers)
