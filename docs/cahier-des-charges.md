# Cahier des charges — SAÉ 23

*« Mettre en place une solution informatique pour l'entreprise » — BUT R&T 1ʳᵉ année, IUT Nord Franche-Comté*

## Problématique professionnelle

Le groupe de projet est sollicité par le **FCSM** (FC Sochaux-Montbéliard) pour développer un outil informatique répondant aux besoins des étudiants de la formation et du service de développement du fan-club. Il s'agit d'organiser la **mise en contact des étudiants** qui étudient dans la même ville, habitent dans la même ville et se rendent aux mêmes endroits pour leurs études, leurs loisirs ou leurs courses. La cible loisir se restreint à l'accès au FCSM. Les étudiants se déplacent peut-être aux mêmes moments : c'est ce que l'application doit déterminer, pour proposer des déplacements en commun, voire en mobilité partagée (**covoiturage**).

L'application :
- **cartographie** le domicile, les lieux de loisir (FCSM) et de courses, et le lieu d'étude commun (département R&T de Montbéliard) d'un groupe d'étudiants de BUT1 R&T ;
- **recense les modes de déplacement** (à pied, vélo, bus, voiture individuelle…) et, pour la voiture, les capacités de covoiturage (véhicule, nombre de places, participation demandée) ;
- **met en contact l'offre et la demande** pour chaque jour de la semaine à venir.

## Données à gérer pour chaque étudiant

- Domicile principal ou secondaire depuis lequel il se rend au département R&T.
- Véhicule éventuel : type, nombre de places, immatriculation, carte grise, contrôle technique, assurance.
- Lieux de courses et de loisirs : adresse, arrêt de bus, moyen de transport, durée moyenne.
- Informations universitaires : formation, groupe, sous-groupe, début de cours (−15 min) et fin de cours (+15 min) pour chaque jour du lundi au samedi.

Ces informations doivent pouvoir être saisies de **deux manières** :
1. sous forme d'un fichier **JSON ou CSV** ;
2. depuis une **base de données**, renseignée directement ou depuis une interface web ou mobile.

## Mise en relation

Le système informe des similitudes (même domicile, mêmes cours, même loisir, même lieu de courses) puis **propose des équipages**. À l'initialisation, il y a autant d'équipages que de véhicules. Chaque participant valide ou invalide un trajet aller et un trajet retour par jour. Un étudiant peut quitter un équipage et en rejoindre un autre, rejoindre un équipage incomplet s'il n'en avait pas, ajouter ou retirer un véhicule.

Le cahier des charges est volontairement **imparfait et évolutif** : la solution est expérimentale.

## Étapes

1. **Environnement de développement** : machine virtuelle ou ressources distantes (ESX, Proxmox, Guacamole, Docker…), serveur web (Nginx ou Apache), framework éventuel.
2. **Réalisation documentée** : algorithmique (scripts serveur, dépôt de code), technologies web (HTML, CSS), base de données avec ajout, suppression et modification.
3. **Formation** des utilisateurs en français et en anglais, avec une documentation en anglais.

## Modalités d'évaluation

- Site fonctionnel et dynamique
- Données manipulées dans un fichier CSV ou JSON, et depuis une base de données (ajout, suppression, modification)
- Documentation technique
- Tutoriel d'installation et d'utilisation
- Dépôt du code
- Démonstration et présentation des outils de développement
- Méthode de validation (cahier de tests, tests unitaires)

## Correspondance avec ce dépôt

| Exigence | Réalisation |
|---|---|
| Site fonctionnel et dynamique | [`app/`](../app) — SPA JavaScript + API PHP |
| Données JSON **et** base de données | [`database/data.json`](../database/data.json) + [`import_json.py`](../database/import_json.py), [`fcsm.sql`](../database/fcsm.sql), CRUD dans l'interface |
| Mise en relation | [`app/api/matches.php`](../app/api/matches.php) (même jour, ±15 min, tri par distance) |
| Équipages, rejoindre / quitter | [`app/api/trajets.php`](../app/api/trajets.php) |
| Ajouter / retirer un véhicule | [`app/api/vehicules.php`](../app/api/vehicules.php) |
| Documentation technique | [guide du développeur](guide-developpeur.md) |
| Tutoriel d'installation et d'utilisation | [README](../README.md#installation), [guide utilisateur](guide-utilisateur.md) |
| Documentation en anglais | [user guide](user-guide.md) |
| Méthode de validation | [cahier de tests](cahier-de-tests.md) : [`test_api.py`](../tests/test_api.py) et [`test_interface.py`](../tests/test_interface.py) |

**Non couvert dans cette version** : modes de déplacement autres que la voiture, validation aller/retour depuis l'interface (les colonnes existent en base), import CSV.
