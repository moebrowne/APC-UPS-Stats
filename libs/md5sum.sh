
hashFile() {
  local hash=$(md5sum "$1" | awk '{ print $1 }');
  echo "$hash"
}