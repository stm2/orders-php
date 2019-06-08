#!/bin/sh

# Debug ouput
# set -x

GAME="$1"

if [ -z "$ERESSEA" ] ; then
  ERESSEA="$HOME/eressea"
fi
ECHECK_HOME="$HOME/echeck"
PYTHON_HOME="$ERESSEA/server/bin"
DBTOOL_HOME="$ERESSEA/orders-php"
GAME_HOME="$ERESSEA/game-$GAME"

checkpass() {
  FACTION="$1"
  PASSWORD="$2"
  if [ -z "$PASSWORD" ] ; then
    return 1
  fi
  "$PYTHON_HOME/checkpasswd.py" "$GAME_HOME/eressea.db" "$FACTION" "$PASSWORD"
}

echeck() {
  LANGUAGE="$1"
  FILENAME="$2"
  "$ECHECK_HOME/echeck" -w0 -x -P"$ECHECK_HOME" -R "e$GAME" -L "$LANGUAGE" "$FILENAME"
}

orders() {
  php "$DBTOOL_HOME/cli.php" "$@"
}

OUTPUT=$(mktemp)
cd "$ERESSEA/game-$GAME/orders.dir" || exit
orders -d orders.db select | while read -r LANGUAGE EMAIL FILENAME ; do
  orders info "$FILENAME" | while read -r FACTION PASSWORD ; do
    checkpass "$FACTION" "$PASSWORD" > "$OUTPUT" 2>&1
  done
  echeck "$LANGUAGE" "$FILENAME" >> "$OUTPUT" 2>&1
  orders update "$FILENAME" 2
  mutt -s "Befehle angekommen" "$EMAIL" < "$OUTPUT"
done
