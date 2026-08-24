#!/usr/bin/env bash
set -euo pipefail

echo "Iniciando instalación automática del ERP Escolar..."

# 1. Copiar env si no existe
if [ ! -f .env ]; then
    echo "Creando archivo .env local..."
    cp .env.example .env
fi

# 2. Instalar dependencias php
echo "Instalando dependencias de Composer..."
if [ -f composer.phar ]; then
    php composer.phar install
else
    composer install
fi

# 3. Instalar dependencias npm
echo "Instalando dependencias de NPM..."
npm install

# 4. Correr migraciones
echo "Ejecutando migraciones de base de datos..."
bash scripts/migrate.sh

# 5. Correr seeders
echo "Poblando base de datos..."
bash scripts/seed.sh

echo "¡Instalación completada con éxito!"
