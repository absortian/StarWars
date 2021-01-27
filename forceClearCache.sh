#!/bin/bash
echo "Eliminando cache"
rm -r var/cache
echo "Creando carpeta"
mkdir var/cache
echo "Dando permisos"
chmod 777 -R var/cache
echo "Mundo salvado"
