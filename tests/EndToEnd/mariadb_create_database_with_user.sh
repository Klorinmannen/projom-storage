#!/bin/bash

if [ -z "$1" ]; then
	echo "Usage: $0 <username> <database_name> [remote_ip = localhost] [password = random]"
	exit 1
fi
USERNAME=$1

if [ -z "$2" ]; then
	echo "Usage: $0 <username> <database_name> [remote_ip = localhost] [password = random]"
	exit 1
fi
DATABASE_NAME=$2

REMOTE_IP="localhost"
if [ -n "$3" ]; then
	REMOTE_IP=$3
fi

PASSWORD=$(openssl rand -base64 12);
PASSWORD=${PASSWORD:0:8}
if [ -n "$4" ]; then
	PASSWORD=$4
fi

echo "Creating database: $DATABASE_NAME .."
sudo mysql --password=root -e "CREATE DATABASE $DATABASE_NAME;"
if [ $? -ne 0 ]; then
	echo "Error creating database: $DATABASE_NAME"
	exit 1
fi

echo "Creating user: $USERNAME @ $REMOTE_IP with password: $PASSWORD .."
sudo mysql --password=root -e "CREATE USER '$USERNAME'@'$REMOTE_IP' IDENTIFIED BY '$PASSWORD';"
if [ $? -ne 0 ]; then
	echo "Error creating user: $USERNAME @ $REMOTE_IP with password: $PASSWORD"
	exit 1
fi

echo -e "Setting up grants on $DATABASE_NAME.* for $USERNAME @ $REMOTE_IP .. \n"
sudo mysql -e "GRANT CREATE, CREATE TEMPORARY TABLES ON $DATABASE_NAME.* TO '$USERNAME'@'$REMOTE_IP';"
sudo mysql -e "GRANT SELECT, INSERT, UPDATE, DELETE, EXECUTE ON $DATABASE_NAME.* TO '$USERNAME'@'$REMOTE_IP';"
if [ $? -ne 0 ]; then
	echo "Error setting grants: $DATABASE_NAME.* TO $USERNAME @ $REMOTE_IP"
	exit 1
fi

sudo mysql -e "FLUSH PRIVILEGES;"

echo -e "\n\n***SAVE THIS INFORMATION!***"
echo "User: $USERNAME @ $REMOTE_IP, Password: $PASSWORD, Database: $DATABASE_NAME"
echo -e "***SAVE THIS INFORMATION***\n\n"

exit 0
