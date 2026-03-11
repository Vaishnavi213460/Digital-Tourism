FROM php:8.2-apache

# 1. Install MySQL support
RUN docker-php-ext-install pdo pdo_mysql

# 2. Enable URL rewriting
RUN a2enmod rewrite

# 3. Copy your project into the container
COPY . /var/www/html/

# 4. CRITICAL: Change the Apache Document Root to the 'public' folder
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 5. Set permissions
RUN chown -R www-data:www-data /var/www/html