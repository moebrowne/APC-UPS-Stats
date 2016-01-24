#!/bin/bash

. ./libs/md5sum.sh

#STATUS_FILE=/var/log/apcupsd.status
STATUS_FILE=./apcupsd.status
STATUS_FILE_HASH=$(hashFile "$STATUS_FILE")

SQL_USER='APCUPSStats'
SQL_PASS='bm6+h7%w_gn!4'
SQL_DB_NAME='APCUPS'
SQL_TABLE_NAME='stats'

declare -A STATUS_DATA
declare -a STATUS_DATA_FIELDS

STATUS_DATA_FIELDS=('HOSTNAME' 'UPSNAME' 'DATE' 'STATUS' 'LINEV' 'OUTPUTV' 'LOADPCT')

inotifywait -e modify -m "$STATUS_FILE" | while read data; do

	# Check if the file actually changed contents
	HASH=$(hashFile "$STATUS_FILE")
	if [ "$HASH" = "$STATUS_FILE_HASH" ]; then
		continue;
	fi

	# Update the status file hash
	STATUS_FILE_HASH="$HASH"

	# Get the contents of the file
	STATUS_DATA_RAW=$(cat "$STATUS_FILE")

	# Get all the requested fields
	while read line; do
		for field in "${STATUS_DATA_FIELDS[@]}"; do
			regex="^$field[[:space:]]*: (.*)$"
			[[ $line =~ $regex ]]

			if [[ ${BASH_REMATCH[@]} == '' ]]; then
				# Nothing matched the regex
				continue
			fi

			# Store the value
			STATUS_DATA[$field]=${BASH_REMATCH[1]}

		done
	done < "$STATUS_FILE"

	echo "$data $HASH"

	# Convert the date string to a UNIX timestamp
	STATUS_DATA['DATE']=$(date --date="${STATUS_DATA['DATE']}" +%s)

	# Generate a MySQL query to store the data
	SQLFieldList=''
	SQLValueList=''
	for k in "${!STATUS_DATA[@]}"; do
		SQLFieldList="\`$k\`,$SQLFieldList"
		SQLValueList="'${STATUS_DATA[$k]}',$SQLValueList"
	done

	# Trim the trailing commas
	SQLFieldList=${SQLFieldList::-1}
	SQLValueList=${SQLValueList::-1}

	SQLQuery="INSERT INTO $SQL_DB_NAME.$SQL_TABLE_NAME ($SQLFieldList) VALUES ($SQLValueList)"

	echo "SQL EXEC: $SQLQuery"

	mysql --user="$SQL_USER" --password="$SQL_PASS" "$SQL_DB_NAME" -e "$SQLQuery"

	# Show all the data
	for k in "${!STATUS_DATA[@]}"; do
		echo "$k => ${STATUS_DATA[$k]}"
	done | column -t -s '=>'

done