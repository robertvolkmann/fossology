# FOSSology Dockerfile
# Copyright Siemens AG 2016, fabio.huser@siemens.com
# Copyright TNG Technology Consulting GmbH 2016-2017, maximilian.huber@tngtech.com
#
# Copying and distribution of this file, with or without modification,
# are permitted in any medium without royalty provided the copyright
# notice and this notice are preserved.  This file is offered as-is,
# without any warranty.
#
# Description: Docker container image recipe

FROM ubuntu:xenial as builder

LABEL maintainer="Fossology <fossology@fossology.org>"

WORKDIR /fossology

RUN DEBIAN_FRONTEND=noninteractive apt-get update \
 && DEBIAN_FRONTEND=noninteractive apt-get install -y --no-install-recommends \
      lsb-release \
      sudo \
 && rm -rf /var/lib/apt/lists/*

COPY ./utils/fo-installdeps ./utils/utils.sh /fossology/utils/
COPY ./src/copyright/mod_deps /fossology/src/copyright/
COPY ./src/delagent/mod_deps /fossology/src/delagent/
COPY ./src/mimetype/mod_deps /fossology/src/mimetype/
COPY ./src/pkgagent/mod_deps /fossology/src/pkgagent/
COPY ./src/scheduler/mod_deps /fossology/src/scheduler/
COPY ./src/ununpack/mod_deps /fossology/src/ununpack/
COPY ./src/wget_agent/mod_deps /fossology/src/wget_agent/

RUN mkdir -p /fossology/dependencies-for-runtime \
 && cp -R /fossology/src /fossology/utils /fossology/dependencies-for-runtime/

RUN DEBIAN_FRONTEND=noninteractive apt-get update \
 && DEBIAN_FRONTEND=noninteractive /fossology/utils/fo-installdeps --build -y \
 && rm -rf /var/lib/apt/lists/*

COPY . /fossology/

RUN make install_offline


FROM ubuntu:xenial

LABEL maintainer="Fossology <fossology@fossology.org>"

### install dependencies
COPY --from=builder /fossology/dependencies-for-runtime /fossology

WORKDIR /fossology

COPY ./utils/install_composer.sh ./utils/install_composer.sh

RUN DEBIAN_FRONTEND=noninteractive apt-get update \
 && DEBIAN_FRONTEND=noninteractive apt-get install -y --no-install-recommends \
      curl \
      lsb-release \
      sudo \
 && DEBIAN_FRONTEND=noninteractive /fossology/utils/fo-installdeps --runtime -y \
 && DEBIAN_FRONTEND=noninteractive apt-get purge -y lsb-release \
 && DEBIAN_FRONTEND=noninteractive apt-get autoremove -y \
 && rm -rf /var/lib/apt/lists/*

# configure apache
COPY ./install/src-install-apache-example.conf /etc/apache2/conf-available/fossology.conf
RUN a2enconf fossology.conf \
 && mkdir -p /var/log/apache2/ \
 && ln -sf /proc/self/fd/1 /var/log/apache2/access.log \
 && ln -sf /proc/self/fd/1 /var/log/apache2/error.log

COPY ./docker-entrypoint.sh /fossology/docker-entrypoint.sh
RUN chmod +x /fossology/docker-entrypoint.sh
ENTRYPOINT ["/fossology/docker-entrypoint.sh"]

COPY --from=builder /etc/cron.d/fossology /etc/cron.d/fossology
COPY --from=builder /etc/init.d/fossology /etc/init.d/fossology
COPY --from=builder /usr/local/ /usr/local/

# install composer and configure php
COPY Makefile.conf Makefile.conf
COPY ./src/Makefile ./src/composer.lock ./src/composer.json ./src/
COPY ./install/scripts/php-conf-fix.sh ./install/scripts/php-conf-fix.sh

RUN make -C src composer_install \
 && /fossology/install/scripts/php-conf-fix.sh --overwrite

# the database is filled in the entrypoint
RUN /usr/local/lib/fossology/fo-postinstall --agent --common --scheduler-only --web-only --no-running-database
