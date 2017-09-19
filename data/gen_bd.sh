#!/bin/sh

##########################################################
# Este programa automatiza la creación de la base de datos
##########################################################

# servidor
if [ -z $1 ]

  then

    echo "Ingrese el nombre o la IP del Servidor de BD:"

    read hostname

  else

    hostname=$1

fi

# nombre de la bd a crear
if [ -z $2 ]

  then

    echo "Ingrese el nombre de la BD a crear:"

    read dbname

  else

    dbname=$1

fi

# nombre del usuario
if [ -z $3 ]

  then

    echo "Ingrese el usuario de la base de datos:"

    read usrname

  else

    usrname=$1

fi

# verificar si existe la bd

if [ -n "$(psql -h $hostname -U $usrname template1 -c '\l' | grep $dbname)" ]

  then 

    # drop de la db existente
    dropdb -h $hostname -U $usrname $dbname
    echo "Base de datos eliminada..."

  else

    echo 'La bd no existe...'

fi

# crear la nueva BD
createdb -h $hostname -U $usrname $dbname
echo "Base de datos recreada."


# crear el definition.sql
echo "Generación de definición a partir de los diagramas de clases..."
./gen_modelo.sh
echo "\nArchivo definition.sql creado satisfactoriamente."
