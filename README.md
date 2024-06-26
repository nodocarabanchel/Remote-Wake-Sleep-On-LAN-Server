REMOTE WAKE/SLEEP-ON-LAN SERVER *(RWSOLS)*
==========================================


### 1. Conigurar el servicio:

```bash
python setup.py 
```

En los pasos, indicar que el servicio de dynDNS apunte a la ip pública del servidor y no configurar el certificado de certbot porque ya lo tenemos en el proxy inverso de nginx.

### 2. Correr la versión Dockerizada

Si se ha corrido el configurador anterior, asegurarse de que apache no esta funcionando:

```bash
sudo systemctl stop apache2
```

```bash
sudo systemctl disable apache2
```

Si ya está configurado, se puede simplemente ejecutar:

```bash
docker compose up -d
```

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
