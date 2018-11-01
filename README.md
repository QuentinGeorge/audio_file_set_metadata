# Audio File Set Metadata
Ce script va, pour tous les fichiers .mp3 d'un répertoire&nbsp;:
1. Les copier dans un nouveau dossier à l'intérieur du premier
2. Supprimer les patterns de mots indésirables du nom de fichier (ex&nbsp;: (official video))
3. Renommer les nouveaux fichiers selon le pattern&nbsp;: Auteur - Titre
4. Supprimer les métadonnées du fichier
5. Ajouter les métadonnées pour l'auteur et le titre d'après le nouveau nom du fichier

## Installation
1. Installer PHP
.. Pour Windows&nbsp;:[Télécharger PHP](http://php.net/downloads.php) et Extraire le fichier zip à l'emplacement "C:\php"
2. Télécharger ce script en cliquant sur "Clone or download" => "Download zip" puis Extraire le fichier zip à l'emplacement voulut
3. Ouvrir le fichier "audio_file_set_metadata.bat" avec un éditeur de texte et à la ligne 2 "set scriptPath=D:\Projects\audio_file_set_metadata\" modifier la partie après le "=" par le chemin de l'emplacement du répertoire "audio_file_set_metadata"

## Utilisation
Copier le fichier "audio_file_set_metadata.bat" dans un dossier contenant les fichiers .mp3 à traité et double-cliquez dessus. Les fichiers traités seront envoyés dans le dossier "/done"
