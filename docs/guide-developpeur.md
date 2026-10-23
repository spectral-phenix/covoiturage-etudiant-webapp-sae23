# Guide du développeur

À destination de quiconque reprend le projet. Pour l'installation, voir le [README](../README.md#installation).

## 1. Architecture

| Couche | Technologie |
|---|---|
| Front-end | HTML, CSS, JavaScript sans framework, en une seule page (SPA) |
| Back-end | PHP 8, API REST qui ne renvoie que du JSON |
| Base de données | MySQL 8 / MariaDB, base `FCSM`, encodage `utf8mb4` |
| Serveur | Apache + PHP (WAMP, XAMPP, LAMP ou Docker) |

`index.html` est chargé une seule fois ; le JavaScript affiche ou masque les sections et récupère les données avec `fetch()`. Les fichiers PHP ne produisent jamais de HTML.

## 2. Fichiers

```
app/
├── index.html            application étudiant
├── admin_panel.html      administration (script intégré, indépendant de app.js)
├── css/style.css         thème (variables CSS) et composants
├── js/app.js             navigation, appels API, rendu, formulaires
└── api/
    ├── db.php            session, en-têtes JSON/CORS, connexion PDO, fonctions communes
    ├── config.example.php modèle de configuration (copier en config.php)
    ├── auth.php          connexion, déconnexion, utilisateur courant
    ├── matches.php       mise en relation par horaires
    ├── trajets.php       trajets et équipages
    ├── vehicules.php     véhicules de l'utilisateur
    ├── profil.php        profil, domicile, horaires, lieux
    └── admin.php         CRUD générique réservé à l'admin
database/
├── fcsm.sql              structure + données de démonstration
├── data.json             données au format JSON
└── import_json.py        import JSON → MySQL
tests/test_api.py         tests fonctionnels de l'API
tests/test_interface.py   tests de l'interface (Playwright)
```

## 3. Configuration

`db.php` lit chaque paramètre d'abord dans les **variables d'environnement** (utilisées par Docker), sinon dans **`api/config.php`** (non versionné), sinon prend une valeur par défaut.

| Paramètre | Défaut | Rôle |
|---|---|---|
| `DB_HOST` | `localhost` | serveur MySQL |
| `DB_NAME` | `FCSM` | nom de la base |
| `DB_USER` | `root` | utilisateur MySQL |
| `DB_PASS` | *(vide)* | mot de passe MySQL |
| `ADMIN_LOGIN` | `admin` | identifiant du compte administrateur |
| `ADMIN_PASSWORD` | `root` | mot de passe du compte administrateur |

Sous Linux, les noms de base sont sensibles à la casse : la base doit s'appeler exactement `FCSM` (ou adapter `DB_NAME`).

## 4. Base de données

| Table | Colonnes principales |
|---|---|
| `etudiant` | id, nom, prenom, email (unique), password, groupe |
| `lieu` | id, nom, adresse, ville, type (`universite`, `loisir`, `courses`, `domicile`), code_postal, latitude, longitude |
| `domicile` | id, type (`principale`, `secondaire`), id_etudiant, id_lieu |
| `vehicule` | id, immatriculation (unique), nb_places, id_etudiant, marque, modele, date_ct, date_assurance |
| `horaire` | id, jour_semaine (lundi → samedi), heure_debut, heure_fin, id_etudiant, id_lieu, type (`travail`, `loisir`, `courses`) |
| `trajet` | id, date_trajet, heure_depart, heure_arrivee_estimee, heure_retour_depart, heure_retour_arrivee, type (`aller`, `retour`, `aller-retour`), estPlein, id_createur, id_lieu_depart, id_lieu_arrivee, id_vehicule, participation, remarques |
| `membre_equipage` | id, id_trajet, id_etudiant, role (`conducteur`, `passager`), valide_aller, valide_retour — unique sur (id_trajet, id_etudiant) |

Le schéma relationnel est dessiné dans le [README](../README.md#base-de-données). Les tables sont en **MyISAM**, sans clé étrangère : la cohérence est assurée par le code PHP (par exemple, supprimer un trajet supprime aussi son équipage).

Si une ancienne base n'a pas les colonnes du retour :

```sql
ALTER TABLE trajet
  ADD COLUMN heure_retour_depart  TIME NULL AFTER heure_arrivee_estimee,
  ADD COLUMN heure_retour_arrivee TIME NULL AFTER heure_retour_depart;
```

## 5. API

### `db.php`
Inclus par tous les autres fichiers. Démarre la session, envoie les en-têtes JSON et CORS, et fournit :
- `getDB()` : connexion PDO (exceptions activées, résultats en tableaux associatifs) ;
- `jsonResponse($data, $code)` : encode en JSON et termine ;
- `getBody()` : décode le corps JSON de la requête ;
- `requireLogin()` : renvoie l'id de l'étudiant connecté, ou répond `401`.

### `auth.php`
| Requête | Effet |
|---|---|
| `GET ?action=me` | utilisateur connecté, `is_admin: true` pour l'admin, sinon `connected: false` |
| `POST {action: "login", email, password}` | ouvre la session étudiant, ou la session admin si les identifiants correspondent à `ADMIN_LOGIN` / `ADMIN_PASSWORD` |
| `POST {action: "logout"}` | détruit la session |

### `matches.php`
`GET` uniquement. Pour chaque créneau de l'étudiant, cherche les autres étudiants ayant un créneau le même jour avec une heure de début à 15 minutes près (`ABS(TIME_TO_SEC(...) - TIME_TO_SEC(?)) <= 900`), calcule la distance Haversine entre les domiciles principaux, supprime les doublons et trie par distance. Le front affiche les 4 premiers.

### `trajets.php`
| Requête | Effet |
|---|---|
| `GET ?type=mes` | trajets de l'utilisateur (conducteur ou passager) |
| `GET ?type=disponibles` | trajets non complets auxquels il ne participe pas |
| `GET ?type=membres&id_trajet=X` | équipage (créateur uniquement, sinon `403`) |
| `POST {action: "create", ...}` | crée le trajet et inscrit le créateur comme conducteur |
| `POST {action: "rejoindre", id_trajet}` | inscrit comme passager, refuse si complet (`400`), met `estPlein` à jour |
| `POST {action: "quitter", id_trajet}` | se retire du trajet, remet `estPlein` à 0 |
| `POST {action: "kick", id_trajet, id_etudiant}` | le créateur retire un passager (`403` sinon, impossible sur soi-même) |
| `DELETE ?id=X` | le créateur supprime le trajet et son équipage |

### `vehicules.php`
`GET` (liste), `POST` (ajout), `PUT` (modification), `DELETE ?id=X`. Chaque requête est filtrée sur `id_etudiant` : on ne peut agir que sur ses propres véhicules.

### `profil.php`
| Requête | Effet |
|---|---|
| `GET ?action=profil` | informations + domicile principal |
| `GET ?action=lieux` | tous les lieux |
| `GET ?action=lieux_trajet` | lieux utilisables dans un trajet : tout sauf les domiciles des autres étudiants |
| `GET ?action=horaires` | emploi du temps de l'étudiant (pas encore affiché dans l'interface) |
| `POST {action: "update_profil"}` | nom, prénom, e-mail, groupe |
| `POST {action: "update_mdp"}` | mot de passe |
| `POST {action: "update_domicile"}` | crée ou met à jour le domicile principal |

### `admin.php`
CRUD générique, protégé par `$_SESSION['is_admin']` (sinon `403`). Tables autorisées (liste blanche) : `etudiant`, `vehicule`, `lieu`, `trajet`, `horaire`, `membre_equipage`, `domicile`.

| Requête | Effet |
|---|---|
| `GET ?table=X` | `SELECT * ... ORDER BY id` |
| `POST {table, colonnes...}` | `INSERT` |
| `PUT {table, id, colonnes...}` | `UPDATE ... WHERE id` |
| `DELETE ?table=X&id=Y` | `DELETE ... WHERE id` |

Aucune validation métier dans cet endpoint : à utiliser avec précaution.

## 6. Front-end (`app.js`)

- `const API = 'api'` : préfixe de tous les appels ; à changer si l'arborescence change.
- Utilitaires : `fetchJSON()`, `toast()` (notification), `joliHeure()` (`08:00:00` → `8h00`), `joliJour()`, `initiales()`, `placesRestantes()`.
- **Authentification** : `checkAuth()` au chargement → écran de connexion, application, ou redirection vers `admin_panel.html`.
- **Navigation** : chaque `.nav-item` porte `data-page="xxx"` ; au clic, un écouteur active `#page-xxx` et appelle `loadAccueil()`, `loadCreerForm()`, `loadRejoindre()`, `loadVehicules()` ou `loadProfil()`.
- **Formulaires** : soumission interceptée (`preventDefault()`), corps envoyé en JSON.
- **Aller-retour** : le select `#f-type` affiche le bloc `#retour-fields` quand la valeur est `aller-retour`.
- **Équipage** : `toggleMembres(id)` affiche les membres sous la carte du trajet, `kickMembre()` retire un passager.

`admin_panel.html` a son propre script : `FIELD_DEFS` (champs modifiables par table — défini pour `etudiant`, `vehicule`, `lieu`, `horaire` et `domicile` ; les trajets et équipages sont en consultation et suppression), `PANEL_TO_TABLE` (nom du panneau → nom SQL), `PANEL_COLS` (colonnes affichées). La vraie protection est côté serveur dans `admin.php`.

## 7. Thème (`style.css`)

Variables dans `:root` : `--bg`, `--surface`, `--surface2`, `--border`, `--text`, `--text-2`, `--text-3`, `--accent`, `--green`, `--blue`, `--amber`, `--red`.
Composants : `.card`, `.card-row`, `.badge` (`badge-green`, `badge-blue`…), `.btn`, `.btn-primary`, `.btn-danger`, `.btn-full`, `.form-group`, `.form-row`, `.form-row-3`, `.mono`, `.empty-state`, `.loading`, `.avatar`, `.places-bar`.

## 8. Ajouter une fonctionnalité : exemple

Notation des conducteurs :
1. **Base** : `ALTER TABLE membre_equipage ADD COLUMN note TINYINT NULL;`
2. **API** : créer `api/notes.php` (`GET` pour lire, `POST` pour noter), en commençant par `require_once 'db.php'` et `requireLogin()`.
3. **HTML** : ajouter `<div class="nav-item" data-page="notes">` et `<div class="page" id="page-notes">` dans `index.html`.
4. **JS** : ajouter le cas `notes` dans l'écouteur de navigation et écrire `loadNotes()`.
5. **Tests** : ajouter les scénarios dans `tests/test_api.py` et, pour l'interface, `tests/test_interface.py`.

## 9. Pistes d'évolution

- **Sécurité** : `password_hash()` / `password_verify()`, validation des entrées côté serveur, CORS limité à l'origine réelle, HTTPS, administrateurs en base.
- **Intégrité** : passer en InnoDB avec clés étrangères et `ON DELETE CASCADE`.
- **Horaires** : affichage et saisie dans la page Profil (l'endpoint existe).
- **Géolocalisation** : géocoder automatiquement l'adresse saisie (API Nominatim d'OpenStreetMap) pour remplir latitude et longitude.
- **Équipages** : boutons de validation aller / retour (colonnes déjà présentes).
- **Journal** : table de logs des actions de l'administrateur.
