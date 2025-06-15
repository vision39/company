FROM php:8.2-apache

# Install msmtp (modern alternative to ssmtp)
RUN apt-get update && apt-get install -y msmtp msmtp-mta

# Copy app files
COPY . /var/www/html/

# Configure msmtp to use Gmail SMTP
RUN mkdir -p /etc/msmtp
RUN printf "defaults\n\
auth           on\n\
tls            on\n\
tls_trust_file /etc/ssl/certs/ca-certificates.crt\n\
logfile        /var/log/msmtp.log\n\
account        gmail\n\
host           smtp.gmail.com\n\
port           587\n\
from           dkrray772@gmail.com\n\
user           dkrray772@gmail.com\n\
password       qumkfvsvcrtcqmzm\n\
account default : gmail\n" > /etc/msmtprc

# Set mail alias for www-data (Apache runs as this user)
RUN echo "defaults: dkrray772@gmail.com" > /etc/mail.rc

# Set permissions
RUN chown -R www-data:www-data /var/www/html
