FROM php:7.2-apache

# Install TYPO3
RUN apt-get update && \
    apt-get install -y --no-install-recommends \
        wget \
        git \
        mysql-client \
        unzip

# Configure PHP
RUN apt-get install -y --no-install-recommends \
        libxml2-dev libfreetype6-dev \
        libjpeg62-turbo-dev \
        libmcrypt-dev \
        libpng-dev \
        zlib1g-dev

# Install required 3rd party tools
RUN apt-get install -y --no-install-recommends \
        graphicsmagick

# Configure extensions
RUN docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ && \
    docker-php-ext-install -j$(nproc) mysqli soap gd zip opcache intl && \
    echo 'always_populate_raw_post_data = -1\nmax_execution_time = 240\nmax_input_vars = 1500\nupload_max_filesize = 32M\npost_max_size = 32M' > /usr/local/etc/php/conf.d/typo3.ini

# Configure Apache as needed
RUN a2enmod rewrite && \
    apt-get clean && \
    apt-get -y purge \
        libxml2-dev libfreetype6-dev \
        libjpeg62-turbo-dev \
        libmcrypt-dev \
        libpng12-dev \
        zlib1g-dev

RUN rm -rf /var/lib/apt/lists/* /usr/src/*

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN a2enmod rewrite
ADD ./docker/typo3.conf /etc/apache2/sites-enabled/000-default.conf

# Adjust some php settings
ADD ./docker/typo3.php.ini /etc/php5/conf.d/

ADD ./docker/AdditionalConfiguration.php /var/www/html/typo3conf/

# Expose environment variables
ENV DB_HOST **LinkMe**
ENV DB_PORT **LinkMe**
ENV DB_NAME typo3
ENV DB_USER admin
ENV DB_PASS **ChangeMe**
ENV INSTALL_TOOL_PASSWORD password
ENV TYPO3_CONTEXT Development

ADD ./docker/run.sh /run.sh
RUN chmod 755 /*.sh

# Configurate apache
ADD ./docker/000-default.conf /etc/apache2/sites-available/000-default.conf

# Install app
WORKDIR /var/www/html/

# Install dependencies defined in composer.json
ADD ./docker/composer.json .
#ADD ./docker/composer.lock .
RUN composer config extra.typo3/cms.cms-package-dir '{$vendor-dir}/typo3/cms' && \
    composer config secure-http false && \
    composer install

# Install plugin
ADD ./ec_styla ./typo3conf/ext/ec_styla
#WORKDIR /var/www/html/typo3conf/ext/ec_styla
#RUN composer config secure-http false && \
#    composer install

# Add other files
WORKDIR /var/www/html/
ADD ./docker/dump.sql .

# Configure volumes
VOLUME /var/www/html/fileadmin
VOLUME /var/www/html/typo3conf
VOLUME /var/www/html/typo3temp
VOLUME /var/www/html/uploads
VOLUME /var/www/html/typo3conf/ext/ec-styla

CMD ["/bin/bash", "-c", "/run.sh"]
