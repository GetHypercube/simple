docker-compose up -d && \
echo "Please wait while service is up..." && \
sleep 5 && \
docker exec simple2 bash /var/www/simple/setup/simple.sh && \
echo "All done"