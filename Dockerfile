# Imagem PHP + Apache — roda o app tal como ele foi feito (usa o .htaccess).
FROM php:8.2-apache

# Extensões PHP + utilitários necessários.
# git e unzip são exigidos pelo Composer para baixar/extrair os pacotes
# (a imagem base não os traz); pdo_mysql é a extensão do banco.
RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip \
    && docker-php-ext-install pdo pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

# Permite o Composer rodar como root no build sem desabilitar plugins.
ENV COMPOSER_ALLOW_SUPERUSER=1

# Garante um único MPM ativo (prefork, exigido pelo mod_php) — evita o erro
# "More than one MPM loaded" caso o mpm_event/worker também esteja carregado.
RUN a2dismod mpm_event mpm_worker 2>/dev/null || true; \
    a2enmod mpm_prefork

# Habilita o mod_rewrite (o .htaccess depende dele) e permite override do .htaccess.
RUN a2enmod rewrite \
    && sed -ri 's/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# O Apache no Railway/containers deve escutar na porta fornecida via $PORT.
RUN sed -ri 's/^Listen 80$/Listen ${PORT}/' /etc/apache2/ports.conf \
    && sed -ri 's/:80>/:${PORT}>/' /etc/apache2/sites-available/000-default.conf
ENV PORT=8080

# Instala o Composer (versão oficial fixada para builds reproduzíveis).
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Instala dependências primeiro (melhor cache de build).
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --optimize-autoloader

# Copia o restante da aplicação.
COPY . .

# Ajusta permissões da pasta de uploads (será montada como volume persistente).
RUN mkdir -p /var/www/html/uploads \
    && chown -R www-data:www-data /var/www/html

EXPOSE 8080
