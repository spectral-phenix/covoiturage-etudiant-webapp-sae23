# Guide utilisateur

*Application de covoiturage — étudiants du BUT R&T de Montbéliard et fan-club du FCSM*

L'application aide à organiser des trajets partagés entre étudiants qui ont les mêmes horaires de cours ou de loisirs (matchs du FCSM, courses). Elle fonctionne dans le navigateur, sans installation.

*English version: [user guide](user-guide.md).*

---

## 1. Connexion

Saisissez votre adresse e-mail universitaire et votre mot de passe, puis cliquez sur **Se connecter**.
Compte de démonstration : `hugo.petit@ufc` / `butrt`.

Si les identifiants sont faux, un message s'affiche en bas de l'écran. Le bouton **Déconnexion** se trouve en bas de la barre de gauche.

![Connexion](captures/01-connexion.png)

---

## 2. Accueil

![Accueil](captures/02-accueil.png)

**Étudiants compatibles** — les étudiants qui ont un créneau le même jour que vous, avec une heure de début à 15 minutes près. Pour chacun : nom, groupe, créneau partagé et distance entre vos domiciles (badge vert « Très proche » jusqu'à 2 km, bleu « Proche » jusqu'à 5 km). Les 4 plus proches sont affichés ; s'il y en a d'autres, un compteur l'indique.
Si la liste est vide, vos horaires ne sont pas encore enregistrés : contactez un administrateur.

**Mes trajets** — les trajets auxquels vous participez, comme conducteur ou passager, avec l'itinéraire, la date, l'heure, les places restantes et la participation demandée.
- Conducteur : **Membres** affiche votre équipage, **Retirer** enlève un passager, **Supprimer** annule le trajet.
- Passager : **Quitter** vous désinscrit.

---

## 3. Créer une annonce

![Créer un trajet](captures/03-creer-trajet.png)

1. **Lieu de départ** et **lieu d'arrivée** : IUT, stade Bonal, commerces, ou votre propre domicile (celui des autres n'est jamais proposé).
2. **Date** du trajet.
3. **Type** : **Aller**, **Retour** ou **Aller-retour**. En aller-retour, une section apparaît pour les horaires du retour.
4. **Heure de départ** et, si vous la connaissez, **heure d'arrivée estimée**.
5. **Véhicule** : l'un de vos véhicules enregistrés (voir la section 5 si la liste est vide).
6. **Participation demandée** (facultatif) : « 1 € », « gratuit », « libre »…
7. **Remarques** (facultatif).

Cliquez sur **Publier le trajet** : vous en devenez automatiquement le conducteur et il apparaît chez les autres étudiants.

---

## 4. Rejoindre un trajet

![Rejoindre un trajet](captures/04-rejoindre.png)

La page liste les trajets des autres étudiants qui ne sont pas complets : itinéraire, date et heure, conducteur, véhicule, places restantes et participation.

Cliquez sur **Rejoindre** : le trajet passe dans « Mes trajets » sur l'accueil. Quand toutes les places du véhicule sont prises, le trajet devient complet et disparaît de cette liste ; il réapparaît si quelqu'un se désiste.

---

## 5. Véhicules

![Véhicules](captures/05-vehicules.png)

- **Ajouter** : marque, modèle, immatriculation (ex. `AB-123-CD`), nombre de places conducteur compris, dates de validité du contrôle technique et de l'assurance, puis **Enregistrer le véhicule**.
- **Modifier** : le formulaire se remplit avec les informations actuelles ; corrigez puis enregistrez.
- **Supprimer** : une confirmation est demandée. Pensez à supprimer d'abord les trajets qui utilisent ce véhicule.

---

## 6. Mon profil

![Profil](captures/06-profil.png)

- **Informations générales** : nom, prénom, e-mail, groupe.
- **Domicile principal** : adresse, ville, code postal. Il sert à calculer la distance avec les autres étudiants.
- **Changer de mot de passe** : saisissez-le deux fois.

---

## 7. Administration

Le compte administrateur ouvre un panneau séparé : tableau de bord (nombre d'étudiants, véhicules, trajets et lieux) et une page par table : ajout, modification et suppression pour les étudiants, véhicules, lieux, horaires et domiciles ; consultation et suppression pour les trajets et équipages. C'est là que se gèrent les emplois du temps et les coordonnées GPS des domiciles.

![Administration](captures/07-admin-tableau-de-bord.png)

---

## Bon à savoir

- L'application n'envoie pas de notification : consultez régulièrement « Rejoindre un trajet ».
- Un trajet publié est visible par tous les étudiants : ne publiez que des trajets que vous ferez vraiment.
- En cas de page blanche ou d'erreur de connexion à la base, contactez l'administrateur.
