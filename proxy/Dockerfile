FROM nginx:latest

# Install Certbot for SSL certificate generation
RUN apt update && \
    apt install -y certbot

# Create directories
RUN mkdir -p /var/www/certbot
RUN mkdir -p /etc/letsencrypt/live