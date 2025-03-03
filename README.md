# biosphere

## Instructions de démarrage
### Installer php pour windows
Télécharger la dernière version sur le site [php.net](https://windows.php.net/download)

>[!TIP]
>un tuto de [Grafikart](https://www.youtube.com/watch?v=OVTnj3hcHuc) explique comment installer **php** sur **windows**

>[!NOTE]
>Dans la vidéo il s'agit d'une version antérieure\
>Voir plus sur  la nouvelle version de [php](https://www.php.net/releases/8.4/en.php)

<br />

### Créer un fichier ***.env*** à la racine du projet
>[!IMPORTANT]
>Il faudra définir les variables suivantes dans le fichier créé

```
DB_NAME = nom de la base de données
DB_USER = nom de l'utilisateur
DB_HOST = nom de l'hôte
DB_PSWD = mot de passe
```
<br />

### Installer les dépendances
```powershell
composer require altorouter/altorouter
composer require vlucas/phpdotenv
composer require --dev filp/whoops
composer require --dev symfony/var-dumper
```
<br />

### Exécuter le code
Démarrer le server sur un port (*8000 par exemple*) en entrant la commande suivante dans le terminal d'un éditeur de code tel que *VScode*

```powershell
php -S localhost:8000  -t public/
```

