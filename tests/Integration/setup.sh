#!/bin/bash

echo -e "\n\n**Installing mariaDB server packages**\n\n"

sudo apt-get update
sudo apt-get -y install mariadb-server
sudo apt-get -y install openssl

sleep 1
exit 0
