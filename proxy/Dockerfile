FROM nginx:latest

# Install Certbot for SSL certificate generation
RUN apt update && \
    apt install -y certbot python3-certbot-nginx

# Create directories
RUN mkdir -p /var/www/certbot