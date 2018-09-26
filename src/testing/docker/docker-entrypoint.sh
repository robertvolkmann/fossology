#!/usr/bin/env bash
set -o errexit -o nounset -o pipefail

/etc/init.d/postgresql start

DEBIAN_FRONTEND=noninteractive ./utils/prepare-test -afty

if lsb_release --codename --short | grep -q -e 'jessie'; then
  DEBIAN_FRONTEND=noninteractive apt-get install -y php5-sqlite
else
  DEBIAN_FRONTEND=noninteractive apt-get install -y php-sqlite3
fi

if lsb_release --codename --short | grep -q -e 'jessie' -e 'xenial'; then
  composer install --prefer-dist --working-dir=src
else
  composer composer update --ignore-platform-reqs --with-dependencies --prefer-dist --working-dir=src phpunit/phpunit
fi

PGHOST=localhost make test

./src/vendor/bin/phpunit -csrc/phpunit.xml --testsuite="Fossology PhpUnit Test Suite"
