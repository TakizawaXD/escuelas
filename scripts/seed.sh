#!/usr/bin/env bash
set -euo pipefail

echo "Poblando base de datos con datos de prueba..."
php create_test_records.php
echo "¡Base de datos poblada con éxito!"
