# ---- Stage 1: fetch, verify and prune MySQL 5.6.51 (generic Linux tarball) ----
FROM alpine:3.19 AS mysql-dl
ARG MYSQL_VERSION=5.6.51
# MD5 as published by Oracle (https://cdn.mysql.com/archives/mysql-5.6/<file>.md5)
ARG MYSQL_MD5=b4a58f228dc5e2d579eb9ce59e566bd5
RUN apk add --no-cache curl \
    && F=mysql-${MYSQL_VERSION}-linux-glibc2.12-x86_64.tar.gz \
    && curl -fsSL -o /tmp/mysql.tgz https://cdn.mysql.com/archives/mysql-5.6/$F \
    && echo "${MYSQL_MD5}  /tmp/mysql.tgz" | md5sum -c - \
    && mkdir -p /usr/local/mysql \
    && tar -xzf /tmp/mysql.tgz -C /usr/local/mysql --strip-components=1 \
    && cd /usr/local/mysql \
    && rm -rf mysql-test sql-bench docs man include data lib/*.a lib/plugin/debug \
              bin/*-debug bin/mysqlslap bin/mysql_embedded bin/mysqltest* bin/mysql_client_test* \
              bin/mysqlimport bin/mysqldumpslow bin/mysql_plugin bin/innochecksum \
              bin/mysql_config* bin/mysqlaccess* bin/mysqlhotcopy bin/mysql_zap

# ---- Stage 2: runtime ----
FROM php:7.4.33-apache

# System libs for the PHP extensions (Debian bullseye)
# Bullseye is EOL: repos moved to archive.debian.org
RUN sed -i -e 's|deb.debian.org/debian-security|archive.debian.org/debian-security|g' \
           -e 's|deb.debian.org/debian|archive.debian.org/debian|g' \
           -e '/bullseye-updates/d' /etc/apt/sources.list \
    && apt-get -o Acquire::Check-Valid-Until=false update && apt-get install -y --no-install-recommends \
        libjpeg-dev libpng-dev libfreetype6-dev libxml2-dev libonig-dev libcurl4-openssl-dev libssl-dev \
        libaio1 libnuma1 libncurses5 perl supervisor \
    && rm -rf /var/lib/apt/lists/*

# json, openssl are built in; the rest are compiled here
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" mysqli gd soap mbstring dom curl simplexml

RUN a2enmod rewrite

# MySQL 5.6 (pruned) + service user + runtime dirs
COPY --from=mysql-dl /usr/local/mysql /usr/local/mysql
RUN groupadd -r mysql && useradd -r -g mysql -s /usr/sbin/nologin -d /var/lib/mysql mysql \
    && mkdir -p /var/lib/mysql /var/run/mysqld \
    && chown mysql:mysql /var/lib/mysql /var/run/mysqld
ENV PATH="/usr/local/mysql/bin:${PATH}"

# Apache on 8080 (single exposed port)
RUN sed -i -e 's/Listen 80$/Listen 8080/' -e 's/Listen 443$/Listen 8443/' /etc/apache2/ports.conf

COPY docker/my.cnf /etc/my.cnf
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/zz-app.ini
COPY docker/apache-vhost.conf /etc/apache2/sites-available/000-default.conf

# lib/ must sit next to public_html/ (relative requires: ../../../lib/)
COPY lib/ /var/www/lib/
COPY public_html/ /var/www/public_html/

# Optional SQL dumps baked into the image; imported on first start
COPY docker/initdb/ /docker-entrypoint-initdb.d/

# Demo game images staged outside the data volume; copied into place on first start.
# Resized variants (224x105, 240x112, 320x149) are generated once at build time.
COPY docker/seed-assets/ /opt/seed-assets/
COPY docker/gen-thumbs.php /tmp/gen-thumbs.php
RUN if [ -d /opt/seed-assets/game_imgs ]; then php /tmp/gen-thumbs.php /opt/seed-assets/game_imgs; fi \
    && rm -f /tmp/gen-thumbs.php

COPY docker/config.example.php /usr/local/share/config.example.php
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Demo-only defaults; override at runtime (docker run -e ...)
ENV DB_HOST=127.0.0.1 \
    DB_NAME=steambuy \
    DB_USER=steambuy \
    DB_PASSWORD=steambuy-demo \
    MYSQL_ROOT_PASSWORD=root-demo

# Only the DB datadir: a persistent volume keeps data across container
# recreation; without one the data just lives in the container layer.
VOLUME /var/lib/mysql

EXPOSE 8080
ENTRYPOINT ["entrypoint.sh"]
CMD ["supervisord", "-n", "-c", "/etc/supervisord.conf"]
