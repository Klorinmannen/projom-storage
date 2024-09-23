#!/bin/bash

if [ -z "$1" ]; then
	echo "Usage: $0 <username> <database>"
	exit 1
fi
USER=$1

if [ -z "$2" ]; then
	echo "Usage: $0 <username> <database>"
	exit 1
fi
DATABASE=$2

echo -e "\n\n**Installing mariaDB server packages**\n\n"
sudo apt-get update
sudo apt-get -y install mariadb-server
sudo apt-get -y install openssl

bash ./scripts/mariadb_create_database_with_user.sh $USER $DATABASE "localhost" "projom"
if [ $? -ne 0 ]; then
	echo "Error creating database: $DATABASE with user: $USER"
	exit 1
fi

echo -e "Restarting MariaDB server service .. \n"
sudo systemctl restart mariadb.service

sleep 1

exit 0
