#!/bin/sh

inotifywait -m --timefmt '%d/%m/%y %H:%M' --format '%T %w %f' /var/log/apcupsd.status | while read date time dir file; do
  echo "$date $time $dir $file"
done
