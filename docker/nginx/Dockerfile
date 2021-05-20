FROM nginx:latest

RUN ln -sf /usr/share/zoneinfo/Europe/Moscow  /etc/localtime
RUN dpkg-reconfigure -f noninteractive tzdata

RUN apt-get update && apt-get install -y git curl
RUN curl -sL https://deb.nodesource.com/setup_12.x | bash -
RUN apt-get install -y nodejs

RUN npm i -g npm@latest
RUN npm i -g tsd
RUN npm i -g typings

ADD docker/nginx/conf/ /etc/nginx/conf.d/

ADD frontend /var/www/frontend/

WORKDIR /var/www/frontend

RUN chmod 755 -R /var/www
