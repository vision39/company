FROM php:8.2-apache

# Install sendmail
RUN apt-get update && apt-get install -y sendmail

# Enable rewrite module if needed
RUN a2enmod rewrite

# Copy app files
COPY . /var/www/html/

# Configure Gmail SMTP relay
RUN printf "root=dkrray772@gmail.com\n\
mailhub=smtp.gmail.com:587\n\
AuthUser=dkrray772@gmail.com\n\
AuthPass=xaztgsmkhxwatrnb\n\
UseSTARTTLS=YES\n\
FromLineOverride=YES" > /etc/ssmtp/ssmtp.conf

# Set permissions (optional)
RUN chown -R www-data:www-data /var/www/html
