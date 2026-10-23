# Cahier de tests

Les tests fonctionnels de l'API sont automatisés dans [`tests/test_api.py`](../tests/test_api.py). Le script se comporte comme plusieurs navigateurs à la fois (trois étudiants, un visiteur non connecté et l'administrateur), chacun avec sa propre session, et vérifie à la fois les fonctionnalités et les **droits d'accès** : ce qu'un utilisateur ne doit pas pouvoir faire est testé autant que ce qu'il doit pouvoir faire.

## Lancer les tests

1. Importer une base propre : `database/fcsm.sql` (les tests créent puis suppriment leurs propres véhicules et trajets).
2. Démarrer l'application (Docker, Apache, ou `php -S localhost:8000 -t app`).
3. Lancer :
   ```bash
   python tests/test_api.py http://localhost:8000
   ```

Le script affiche un tableau de résultats et se termine avec le code 1 si un test échoue, ce qui permet de l'utiliser en intégration continue.

## Résultats des tests de l'API

Dernière exécution : 23 octobre 2026 — PHP 8.3, MariaDB 10.11 — **26/26 tests réussis**.

### Authentification

| N° | Scénario | Résultat attendu | Résultat |
|---|---|---|:---:|
| T01 | Connexion avec un mauvais mot de passe | refus 401 | ✅ |
| T02 | Connexion d'un étudiant | succès, pas admin | ✅ |
| T03 | Session conservée entre deux requêtes | connected = true | ✅ |
| T04 | Accès à l'API sans être connecté | refus 401 | ✅ |

### Mise en relation

| N° | Scénario | Résultat attendu | Résultat |
|---|---|---|:---:|
| T05 | Étudiants compatibles (même créneau ±15 min) | liste non vide | ✅ |
| T06 | Tri par distance entre domiciles | ordre croissant | ✅ |
| T07 | Un étudiant n'apparaît qu'une fois | pas de doublon | ✅ |
| T08 | Lieux proposés pour un trajet | seul son propre domicile | ✅ |

### Véhicules

| N° | Scénario | Résultat attendu | Résultat |
|---|---|---|:---:|
| T09 | Ajout d'un véhicule | succès | ✅ |
| T10 | Modification du véhicule | modèle = Clio | ✅ |
| T11 | Suppression du véhicule d'un autre étudiant | véhicule toujours présent | ✅ |

### Trajets et équipages

| N° | Scénario | Résultat attendu | Résultat |
|---|---|---|:---:|
| T12 | Création d'un trajet | succès | ✅ |
| T13 | Le créateur devient conducteur | rôle conducteur | ✅ |
| T14 | Le trajet est proposé aux autres | visible dans « disponibles » | ✅ |
| T15 | Rejoindre un trajet | succès | ✅ |
| T16 | Rejoindre un trajet complet (2 places) | refus 400 | ✅ |
| T17 | Retirer un passager sans être le conducteur | refus 403 | ✅ |
| T18 | Le conducteur voit son équipage | 2 membres | ✅ |
| T19 | Une place se libère quand un passager quitte | un autre peut rejoindre | ✅ |
| T20 | Le conducteur retire un passager | succès | ✅ |
| T21 | Supprimer le trajet d'un autre | refus 403 | ✅ |
| T22 | Le créateur supprime son trajet | succès, trajet disparu | ✅ |

### Administration

| N° | Scénario | Résultat attendu | Résultat |
|---|---|---|:---:|
| T23 | Un étudiant appelle l'API d'administration | refus 403 | ✅ |
| T24 | Connexion administrateur | is_admin = true | ✅ |
| T25 | L'admin liste les étudiants | 7 étudiants | ✅ |
| T26 | L'admin demande une table hors liste blanche | refus 400 | ✅ |

## Anomalies trouvées grâce aux tests

| Anomalie | Correction |
|---|---|
| Supprimer un trajet laissait ses membres dans `membre_equipage` (lignes orphelines, MyISAM sans `ON DELETE CASCADE`) | `trajets.php` supprime aussi l'équipage ; vérifié par T22 |
| Coordonnées inversées (latitude ↔ longitude) pour deux magasins dans `fcsm.sql` | valeurs remises dans le bon ordre |
| `import_json.py` cherchait un fichier `donnees.json` inexistant et masquait toutes les erreurs (`except: pass`) | lit `data.json`, transaction annulée et message clair en cas d'erreur |

## Tests de l'interface

[`tests/test_interface.py`](../tests/test_interface.py) pilote un vrai navigateur (Chromium, via Playwright) : il remplit les formulaires et clique comme un utilisateur, puis vérifie **directement en base** que l'action a été enregistrée.

```bash
pip install playwright && playwright install chromium
python tests/test_interface.py http://localhost:8000
```

Dernière exécution : 23 octobre 2026 — **15/15 tests réussis**.

Ces tests modifient le profil de démonstration (domicile, mot de passe, inscription à un trajet) : réimporter `database/fcsm.sql` après les avoir lancés.

| Écran | Scénario | Résultat |
|---|---|:---:|
| Connexion | connexion refusée avec un mauvais mot de passe, message affiché | ✅ |
| Connexion | le compte admin est redirigé vers le panneau d'administration | ✅ |
| Accueil | 4 étudiants compatibles + compteur des suivants | ✅ |
| Créer une annonce | champs du retour affichés seulement en aller-retour | ✅ |
| Créer une annonce | seul son propre domicile est proposé comme lieu | ✅ |
| Rejoindre | un trajet rejoint quitte la liste des trajets disponibles | ✅ |
| Véhicules | formulaire de modification pré-rempli | ✅ |
| Véhicules | modification enregistrée en base | ✅ |
| Véhicules | ajout enregistré en base | ✅ |
| Véhicules | suppression (avec confirmation) | ✅ |
| Profil | modification du domicile enregistrée | ✅ |
| Profil | mot de passe changé seulement si les deux saisies concordent | ✅ |
| Administration | ajout d'une ligne | ✅ |
| Administration | modification d'une ligne | ✅ |
| Administration | suppression d'une ligne | ✅ |
