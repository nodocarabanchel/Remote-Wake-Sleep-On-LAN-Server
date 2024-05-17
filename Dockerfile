FROM php:8.2-apache

# Install required packages
RUN apt-get update && apt-get install -y \
    git \
    wget \
    wakeonlan \
    && docker-php-ext-install mysqli

# Enable Apache modules
RUN a2enmod rewrite

# Enable mod_headers module
RUN a2enmod headers
# Copy the apache configuration file
COPY apache2_configs/000-default_http.conf /etc/apache2/sites-available/000-default_http.conf

# Set ServerName to suppress the warning
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Enable site configuration
RUN a2ensite 000-default_http

# Expose port 80
EXPOSE 80

CMD ["apache2-foreground"]
