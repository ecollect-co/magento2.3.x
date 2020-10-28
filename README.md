# Plugin ecollect API Gateway para Magento 2 (2.2-2.4)

Este plugin permite integrar la plataforma ecollect como opción de pago en ecommerce Magento 2 mediante REST API

### Prerrequisitos

Necesita tener instalado Magento 2 con todas sus dependencias y tener los datos de integración a ecollect.

### Instalación del plugin

1- Copiar el plugin en el servidor de Magento 2

Primera opción: Descargue le Plug-in y descomprima en la carpeta /app/code/ de su instalación de Magento 2 

Segunda opción: Clonar el repositorio en su máquina. Requiere instalación de Git (https://git-scm.com/download)
```
git clone https://github.com/ecollect-co/magento2.3.x
```

2- Ingresar a la carpeta creada y copiar el contenido en su instalacion en Magento 2 a partir de la ruta /app/code/
```
cd magento2.3.x
cp . -R /path Magento/app/code/
```
3- Dirigirse a la ruta de instalación de Magento 2 y ejecutar el siguiente comando
```
php bin/magento module:enable ecollect_Core
```
```
Cambiar permisos de todas las carpetas: 
find ./ -type d -exec chmod 755 {} \;
Cambiar permisos de todos los archivos: 
find ./ -type f -print0 | xargs -0 chmod 0644
```

4- El siguiente comando ejecuta las tareas programadas agendadas, entre esas tareas está la que busca nuevas transacciones en ecollect y actualiza las órdenes de la tienda

```
php bin/magento cron:run
```

5- Ejecute los siguientes comandos
```
php bin/magento setup:upgrade
php bin/magento setup:di:compile
```

6- Los siguientes comandos limpian cache
```
cd pub/static 
rm -rf _requirejs 
rm -rf adminhtml 
rm -rf frontend 
cd ../../var 
rm -rf cache 
rm -rf page_cache 
rm -rf view_preprocessed 
cd .. 

rm -rf var/cache/* pub/static/* generated/*
php bin/magento cache:flush
php -dmemory_limit=5G  bin/magento setup:static-content:deploy -f

```

## Configuración de la tienda

Siga los pasos del capítulo "4. Configuración" indicado en el siguiente manual:
https://github.com/ecollect-co/magento2.3.x/blob/main/Manual/Manual-instalacion-ecollect-Magento2.pdf
