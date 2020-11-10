#!/bin/sh

# Debug ouput
#set -x

GAME="$1"
WARNINGS=0

if [ -z "$ERESSEA" ] ; then
  ERESSEA="$HOME/eressea"
fi
PYTHON_HOME="$ERESSEA/server/bin"
DBTOOL_HOME="$ERESSEA/orders-php"
GAME_HOME="$ERESSEA/game-$GAME"
TEXTDOMAIN="orders"
TEXTDOMAINDIR="$DBTOOL_HOME/locale"

export TEXTDOMAINDIR

GETTEXT() {
  gettext "$TEXTDOMAIN" "$*"
}

checkpass() {
  FACTION="$1"
  PASSWORD="$2"
  if [ -n "$PASSWORD" ]
  then
    if "$PYTHON_HOME/checkpasswd.py" "$GAME_HOME/eressea.db" "$FACTION" "$PASSWORD"
    then
      return 0
    fi
  fi
  # shellcheck disable=SC2059
  printf "$(GETTEXT 'WARNING: Unknown faction %s or invalid password!')\n" "$FACTION"
  WARNINGS=1
  return 1
}

check() {
  LANGUAGE="$1"
  FILENAME="$2"
  if ["$1"=="de"] ; then
    LC_ALL=de_DE.UTF-8
  else
    LC_ALL=en_US.UTF-8
  fi
  "echeck" -w0 -x -R "e$GAME" -L "$LANGUAGE" "$FILENAME"
}

orders() {
  php "$DBTOOL_HOME/cli.php" "$@"
}

OUTPUT=$(mktemp)
cd "$ERESSEA/game-$GAME/orders.dir" || exit
orders -d orders.db select | while read -r LANGUAGE EMAIL FILENAME ; do
  export LANGUAGE
  SUBJECT="$(GETTEXT 'orders received')"
  mkfifo check.pipe
  orders info "$FILENAME" > check.pipe &
  while read -r FACTION PASSWORD ; do
    checkpass "$FACTION" "$PASSWORD" >> "$OUTPUT" 2>&1
  done < check.pipe
  rm -f check.pipe
  check "$LANGUAGE" "$FILENAME" >> "$OUTPUT" 2>&1
  orders update "$FILENAME" 2
  if [ $WARNINGS -gt 0 ] ; then
    SUBJECT="$(GETTEXT 'orders received (warning)')"
  fi
  mutt -s "[E$GAME] $SUBJECT" "$EMAIL" < "$OUTPUT"
done
