FROM ubuntu:20.04

ENV DEBIAN_FRONTEND noninteractive
ENV UID 1000
ENV GID 1000

RUN export DEBIAN_FRONTEND=${DEBIAN_FRONTEND}
RUN apt-get -qy update \
    && apt-get -qy upgrade \
    && apt-get -qy install software-properties-common unzip wget
RUN add-apt-repository ppa:ondrej/php
RUN apt-get -qy update
RUN apt-get -qy install \
    php8.0-cli php8.0-readline php8.0-mysql php8.0-mbstring php8.0-redis php8.0-amqp php8.0-xml php8.0-intl \
    php8.0-zip php8.0-xhprof php8.0-xdebug php8.0-opcache php8.0-mcrypt php8.0-curl php8.0-gd php8.0-imagick \
    php8.0-swoole \
    php8.1-cli php8.1-readline php8.1-mysql php8.1-mbstring php8.1-redis php8.1-amqp php8.1-xml php8.1-intl \
    php8.1-zip php8.1-xhprof php8.1-xdebug php8.1-opcache php8.1-mcrypt php8.1-curl php8.1-gd php8.1-imagick \
    php8.1-swoole

RUN echo 'xdebug.mode=debug' >> /etc/php/8.0/cli/php.ini
RUN echo 'xdebug.client_host=host.docker.internal' >> /etc/php/8.0/cli/php.ini
RUN echo 'xdebug.client_port=9000' >> /etc/php/8.0/cli/php.inibuil

RUN php -r "copy('https://getcomposer.org/download/latest-stable/composer.phar', '/usr/local/bin/composer');"
RUN chmod 755 /usr/local/bin/composer

RUN adduser --uid ${UID} --home /app --shell /bin/bash --gecos "TinyFramework TinyFramework,,," tinyframework --disabled-password
RUN adduser --gid ${GID} tinyframework tinyframework
RUN mkdir -p /app && chown tinyframework:tinyframework /app
RUN echo "PS1='bash$ '" >> /etc/bash.bashrc
RUN echo "PATH=\"\$PATH:/opt/sonar-scanner/bin\"" >> /etc/bash.bashrc
RUN echo "export PHP_IDE_CONFIG=serverName=tinyframework" >> /etc/bash.bashrc
RUN echo "alias phpx8.0=\"php8.0 -dxdebug.mode=debug -dxdebug.client_host=host.docker.internal -dxdebug.client_port=9003 -dxdebug.start_with_request=yes \$@\"" >> /etc/bash.bashrc
RUN echo "alias phpx8.1=\"php8.1 -dxdebug.mode=debug -dxdebug.client_host=host.docker.internal -dxdebug.client_port=9003 -dxdebug.start_with_request=yes \$@\"" >> /etc/bash.bashrc
RUN echo "alias phpx=\"php -dxdebug.mode=debug -dxdebug.client_host=host.docker.internal -dxdebug.client_port=9003 -dxdebug.start_with_request=yes \$@\"" >> /etc/bash.bashrc

RUN mkdir -p /opt/sonar-scanner
RUN wget -q https://binaries.sonarsource.com/Distribution/sonar-scanner-cli/sonar-scanner-cli-4.6.2.2472-linux.zip --output-document=/tmp/sonar-scanner-cli-4.6.2.2472-linux.zip
RUN unzip -d /tmp/ /tmp/sonar-scanner-cli-4.6.2.2472-linux.zip
RUN mv /tmp/sonar-scanner*/* /opt/sonar-scanner
RUN rm -rf /tmp/sonar-scanner*

WORKDIR /app
USER tinyframework

CMD ["sleep", "infinity"]
