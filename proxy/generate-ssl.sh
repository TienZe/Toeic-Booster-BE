# Generate SSL certificate
docker exec -it toeic_proxy bash -c "certbot certonly --webroot \
  -w /var/www/certbot \
  -d toeicbooster.io.vn \
  -d www.toeicbooster.io.vn \
  --email hertyv27@gmail.com \
  --agree-tos \
  --no-eff-email \
  -v"


# Copy actual SSL certificate to ssl folder inside toeic_proxy container
docker exec -it toeic_proxy bash -c 'cd /etc/letsencrypt/live/toeicbooster.io.vn && cp -L --remove-destination cert.pem temp && mv temp cert.pem'
docker exec -it toeic_proxy bash -c 'cd /etc/letsencrypt/live/toeicbooster.io.vn && cp -L --remove-destination chain.pem temp && mv temp chain.pem'
docker exec -it toeic_proxy bash -c 'cd /etc/letsencrypt/live/toeicbooster.io.vn && cp -L --remove-destination fullchain.pem temp && mv temp fullchain.pem'
docker exec -it toeic_proxy bash -c 'cd /etc/letsencrypt/live/toeicbooster.io.vn && cp -L --remove-destination privkey.pem temp && mv temp privkey.pem'