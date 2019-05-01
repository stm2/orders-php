#!/bin/sh
SCRIPT=../../orders-php/cli.php
for FILE in "$@"
do
  EMAIL=$(echo "$FILE" | sed 's/turn-\([^,]*\),.*/\1/')
  php $SCRIPT insert "$FILE" "$EMAIL"
done

