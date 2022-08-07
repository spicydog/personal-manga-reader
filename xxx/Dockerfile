FROM php:7.4-apache
RUN echo "ServerName localhost" | tee /etc/apache2/conf-available/fqdn.conf && a2enconf fqdn
COPY . .
ENTRYPOINT [ "" ]
CMD [ "bash", "start.sh" ]
