FROM php:7.1-alpine

EXPOSE 8000
WORKDIR /app

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions pdo_mysql zip

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php composer-setup.php
RUN php -r "unlink('composer-setup.php');"

COPY . .
RUN mv app/config/parameters.docker.yml app/config/parameters.yml
RUN php composer.phar install
RUN php bin/console ckeditor:install
RUN php bin/console assets:install web
RUN mkdir web/uploads
RUN mkdir web/uploads/user_images

ENTRYPOINT ["php", "bin/console", "server:run", "0.0.0.0:8000"]