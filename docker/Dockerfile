FROM amazonlinux:2023

ARG app_user=nginx
ARG app_uid=1111
ARG app_gid=1111

# this needs to happen first. otherwise, nginx will conflict with the chosen ids
RUN yum install --assumeyes shadow-utils \
  && groupadd --system --gid $app_gid $app_user \
  && useradd --system --uid $app_uid --home-dir /var/lib/nginx --gid $app_user $app_user

RUN yum install --assumeyes \
  7zip \
  bind-utils \
  git \
  net-tools \
  nginx \
  php8.1-cli \
  php8.1-fpm \
  php8.1-mbstring \
  procps \
  vim \
  wget \
  zip

WORKDIR /src

COPY docker/files/install-composer /

RUN mkdir /run/php-fpm/ /var/run/nginx
RUN /install-composer \
  && composer require aws/aws-sdk-php

RUN chown --recursive $app_user:$app_user \
  /var/log/php-fpm \
  /var/log/nginx \
  /var/run/nginx \
  /var/run/php-fpm

COPY docker/files/ /
COPY src/ /app
COPY VERSION /app

EXPOSE 8080

USER $app_uid:$app_gid

ENTRYPOINT ["/entrypoint.sh"]
