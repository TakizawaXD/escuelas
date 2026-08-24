#!/usr/bin/env bash
set -euo pipefail

# Obtener la ruta de la base de datos desde el archivo .env
DB_PATH="database/escuela_erp.sqlite"
if [ -f .env ]; then
    ENV_PATH=$(grep -v '^#' .env | grep 'DB_PATH' | head -n 1 | cut -d '=' -f2 | tr -d '"' | tr -d "'" | tr -d ' ')
    if [ ! -z "$ENV_PATH" ]; then
        DB_PATH="$ENV_PATH"
    fi
fi

BACKUP_DIR="database/backups"
mkdir -p "$BACKUP_DIR"

TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
BACKUP_FILE="$BACKUP_DIR/backup_$TIMESTAMP.sqlite"

echo "Creando copia de seguridad de la base de datos..."
if [ -f "$DB_PATH" ]; then
    # Usar el comando backup de sqlite3 para evitar corrupción si hay conexiones activas
    sqlite3 "$DB_PATH" ".backup '$BACKUP_FILE'"
    echo "¡Copia de seguridad guardada en $BACKUP_FILE!"
else
    echo "Error: Base de datos no encontrada en $DB_PATH"
    exit 1
fi
