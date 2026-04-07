# Koresha PHP ifite Apache (Server)
FROM php:8.1-apache

# Kopiya amadociye yawe yose uyashyire muri server
COPY . /var/www/html/

# Fungura amarembo (Port) 80
EXPOSE 80
