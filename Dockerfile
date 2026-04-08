# 1. Koresha PHP verisiyo ya 8.1 ifite Apache server imbere
FROM php:8.1-apache

# 2. Fungura mysqli (Ibi nibyo bikemura ya Fatal Error yari yaje)
# Turashyiramo na pdo_mysql niba wazabikenera mu gihe kizaza
RUN docker-php-ext-install mysqli pdo pdo_mysql && docker-php-ext-enable mysqli

# 3. Kopiya amadociye yawe yose ari muri GitHub uyashyire muri folder ya server
COPY . /var/www/html/

# 4. Aha ni amabwiriza yo guha uburenganzira (permissions) server yo gusoma dosiye
RUN chown -R www-data:www-data /var/www/html

# 5. Fungura amarembo ya Port 80
EXPOSE 80

# 6. Tangiza Apache server
CMD ["apache2-foreground"]
