FROM ubuntu:22.04

ENV DEBIAN_FRONTEND noninteractive
ARG _UID=1000
ARG _GID=1000

RUN export DEBIAN_FRONTEND=${DEBIAN_FRONTEND}
RUN apt-get -qy update \
    && apt-get -qy upgrade \
    && apt-get -qy install software-properties-common unzip wget
RUN add-apt-repository ppa:ondrej/php
RUN apt-get -qy update
RUN apt-get -qy install \
    php-sodium \
    php8.0-cli php8.0-readline php8.0-mysql php8.0-mbstring php8.0-redis php8.0-amqp php8.0-xml php8.0-intl \
    php8.0-zip php8.0-xhprof php8.0-xdebug php8.0-opcache php8.0-mcrypt php8.0-curl php8.0-gd php8.0-imagick \
    php8.0-swoole \
    php8.1-cli php8.1-readline php8.1-mysql php8.1-mbstring php8.1-redis php8.1-amqp php8.1-xml php8.1-intl \
    php8.1-zip php8.1-xhprof php8.1-xdebug php8.1-opcache php8.1-mcrypt php8.1-curl php8.1-gd php8.1-imagick \
    php8.1-swoole \
    php8.2-cli php8.2-readline php8.2-mysql php8.2-mbstring php8.2-redis php8.2-amqp php8.2-xml php8.2-intl \
    php8.2-zip php8.2-xhprof php8.2-xdebug php8.2-opcache php8.2-mcrypt php8.2-curl php8.2-gd php8.2-imagick \
    php8.2-swoole
RUN /usr/bin/update-alternatives --set php /usr/bin/php8.1

RUN echo 'xdebug.mode=debug' >> /etc/php/8.0/cli/php.ini
RUN echo 'xdebug.client_host=host.docker.internal' >> /etc/php/8.0/cli/php.ini
RUN echo 'xdebug.client_port=9000' >> /etc/php/8.0/cli/php.ini
RUN echo 'xdebug.mode=debug' >> /etc/php/8.1/cli/php.ini
RUN echo 'xdebug.client_host=host.docker.internal' >> /etc/php/8.1/cli/php.ini
RUN echo 'xdebug.client_port=9000' >> /etc/php/8.1/cli/php.ini
RUN echo 'xdebug.mode=debug' >> /etc/php/8.2/cli/php.ini
RUN echo 'xdebug.client_host=host.docker.internal' >> /etc/php/8.2/cli/php.ini
RUN echo 'xdebug.client_port=9000' >> /etc/php/8.2/cli/php.ini

RUN php -r "copy('https://getcomposer.org/download/latest-stable/composer.phar', '/usr/local/bin/composer');"
RUN chmod 755 /usr/local/bin/composer

RUN adduser --uid ${_UID} --home /home/app --shell /bin/bash --gecos "TinyFramework TinyFramework,,," tinyframework --disabled-password
RUN adduser --gid ${_GID} tinyframework tinyframework
RUN mkdir -p /app /home/app && chown tinyframework:tinyframework /app /home/app
RUN echo "PS1='bash$ '" >> /etc/bash.bashrc
RUN echo "PATH=\"\$PATH:/opt/sonar-scanner/bin\"" >> /etc/bash.bashrc
RUN echo "export PHP_IDE_CONFIG=serverName=tinyframework" >> /etc/bash.bashrc
RUN echo "alias phpx8.0=\"php8.0 -dxdebug.mode=debug -dxdebug.client_host=host.docker.internal -dxdebug.client_port=9003 -dxdebug.start_with_request=yes \$@\"" >> /etc/bash.bashrc
RUN echo "alias phpx8.1=\"php8.1 -dxdebug.mode=debug -dxdebug.client_host=host.docker.internal -dxdebug.client_port=9003 -dxdebug.start_with_request=yes \$@\"" >> /etc/bash.bashrc
RUN echo "alias phpx8.2=\"php8.2 -dxdebug.mode=debug -dxdebug.client_host=host.docker.internal -dxdebug.client_port=9003 -dxdebug.start_with_request=yes \$@\"" >> /etc/bash.bashrc
RUN echo "alias phpx=\"php -dxdebug.mode=debug -dxdebug.client_host=host.docker.internal -dxdebug.client_port=9003 -dxdebug.start_with_request=yes \$@\"" >> /etc/bash.bashrc

RUN mkdir -p /opt/sonar-scanner
RUN wget -q https://binaries.sonarsource.com/Distribution/sonar-scanner-cli/sonar-scanner-cli-4.6.2.2472-linux.zip --output-document=/tmp/sonar-scanner-cli-4.6.2.2472-linux.zip
RUN unzip -d /tmp/ /tmp/sonar-scanner-cli-4.6.2.2472-linux.zip
RUN mv /tmp/sonar-scanner*/* /opt/sonar-scanner
RUN rm -rf /tmp/sonar-scanner*

WORKDIR /app
USER tinyframework

CMD ["sleep", "infinity"]
