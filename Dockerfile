FROM php:8.2-apache

# Install system dependencies, Node.js 20, Supervisor, and MySQL PDO
RUN apt-get update && apt-get install -y \
    curl \
    git \
    supervisor \
    ca-certificates \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && docker-php-ext-install pdo pdo_mysql \
    && a2enmod rewrite \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Point Apache DocumentRoot to /var/www/html/public with .htaccess overrides enabled
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
RUN sed -i '/<Directory \${APACHE_DOCUMENT_ROOT}>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf || true
RUN echo '<Directory /var/www/html/public>\n    AllowOverride All\n    Require all granted\n</Directory>' >> /etc/apache2/apache2.conf
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

WORKDIR /var/www/html

# Copy repository files
COPY . /var/www/html

# Create session directory, dummy .env, and set permissions
RUN mkdir -p /var/www/html/whatsapp-api-data/sessions /var/log/supervisor \
    && touch /var/www/html/.env \
    && ln -sf /var/www/html/public /var/www/html/public/public \
    && chown -R www-data:www-data /var/www/html

# Install worker dependencies
RUN cd /var/www/html/worker && npm install --omit=dev

# Copy supervisor configuration and set entrypoint
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
RUN chmod +x /var/www/html/docker/entrypoint.sh

EXPOSE 80

CMD ["/var/www/html/docker/entrypoint.sh"]
