FROM debian:latest


RUN apt-get update
RUN apt-get upgrade
RUN apt-get install -y curl php git cron acl php7.0-fpm sudo unzip zip nginx
RUN apt-get install -y php-common php7.0-mbstring php-xml
RUN curl -LO https://deployer.org/deployer.phar
RUN mv deployer.phar /usr/local/bin/dep
RUN chmod +x /usr/local/bin/dep
RUN mkdir -p /var/www/preprod/releases
RUN mkdir -p /var/www/prod/univ-nantes-aviron/releases
RUN mkdir -p /var/www/prod/univ-nantes-aviron/shared/storage

COPY src/nginx/* /etc/nginx/

RUN export COMPOSER_PROCESS_TIMEOUT=1200
RUN mkdir /root/una-workspace

COPY src/deploy.php /root/una-workspace/

RUN cd /root/una-workspace; dep deploy docker-prod
