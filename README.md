REMOTE WAKE/SLEEP-ON-LAN SERVER *(RWSOLS)*
==========================================


# Pre-requisitos: cambiar los Puertos en Apache

Este documento proporciona una guía rápida sobre cómo cambiar los puertos predeterminados del servidor Apache de wake-on-lan para evitar conflictos con otros servicios que pueden estar utilizando los puertos 80 y 443, p. ej., la radio.


## Pasos para Cambiar los Puertos

### 1. Editar el archivo `ports.conf`

Encuentra y edita el archivo `ports.conf`. Este archivo se ubica comúnmente en `/etc/apache2/ports.conf` en sistemas basados en Debian/Ubuntu.

```bash
sudo nano /etc/apache2/ports.conf
```

Cambia las líneas que escuchan en los puertos 80 y 443 a los nuevos puertos que deseas usar. Por ejemplo:

```apache
Listen 8080

<IfModule ssl_module>
    Listen 8443
</IfModule>

<IfModule mod_gnutls.c>
    Listen 8443
</IfModule>
```

### 2. Actualizar la Configuración de los Hosts Virtuales

Edita los archivos de configuración de tus hosts virtuales en `/etc/apache2/sites-available/`. Cambia `<VirtualHost *:80>` a `<VirtualHost *:8080>` y `<VirtualHost *:443>` a `<VirtualHost *:8443>`.

Por ejemplo:

```apache
<VirtualHost *:8080>
    # Configuración para HTTP
</VirtualHost>

```

### 3. Reiniciar Apache

Reinicia el servidor Apache para aplicar los cambios:

```bash
sudo systemctl restart apache2
```

### 4. Correr el servicio

```bash
python setup.py 
```

En los pasos, indicar que el servicio de dynDNS apunte a la ip pública del servidor y no configurar el certificado de certbot porque ya lo tenemos en el proxy inverso de nginx.


# Remote Wake/Sleep On LAN Server Fork

Este repositorio es un fork del proyecto original Remote Wake/Sleep On LAN Server. Este fork incluye actualizaciones en el archivo de configuración `config.php` para mejorar la compatibilidad y funcionalidad, además de incluir configuración específica para el uso con un proxy inverso.

## Cambios en este Fork

Los cambios principales de este fork incluyen:
- **Actualización del archivo `config.php`**: El archivo de configuración.

## Instalación

Sigue la guía de instalación oficial disponible en la wiki de GitHub del proyecto original. Usa el script `setup.py` para configurar las direcciones IP y otros ajustes necesarios.

### Pasos para la Instalación

1. Clona este repositorio:
2. Navega al directorio del proyecto y ejecuta `setup.py` para realizar la configuración inicial:
   ```
   cd Remote-Wake-Sleep-On-LAN-Server
   python3 setup.py
   ```
3. Modifica `config.php` según tus necesidades específicas.
4. Si estás utilizando un proxy inverso, asegúrate de configurar el archivo de configuración específico para proxies inversos ubicado en tu servidor.
