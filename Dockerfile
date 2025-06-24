# Usa l'immagine ufficiale PHP con Apache
FROM php:8.2-apache

# Copia tutto il codice nel container
COPY . /var/www/html/

# Abilita mod_rewrite per usare .htaccess
RUN a2enmod rewrite

# Imposta permessi corretti (utile per sicurezza)
RUN chown -R www-data:www-data /var/www/html

# Espone la porta 80 (gestita da Render)
EXPOSE 80
