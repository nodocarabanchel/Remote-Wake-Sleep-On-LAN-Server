FROM php:8.2-apache

# Install required packages
RUN apt-get update && apt-get install -y \
    git \
    wget \
    wakeonlan \
    && docker-php-ext-install mysqli

# Enable Apache modules
RUN a2enmod rewrite

# Copy the apache configuration file
COPY apache2_configs/000-default.conf /etc/apache2/sites-available/000-default.conf

# Enable site configuration
RUN a2ensite 000-default

# Expose ports
EXPOSE 80

CMD ["apache2-foreground"]
