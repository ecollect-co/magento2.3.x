# magento2.3.x
Magento plug-in for ecollect REST API Gateway

# Plugin Integración ecollect API Gateway para Magento 2 (2.2, 2.3, 2.4)

Este plugin permite integrar la plataforma ecollect como opción de pago en ecommerce Magento 2.

### Prerrequisitos

Necesita tener instalado Magento 2 con todas sus dependencias y tener los datos de integración a ecollect.


### Instalación del plug-in


1- Clonar el repositorio en su máquina.

```
git clone https://github.com/epayco/magento2.x.git
```
2- Ingresar a la carpeta creada y copiar el contenido en su instalacion en magento en la ruta ruta/de/su/instalacion/app/code/
```
cd magento2
cp . -R /path Magento/app/code/
```
3- Dirigirse a la ruta de instalación de Magento 2 y ejecutar el siguiente comando
```
php bin/magento module:enable ecollect_Core
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

php bin/magento cache:flush
```

## Configuración de la tienda

Siga los pasos del capítulo 4.Configuración indicados en el siguiente manual





