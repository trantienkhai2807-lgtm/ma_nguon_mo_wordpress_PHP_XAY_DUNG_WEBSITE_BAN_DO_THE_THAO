# Huong dan dua project len GitHub

Project nay gom theme va plugin tuy chinh cho WordPress. Khi dua len GitHub, chi nen dua cac thu muc custom, khong dua toan bo WordPress core va database.

## Cau truc nen day len GitHub

```text
sportzone-wordpress/
├─ sportzone-theme/
├─ sportzone-demo-content/
├─ sportzone-virtual-card-gateway/
├─ README.md
└─ GITHUB_DEPLOY.md
```

## Khong nen dua len GitHub

Khong dua cac thu muc/file sau:

```text
wordpress/
wp-admin/
wp-includes/
wp-content/uploads/
wp-config.php
*.sql
```

Neu can nop database cho bai tap, hay export file `.sql` rieng va chi gui khi giang vien yeu cau.

## Cach tao repository

Mo terminal tai thu muc project:

```bash
git init
git add sportzone-theme sportzone-demo-content sportzone-virtual-card-gateway README.md GITHUB_DEPLOY.md
git commit -m "Add SportZone WordPress theme and plugins"
git branch -M main
git remote add origin https://github.com/USERNAME/REPOSITORY.git
git push -u origin main
```

Thay `USERNAME` va `REPOSITORY` bang tai khoan va ten repo cua ban.

## Cach cai tren may khac

1. Cai XAMPP.
2. Tai WordPress tu wordpress.org.
3. Copy WordPress vao:

```text
C:/xampp/htdocs/wordpress
```

4. Tao database trong phpMyAdmin, vi du:

```text
wordpress_sport
```

5. Cai WordPress voi thong tin:

```text
Database Name: wordpress_sport
Username: root
Password: de trong
Database Host: localhost
Table Prefix: wp_
```

6. Copy cac thu muc trong repo:

```text
sportzone-theme
sportzone-demo-content
sportzone-virtual-card-gateway
```

vao:

```text
wp-content/themes/sportzone-theme
wp-content/plugins/sportzone-demo-content
wp-content/plugins/sportzone-virtual-card-gateway
```

7. Vao WordPress Admin:

```text
http://localhost/wordpress/wp-admin
```

8. Kich hoat theme:

```text
Appearance > Themes > SportZone Shop
```

9. Kich hoat plugin:

```text
WooCommerce
SportZone Demo Content
SportZone Virtual Card Gateway
```

10. Vao website va nhan `Ctrl + F5`.

## Ghi chu ve thanh toan the ao

Plugin `sportzone-virtual-card-gateway` chi dung cho demo/do an.

The demo:

```text
So the: 4242 4242 4242 4242
CVV: 123
Ngay het han: 12/30
```

Plugin khong xu ly thanh toan that va khong luu so the.
