#!/usr/bin/env bash

if [ "$(id -u)" != "0" ]; then
   echo "This script must be run as root"
   exit 1
fi

curdir=$(pwd)

if [ ! -f ${curdir}/dist/php-daemon.phar ]; then
    echo "You need to build the .phar file before installing it"
    exit 1
fi

[ ! -d /opt/php-daemon ] && mkdir /opt/php-daemon

[ -L /opt/php-daemon/php-daemon ] && rm /opt/php-daemon/php-daemon
[ -L /lib/systemd/system/php-daemon.service ] && rm /lib/systemd/system/php-daemon.service

ln -s ${curdir}/php-daemon.service /lib/systemd/system/php-daemon.service
ln -s ${curdir}/dist/php-daemon.phar /opt/php-daemon/php-daemon
chmod +x ${curdir}/dist/php-daemon.phar

systemctl daemon-reload

systemctl start php-daemon
systemctl status php-daemon