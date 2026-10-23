# User guide

*Carpooling app — students of the BUT R&T programme in Montbéliard and FC Sochaux-Montbéliard supporters' club*

The app helps students who share the same class or leisure schedule (FCSM home matches, grocery shopping) organise shared trips. It runs in a web browser; nothing needs to be installed.

*Version française : [guide utilisateur](guide-utilisateur.md). The interface itself is in French; the French labels are given in bold.*

---

## 1. Signing in

Enter your university e-mail address and password, then click **Se connecter** (Sign in).
Demo account: `hugo.petit@ufc` / `butrt`.

Wrong credentials display an error at the bottom of the screen. **Déconnexion** (Sign out) is at the bottom of the left sidebar.

![Sign-in](captures/01-connexion.png)

---

## 2. Home page (Accueil)

![Home page](captures/02-accueil.png)

**Compatible students (Étudiants compatibles)** — students who have a time slot on the same day as you, starting within 15 minutes of yours. Each card shows their name, group, the shared slot and the distance between your homes (green "Très proche" = very close, up to 2 km; blue "Proche" = close, up to 5 km). The four closest are shown; a counter tells you if there are more.
An empty list means your timetable has not been entered yet: contact an administrator.

**My trips (Mes trajets)** — trips you take part in as driver or passenger: route, date, time, seats left and requested contribution.
- As a driver: **Membres** shows your crew, **Retirer** removes a passenger, **Supprimer** cancels the trip.
- As a passenger: **Quitter** leaves the trip.

---

## 3. Offering a ride (Créer une annonce)

![Offering a ride](captures/03-creer-trajet.png)

1. **Lieu de départ / Lieu d'arrivée** (departure / destination): the IUT, the Bonal stadium, shops, or your own home (other students' homes are never listed).
2. **Date du trajet**: date of the trip.
3. **Type**: one-way (**Aller**), return only (**Retour**) or round trip (**Aller-retour**). For a round trip, extra fields appear for the return times.
4. **Heure de départ**: departure time; **heure d'arrivée estimée**: estimated arrival time (optional).
5. **Véhicule**: one of your registered vehicles (see section 5 if the list is empty).
6. **Participation demandée** (optional): requested contribution, e.g. "1 €", "gratuit" (free), "libre" (pay what you want).
7. **Remarques** (optional): any extra information.

Click **Publier le trajet** (Publish): you automatically become the driver and the trip is shown to the other students.

---

## 4. Joining a ride (Rejoindre un trajet)

![Joining a ride](captures/04-rejoindre.png)

This page lists other students' trips that still have free seats: route, date and time, driver, vehicle, seats left and contribution.

Click **Rejoindre** (Join): the trip now appears under "Mes trajets" on the home page. When every seat is taken the trip becomes full and disappears from this list; it comes back if someone leaves.

---

## 5. Vehicles (Véhicule)

![Vehicles](captures/05-vehicules.png)

- **Add**: make (**Marque**), model (**Modèle**), licence plate (**Immatriculation**, e.g. `AB-123-CD`), number of seats including the driver (**Nombre de places**), roadworthiness test expiry (**Contrôle technique**) and insurance expiry (**Assurance**), then **Enregistrer le véhicule** (Save).
- **Modifier** (Edit): the form is pre-filled; make your changes and save.
- **Supprimer** (Delete): you are asked to confirm. Delete the trips that use this vehicle first.

---

## 6. My profile (Mon profil)

![Profile](captures/06-profil.png)

- **Informations générales**: surname, first name, e-mail, group.
- **Domicile principal** (main home): address, city, postcode. Used to compute distances to other students.
- **Changer le mot de passe**: type the new password twice.

---

## 7. Administration

The administrator account opens a separate panel: a dashboard (number of students, vehicles, trips and places) and one page per database table: students, vehicles, places, timetables and homes can be added, edited and deleted; trips and crews can be reviewed and deleted. Timetables and the GPS coordinates of homes are managed there.

![Administration](captures/07-admin-tableau-de-bord.png)

---

## Good to know

- The app does not send notifications: check "Rejoindre un trajet" regularly.
- A published trip is visible to every student: only publish trips you will actually make.
- If you get a blank page or a database connection error, contact the administrator.
