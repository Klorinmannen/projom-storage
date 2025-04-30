#!/bin/bash

if [ -z "$1" ]; then
	echo "Usage: $0 <username> <database_name> [REMOTE = localhost] [password = random]"
	exit 1
fi
USERNAME=$1

PASSWORD=$(openssl rand -base64 12);
PASSWORD=${PASSWORD:0:8}
if [ -n "$2" ]; then
	PASSWORD=$2
fi

if [ -z "$3" ]; then
	echo "Usage: $0 <username> <database_name> [REMOTE = localhost] [password = random]"
	exit 1
fi
DATABASE_NAME=$3

REMOTE="localhost"

echo "Creating database: $DATABASE_NAME .."
sudo mysql --password=root -e "CREATE DATABASE $DATABASE_NAME;"
if [ $? -ne 0 ]; then
	echo "Error creating database: $DATABASE_NAME"
	exit 1
fi

echo "Creating user: $USERNAME @ $REMOTE with password: $PASSWORD .."
sudo mysql --password=root -e "CREATE USER '$USERNAME'@'$REMOTE' IDENTIFIED BY '$PASSWORD';"
if [ $? -ne 0 ]; then
	echo "Error creating user: $USERNAME @ $REMOTE with password: $PASSWORD"
	exit 1
fi

echo -e "Setting up grants on $DATABASE_NAME.* for $USERNAME @ $REMOTE .. \n"
sudo mysql -e "GRANT DROP, CREATE, CREATE TEMPORARY TABLES ON $DATABASE_NAME.* TO '$USERNAME'@'$REMOTE';"
sudo mysql -e "GRANT SELECT, INSERT, UPDATE, DELETE, EXECUTE ON $DATABASE_NAME.* TO '$USERNAME'@'$REMOTE';"
if [ $? -ne 0 ]; then
	echo "Error setting grants: $DATABASE_NAME.* TO $USERNAME @ $REMOTE"
	exit 1
fi

sudo mysql -e "FLUSH PRIVILEGES;"

echo -e "\n\n***SAVE THIS INFORMATION!***"
echo "User: $USERNAME @ $REMOTE, Password: $PASSWORD, Database: $DATABASE_NAME"
echo -e "***SAVE THIS INFORMATION***\n\n"

sleep 1

echo -e "Restarting MariaDB server service .. \n"
sudo systemctl restart mariadb.service

sleep 1
exit 0
