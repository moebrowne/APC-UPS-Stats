#!/bin/sh

. ./libs/md5sum.sh

#STATUS_FILE=/var/log/apcupsd.status
STATUS_FILE=./apcupsd.status
STATUS_FILE_HASH=$(hashFile "$STATUS_FILE")

inotifywait -e modify -m "$STATUS_FILE" | while read data; do

  # Check if the file actually changed contents
  HASH=$(hashFile "$STATUS_FILE")
  if [ "$HASH" = "$STATUS_FILE_HASH" ]; then
    continue;
  fi

  STATUS_FILE_HASH="$HASH"

  echo "$data $HASH"
done