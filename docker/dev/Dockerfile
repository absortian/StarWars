# Empezamos cargando la imágen base
FROM ubuntu:18.04
# Indicamos que la terminal debe ser no interactiva
ENV DEBIAN_FRONTEND=noninteractive
# Actualizamos repos e instalamos software que necesitamos
RUN apt update && apt-get install -y software-properties-common \
 && apt-add-repository ppa:ondrej/php
RUN apt update && apt install -yq --no-install-recommends fontconfig xfonts-base libssl-dev xfonts-75dpi apache2 php7.2 php7.2-odbc php7.2-readline php7.2-opcache php7.2-xml php7.2-gd php7.2-json php7.2-mysql php7.2-dom php7.2-intl php7.2-curl php7.2-zip php7.2-mbstring curl ca-certificates xz-utils libfontconfig1 libxrender1 libxext6 wget unixodbc unixodbc-dev unixodbc-bin libodbc1 odbcinst1debian2 tdsodbc freetds-bin freetds-common freetds-dev libct4 libsybdb5
# Activo modulos de apache2
RUN a2enmod ssl rewrite php7.2
# Instalo composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
# Añado en el sistema la configuración de apache
RUN rm /etc/apache2/sites-enabled/*.conf
RUN rm -r /var/www/html
COPY ./aszendefleet.conf /etc/apache2/sites-enabled/aszendefleet.conf
# Añado en el sistema el fichero de configuraciónes extra de php.ini
COPY ./extra-conf.ini /etc/php/7.2/apache2/conf.d/extra-conf.ini
# Añado config de librería de conexión a BBDD SAP
COPY ./odbcinst.ini /etc/odbcinst.ini
# Cambiamos carpeta de inicio
WORKDIR /var/www
# Fuerzo que ejecute un comando que se quede leyendo información indefinidamente, si el comando parase, la imagen también
CMD service apache2 start && tail -F /var/log/apache2/error.log
