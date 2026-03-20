#!/usr/bin/env bash
# ============================
# restore.sh
# ============================
# Popis:
#   Načte zálohovací textový soubor a podle hlaviček
#   obnoví jednotlivé soubory i adresáře.
#
# Použití:
#   ./restore.sh [BACKUP_FILE]
#   BACKUP_FILE – zálohovací soubor (např. backup.txt)
#
# Příklad:
#   ./restore.sh projekt_zaloha.txt

set -euo pipefail
IFS=$'\n\t'

BACKUP_FILE="${1:-backup.txt}"

current_file=""
# Pro čtení řádek včetně mezer
while IFS= read -r line || [[ -n $line ]]; do
  if [[ $line =~ ^#####\ BEGIN\ FILE:\ (.*)\ #####$ ]]; then
    # Pokud již máme otevřený soubor, uzavřeme ho (fds se přepíše)
    current_file="${BASH_REMATCH[1]}"
    # Vytvoříme adresáře, pokud neexistují
    mkdir -p "$(dirname "$current_file")"
    # Připravíme nový soubor
    : > "$current_file"
  else
    # Zapíšeme řádek do aktuálního souboru
    if [[ -n $current_file ]]; then
      printf '%s\n' "$line" >> "$current_file"
    fi
  fi
done < "$BACKUP_FILE"

echo "Obnova dokončena."

