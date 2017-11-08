#!/bin/sh

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

    echo "Ingrese el nombre de la BD:"

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
echo "Creación de la base de datos $dbname..."
createdb -h $hostname -U $usrname $dbname

# correr el definition
echo "Importando definition.sql..."
psql -h $hostname -U $usrname -f ./definition.sql $dbname 2>&1 | grep 'ERROR' > ./$dbname.errors

if [ -s ./$dbname.errors ]

  then

    echo '[ERROR] Hubo errores en la generación de la base de datos!'
    echo "[ERROR] Revise $dbname.errors para mayor detalle"

    exit 1 

  else

    rm ./$dbname.errors

    echo '[Ok] Esquema de base de datos creado satisfactoriamente...'

fi


# preguntar si desea cargar los índices
echo 'Desea cargar los índices?(s/n)'
read yn

if [ $yn = 's' ]

  then

    # cargar índices
    psql -h $hostname -U $usrname -f ./indices.sql $dbname 2>&1 | grep 'ERROR' > ./$dbname.errors

    if [ -s ./$dbname.errors ]

  then

  echo '[ERROR] Hubo errores en la carga de los índices!'
  echo "[ERROR] Revise $dbname.errors para mayor detalle"
 
  exit 1 

    else

  rm ./$dbname.errors

  echo '[Ok] Cargado de índices...'
  
    fi

else
    
    echo 'ATENCION: Recuerde cargar los índices MANUALMENTE'

fi


# preguntar si desea cargar las listas de valores estándar del sistema
echo 'Desea cargar las listas de valores estándar?(s/n)'
read yn

if [ $yn = 's' ]

  then

    # cargar listas básicas del sistema
    psql -h $hostname -U $usrname -f ./listas.sql $dbname 2>&1 | grep 'ERROR' > ./$dbname.errors

    if [ -s ./$dbname.errors ]

	then

	echo '[ERROR] Hubo errores en la carga de las listas!'
	echo "[ERROR] Revise $dbname.errors para mayor detalle"
 
	exit 1 

    else

	rm ./$dbname.errors

	echo '[Ok] Cargado de listas básicas del sistema...'
	
    fi

else
    
    echo 'ATENCION: Recuerde cargar las listas de valores MANUALMENTE'

fi

# preguntar si desea cargar las funciones de BD
# echo 'Desea cargar las funciones de BD?(s/n)'
# read yn

# if [ $yn = 's' ]

#   then

#     echo "Concatenando funciones..."
#     cd funciones
#     ./gen_funciones.sh
#     cd ..

#     # cargar las funciones generadas
#     echo "Cargando funciones..."
#     psql -h $hostname -U $usrname -f ./funciones/funciones.sql $dbname 2>&1 | grep 'ERROR' > ./$dbname.errors

#     if [ -s ./$dbname.errors ]

#     then

#       echo '[ERROR] Hubo errores en la carga de las funciones!'
#       echo "[ERROR] Revise $dbname.errors para mayor detalle"
 
#       exit 1 

#     else

#       rm ./$dbname.errors

#     echo '[Ok] Cargado de funciones...'
  
#   fi

# else
    
#     echo 'ATENCION: Recuerde cargar las funciones MANUALMENTE'

# fi

# # preguntar si desea cargar los triggers
# echo 'Desea cargar los triggers de BD?(s/n)'
# read yn

# if [ $yn = 's' ]

#   then

#     echo "Concatenando triggers..."
#     cd triggers
#     ./gen_triggers.sh
#     cd ..

#     # cargar los triggers generados
#     echo "Cargando triggers..."
#     psql -h $hostname -U $usrname -f ./triggers/triggers.sql $dbname 2>&1 | grep 'ERROR' > ./$dbname.errors

#     if [ -s ./$dbname.errors ]

#     then

#       echo '[ERROR] Hubo errores en la carga de los triggers!'
#       echo "[ERROR] Revise $dbname.errors para mayor detalle"
 
#       exit 1 

#     else

#       rm ./$dbname.errors

#     echo '[Ok] Cargado de triggers...'
  
#   fi

# else
    
#     echo 'ATENCION: Recuerde cargar los triggers MANUALMENTE'

# fi

# preguntar si desea cargar la data prueba
# echo 'Desea cargar data de prueba?(s/n)'
# read yn

# if [ $yn = 's' ]

#   then

#     # cargar data prueba
#     psql -h $hostname -U $usrname -f ./data_prueba.sql $dbname 2>&1 | grep 'ERROR' > ./$dbname.errors

#     if [ -s ./$dbname.errors ]

#   then

#   echo '[ERROR] Hubo errores en la carga de la data prueba!'
#   echo "[ERROR] Revise $dbname.errors para mayor detalle"
 
#   exit 1 

#     else

#   rm ./$dbname.errors

#   echo '[Ok] Cargado de la data de prueba...'
  
#     fi

# fi
