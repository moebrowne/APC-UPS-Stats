#!/bin/sh

#STATUS_FILE=/var/log/apcupsd.status
STATUS_FILE=./apcupsd.status

inotifywait -e modify -m "$STATUS_FILE" | while read data; do
  echo "$data"
done
