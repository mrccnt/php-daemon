#!/usr/bin/env bash

if [ "$(id -u)" != "0" ]; then
   echo "This script must be run as root"
   exit 1
fi

systemctl stop php-daemon

[ -d /opt/php-daemon ] && rm -r /opt/php-daemon
[ -L /lib/systemd/system/php-daemon.service ] && rm /lib/systemd/system/php-daemon.service

systemctl daemon-reload
