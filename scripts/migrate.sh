#!/usr/bin/env bash
set -euo pipefail

# Obtener la ruta de la base de datos desde el archivo .env
DB_PATH="database/escuela_erp.sqlite"
if [ -f .env ]; then
    # Leer DB_PATH ignorando comentarios y espacios
    ENV_PATH=$(grep -v '^#' .env | grep 'DB_PATH' | head -n 1 | cut -d '=' -f2 | tr -d '"' | tr -d "'" | tr -d ' ')
    if [ ! -z "$ENV_PATH" ]; then
        DB_PATH="$ENV_PATH"
    fi
fi

echo "Aplicando migraciones a: $DB_PATH"

# Asegurar que el directorio de la base de datos existe
mkdir -p "$(dirname "$DB_PATH")"

# Ejecutar cada migración en orden alfabético
for migration in database/migrations/*.sql; do
    if [ -f "$migration" ]; then
        echo "Ejecutando: $migration ..."
        sqlite3 "$DB_PATH" < "$migration"
    fi
done

echo "¡Migraciones aplicadas con éxito!"
