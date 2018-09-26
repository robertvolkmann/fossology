FROM fossology-builder

WORKDIR /fossology

RUN DEBIAN_FRONTEND=noninteractive apt-get update \
 && DEBIAN_FRONTEND=noninteractive ./utils/fo-installdeps --runtime -y

RUN DEBIAN_FRONTEND=noninteractive apt-get install -y libcppunit-dev default-jre-headless \
 && ./install/scripts/install-spdx-tools.sh

COPY docker-entrypoint.sh /fossology/docker-entrypoint.sh

ENTRYPOINT /fossology/docker-entrypoint.sh
