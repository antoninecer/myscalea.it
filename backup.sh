#!/usr/bin/env bash
# ============================
# backup.sh
# ============================
# Popis:
#   Zálohuje jen soubory v kořeni projektu (bez podadresářů)
#   a rekurzivně soubory v explicitly vyjmenovaných složkách.
#   Před přepsáním výstupního souboru se dotáže.
#   Při běhu echo-uje každý adresář i soubor.

set -euo pipefail
IFS=$'\n\t'

# 1) Rozbor argumentů
if [ $# -eq 1 ]; then
  PROJECT_DIR="$1"
  BACKUP_DIR="/opt/backup"
  BACKUP_NAME="backup.txt"
elif [ $# -eq 2 ]; then
  PROJECT_DIR="$1"
  FULL_PATH="$2"
  BACKUP_DIR="$(dirname "$FULL_PATH")"
  BACKUP_NAME="$(basename "$FULL_PATH")"
else
  PROJECT_DIR="${1:-.}"
  BACKUP_DIR="${2:-/opt/backup}"
  BACKUP_NAME="${3:-backup.txt}"
fi
BACKUP_FILE="$BACKUP_DIR/$BACKUP_NAME"

# 2) Interaktivní dotaz na existující soubor
if [ -e "$BACKUP_FILE" ]; then
  echo "Soubor '$BACKUP_FILE' již existuje."
  while true; do
    read -p "Chcete jej (p)řepsat a pokračovat, nebo (k)onec? [p/k]: " odpoved
    case "${odpoved,,}" in
      p|př* )
        rm -f "$BACKUP_FILE"
        echo "Zvolen přepis – pokračuji dále."
        break
        ;;
      k|konec|exit )
        echo "Ukončuji skript, vyberte prosím jiný výstupní soubor."
        exit 1
        ;;
      * )
        echo "Prosím odpovězte 'p' pro přepis nebo 'k' pro konec."
        ;;
    esac
  done
fi

# 3) Příprava výstupního souboru
mkdir -p "$BACKUP_DIR"
: > "$BACKUP_FILE"

# 4) Definice
ALLOWED_DIRS=( "." "agencies" "apartmany" "clients" "howto" "inc" "map" "myhome" "smlouvy" )
ALLOWED_EXT=( php html csv txt css js )

echo "=== Budu zpracovávat ==="
for d in "${ALLOWED_DIRS[@]}"; do
  if [ "$d" = "." ]; then
    echo "  • Kořen projektu: $PROJECT_DIR (maxdepth 1)"
  else
    echo "  • Podadresář: $PROJECT_DIR/$d"
  fi
done
echo "Povolené přípony: ${ALLOWED_EXT[*]}"
echo

# 5) Smyčka přes ALLOWED_DIRS
for d in "${ALLOWED_DIRS[@]}"; do
  if [ "$d" = "." ]; then
    CUR_DIR="$PROJECT_DIR"
    FIND_OPTS=( -maxdepth 1 -type f )
  else
    CUR_DIR="$PROJECT_DIR/$d"
    FIND_OPTS=( -type f )
  fi

  if [ ! -d "$CUR_DIR" ]; then
    echo "Adresář neexistuje, přeskočeno: $CUR_DIR"
    continue
  fi

  echo "Zpracovávám adresář: $CUR_DIR"

  # Najdi a filtruj přípony
  find "$CUR_DIR" "${FIND_OPTS[@]}" | sort | while read -r FILE; do
    ext="${FILE##*.}"
    for allowed in "${ALLOWED_EXT[@]}"; do
      if [[ "${ext,,}" == "$allowed" ]]; then
        echo "  soubor: $FILE"
        REL_PATH="${FILE#$PROJECT_DIR/}"
        printf '##### BEGIN FILE: %s #####\n' "$REL_PATH" >> "$BACKUP_FILE"
        cat "$FILE" >> "$BACKUP_FILE"
        printf '\n\n' >> "$BACKUP_FILE"
        break
      fi
    done
  done
done

echo
echo "=== Hotovo: záloha uložena do $BACKUP_FILE ==="

