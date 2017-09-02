FROM nginx


RUN apt-get update
RUN apt-get upgrade -y
RUN apt-get install -y sudo curl php git cron acl php7.0-fpm unzip zip
RUN apt-get install -y php-common php7.0-mbstring php-xml php7.0-gd php7.0-mysql
RUN curl -LO https://deployer.org/deployer.phar
RUN mv deployer.phar /usr/local/bin/dep
RUN chmod +x /usr/local/bin/dep
RUN mkdir -p /var/www/preprod/releases
RUN mkdir -p /var/www/prod/univ-nantes-aviron/releases
RUN mkdir -p /var/www/prod/univ-nantes-aviron/shared/storage

RUN rm -f /etc/nginx/conf.d/default.conf

RUN adduser www-data sudo

COPY src/nginx/nginx.conf /etc/nginx/nginx.conf
COPY src/nginx/sites-enabled /etc/nginx/sites-enabled

RUN rm /etc/php/7.0/fpm/php.ini
COPY src/php/php.ini /etc/php/7.0/fpm/

RUN export COMPOSER_PROCESS_TIMEOUT=1200
RUN mkdir /root/una-workspace

COPY src/deploy.php /root/una-workspace/

RUN cd /root/una-workspace; dep deploy docker-prod

RUN chmod a+x /var/www/prod/univ-nantes-aviron/current/.utils/*

RUN /var/www/prod/univ-nantes-aviron/current/.utils/packages_install.sh
RUN /var/www/prod/univ-nantes-aviron/current/.utils/project_install.sh

CMD /etc/init.d/php7.0-fpm start && nginx -g 'daemon off;' "$@"
