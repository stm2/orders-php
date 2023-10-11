#!/bin/bash
# usage: check-orders [-m <muttrc>] [-d] <game-id>
set -euo pipefail

DEBUG=0

abort() {
  echo "$1"
  exit 1
}

[ -n "${ERESSEA+x}" ] || abort "ERESSEA environment variable not set"

MUTTRC=
while getopts dm: o; do
  case "${o}" in
  d) DEBUG=1 ;;
  m) MUTTRC=$OPTARG ;;
  *) echo "unknown option -${o}" ;;
  esac
done
shift $((OPTIND-1))

MUTT=mutt
if [ $DEBUG -gt 0 ]; then
  set -x
  MUTT="$ERESSEA/bin/fake/mutt"
fi

[ -z "${1+x}" ] && abort "missing game parameter"
[ -n "${2+x}" ] && abort "too many parameters"

PYTHON_HOME="$ERESSEA/server/bin"
DBTOOL_HOME="$ERESSEA/orders-php"
TEXTDOMAIN="orders"
TEXTDOMAINDIR="$DBTOOL_HOME/locale"
INIPARSER="$ERESSEA/server/bin/inifile"

GAME="$1"
GAME_HOME="$ERESSEA/game-$GAME"
WARNINGS=0
INIFILE="$GAME_HOME/eressea.ini"

ECHECK=
if command -v echeck &> /dev/null; then
  ECHECK=echeck
  ECHECKDIR=
else
  if [ -x "$ERESSEA/echeck/out/echeck" ]; then
    ECHECK=out/echeck
    ECHECKDIR=$ERESSEA/echeck
  fi
fi

export TEXTDOMAINDIR

GETTEXT() {
  gettext "$TEXTDOMAIN" "$*"
}

checkpass() {
  unset FACTION PASSWORD
  FACTION="$1"
  PASSWORD="$2"
  OUTPUT="$3"
  if [ -n "$PASSWORD" ]
  then
    if "$PYTHON_HOME/checkpasswd.py" "$GAME_HOME/eressea.db" "$FACTION" "$PASSWORD"
    then
      return 0
    fi
  fi
  # shellcheck disable=SC2059
  printf "$(GETTEXT 'WARNING: Unknown faction %s or invalid password!')\\n" "$FACTION" >> "$OUTPUT"
  WARNINGS=1
  return 1
}

check() {
  RULES="$1"
  LANGUAGE="$2"
  FILENAME="$3"
  if [ -n "$ECHECK" ]; then
    if [ -z "$ECHECKDIR" ]; then
      $ECHECK -w1 -x -R "$RULES" -L "$LANGUAGE" "$FILENAME" >> "$OUTPUT" 2>&1 || true
    else
      ORDERSTMP=$(mktemp -t "${EMAIL}-XXXXXX")
      cat "$FILENAME" > "$ORDERSTMP"
      cd "$ECHECKDIR"
      "$ECHECK" -w1 -x -R "$RULES" -L "$LANGUAGE" "$ORDERSTMP" >> "$OUTPUT" 2>&1 || true
      rm "$ORDERSTMP"
      cd "$OLDPWD"
    fi
  else
    # shellcheck disable=SC2059
    printf "$(GETTEXT 'ECheck not installed, could not check orders %s.')\\n" "$FILENAME" >> "$OUTPUT"
  fi
}

orders() {
  php "$DBTOOL_HOME/cli.php" "$@"
}

[ -x "$INIPARSER" ] || abort "inifile not found"
[ -d "$GAME_HOME" ] || abort "game directory $GAME_HOME not found"
[ -r "$INIFILE" ] || abort "$INIFILE not found"

RULES=$($INIPARSER "$GAME_HOME/eressea.ini" get lua:rules)
[ -n "$RULES" ] || abort "rules not found"

cd "$ERESSEA/game-$GAME/orders.dir" || exit

next=$(orders -d orders.db select)
while [ -n "$next" ]; do
unset LANGUAGE EMAIL FILENAME
while read -r LANGUAGE EMAIL FILENAME; do
  OUTPUT=$(mktemp)

  [[ -n "$LANGUAGE" && -n "$EMAIL" && -n "$FILENAME" ]] || abort  "invalid line $LANGUAGE $EMAIL $FILENAME"
  export LANGUAGE
  SUBJECT="$(GETTEXT 'orders received')"
  found=0

  if [ -e "$GAME_HOME/eressea.db" ]; then
    factions=$(orders info "$FILENAME")
    while read -r FACTION PASSWORD ; do
      if checkpass "$FACTION" "$PASSWORD" "$OUTPUT"; then
        found=1
      fi
    done <<< "$factions"
  else
    echo "Cannot check password." >> "$OUTPUT"
  fi

  if [ $found -gt 0 ]; then
    check "$RULES" "$LANGUAGE" "$FILENAME" "$OUTPUT"
  else
    WARNINGS=1
    # shellcheck disable=SC2059
    printf "$(GETTEXT 'WARNING: Unknown faction or invalid password in %s!')\\n" "$FILENAME" >> "$OUTPUT" 
  fi

  orders update "$FILENAME" 2

  if [ $WARNINGS -gt 0 ] ; then
    SUBJECT="$(GETTEXT 'orders received (warning)')"
  fi
  if [ -n "$MUTTRC" ]; then
    "$MUTT" -F "$MUTTRC" -s "[$GAME] $SUBJECT" "$EMAIL" < "$OUTPUT"
  else
    "$MUTT" -s "[$GAME] $SUBJECT" "$EMAIL" < "$OUTPUT"
  fi
  rm "$OUTPUT"
done <<< "$next"
next=$(orders -d orders.db select)
done
