# biosphere

## Instructions de démarrage
### windows
#### Installer wampserver 3.3.8
Télécharger wampserver [wampserver.aviatechno.net](https://wampserver.aviatechno.net/)

>[!NOTE]
>Si vous avez déjà wampserver et qu'elle est antérieure\
>**installer la mise à jour wampserver 3.3.8**

#### Installer php 8.4
1. Ajouter php8.4 à wampserver [wampserver.aviatechno.net](https://wampserver.aviatechno.net/)

2. Rafraîchir : click droit sur l'îcone wampserver puis clicker sur *rafaîchir*

3. Enfin ajouter l'emplacement du fichier php.exe au variable d'environnement

####  

<br />

### Créer un fichier ***.env*** à la racine du projet
>[!IMPORTANT]
>Il faudra définir les variables suivantes dans le fichier créé

```
DB_NAME = nom de la base de données
DB_USER = nom de l'utilisateur
DB_HOST = nom de l'hôte
DB_PSWD = mot de passe
UPLOAD_DIR= sous_dossier_videos/
COVER_DIR= dossier_images/
UPLOAD_BASE_DIR = C:/.../dossier_uploads/
TEMP_UPLOAD_DIR= sous_dossier_chunks/
```
### Créer un fichier ***env*** à la racine du dossier wbesocket
```
MQTT_TOPIC=
MQTT_PORT=
MQTT_SERVER=
CLIENT=
SOCKET_BASE=
``` 

<br />

### Installer les dépendances
PHP
```powershell
composer i
```
WEBOCKET
```powershell
cd websocket
npm run i
```
<br />

### Exécuter le code
Démarrer le server sur le port 8000 en entrant la commande suivante dans le terminal d'un éditeur de code tel que *VScode*

```powershell
php -S localhost:8000  -t public/
```

