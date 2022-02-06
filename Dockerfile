FROM ubuntu:20.04

ENV DEBIAN_FRONTEND noninteractive

RUN export DEBIAN_FRONTEND=${DEBIAN_FRONTEND}
RUN apt-get -qy update && apt-get -qy upgrade && apt-get -qy install software-properties-common unzip wget
RUN add-apt-repository ppa:ondrej/php && apt-get -qy update
RUN apt-get -qy install php8.0-cli php8.0-readline php8.0-mysql php8.0-mbstring php8.0-redis php8.0-amqp php8.0-xml \
    php8.0-intl php8.0-zip php8.0-xhprof php8.0-xdebug php8.0-opcache php8.0-mcrypt php8.0-curl php8.0-gd \
    php8.0-imagick

RUN echo 'xdebug.mode=debug' >> /etc/php/8.0/cli/php.ini
RUN echo 'xdebug.client_host=host.docker.internal' >> /etc/php/8.0/cli/php.ini
RUN echo 'xdebug.client_port=9000' >> /etc/php/8.0/cli/php.inibuil

RUN php -r "copy('https://getcomposer.org/download/latest-stable/composer.phar', '/usr/local/bin/composer');"
RUN chmod 755 /usr/local/bin/composer

RUN adduser --uid 1000 --home /app --shell /bin/bash tinyframework
RUN adduser --gid 1000 tinyframework tinyframework
RUN mkdir -p /app && chown tinyframework:tinyframework /app
RUN echo "PS1='bash$ '" >> /etc/bash.bashrc
RUN echo "PATH=\"\$PATH:/opt/sonar-scanner/bin\"" >> /etc/bash.bashrc
RUN echo "export PHP_IDE_CONFIG=serverName=tinyframework" >> /etc/bash.bashrc
RUN echo "alias phpx=\"php -dxdebug.mode=debug -dxdebug.client_host=host.docker.internal -dxdebug.client_port=9003 -dxdebug.start_with_request=yes \$@\"" >> /etc/bash.bashrc

RUN mkdir -p /opt/sonar-scanner
RUN wget https://binaries.sonarsource.com/Distribution/sonar-scanner-cli/sonar-scanner-cli-4.6.2.2472-linux.zip --output-document=/tmp/sonar-scanner-cli-4.6.2.2472-linux.zip
RUN unzip -d /tmp/ /tmp/sonar-scanner-cli-4.6.2.2472-linux.zip
RUN mv /tmp/sonar-scanner*/* /opt/sonar-scanner
RUN rm -rf /tmp/sonar-scanner*

WORKDIR /app
USER tinyframework

CMD ["sleep", "604800"]
