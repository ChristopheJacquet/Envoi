SQLite
======

Créer à la main ("touch") le fichier de base de données.
Le serveur web doit pouvoir écrire dans ce fichier.
The web server needs the write permission to not only the database file, but also the containing directory of that file.
	--> sinon cela provoque "General error: 14 unable to open database file"