FROM php:8.2

ENV COMPOSER_MEMORY_LIMIT='-1'

ARG env
ARG user
ARG uid

RUN apt-get update && \
    apt-get install -y --force-yes --no-install-recommends \
        cron \
        libzip-dev \
        libcurl4-openssl-dev \
        unzip \
        git \
        mariadb-client

# Install extentions
RUN docker-php-ext-install pdo_mysql zip curl

#####################################
# xDebug:
#####################################
RUN if [ "$env" = "local" ] ; then pecl install xdebug && docker-php-ext-enable xdebug ; fi

#####################################
# Composer:
#####################################
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create user to run Composer and Artisan Commands
RUN useradd -G www-data,root -u $uid -d /home/$user $user
RUN mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user && \
    mkdir /app && \
    chown -R $user:$user /app


# Add crontab file in the cron directory for calling home
COPY ./crontab /etc/cron.d/cron
COPY ./cron.allow /etc/cron.d/cron.allow

RUN touch /var/run/crond.pid && \
	chmod gu+rw /var/run && \
    chmod gu+s /usr/sbin/cron

# Give execution rights on the cron job
RUN chmod 0644 /etc/cron.d/cron && \
    crontab -u $user /etc/cron.d/cron

RUN rm -r /var/lib/apt/lists/*

WORKDIR /app

USER $user

