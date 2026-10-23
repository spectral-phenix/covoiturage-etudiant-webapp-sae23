"""Tests fonctionnels de l'API (cahier de tests automatisé).

Prérequis : l'application tourne (Apache ou `php -S`) avec la base de démonstration
database/fcsm.sql fraîchement importée.

Usage :
    python tests/test_api.py                       # http://localhost:8000
    python tests/test_api.py http://localhost/fcsm

Uniquement la bibliothèque standard de Python : rien à installer.
"""
import json
import sys
import urllib.error
import urllib.request
from http.cookiejar import CookieJar

BASE = (sys.argv[1] if len(sys.argv) > 1 else "http://localhost:8000").rstrip("/") + "/api"
resultats = []


class Client:
    """Un navigateur minimal : garde son cookie de session PHP."""

    def __init__(self):
        self.opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(CookieJar()))

    def req(self, methode, url, corps=None):
        data = json.dumps(corps).encode() if corps is not None else None
        r = urllib.request.Request(BASE + url, data=data, method=methode,
                                   headers={"Content-Type": "application/json"})
        try:
            with self.opener.open(r) as rep:
                return rep.status, json.loads(rep.read() or "null")
        except urllib.error.HTTPError as e:
            return e.code, json.loads(e.read() or "null")

    def login(self, email, mdp="butrt"):
        return self.req("POST", "/auth.php", {"action": "login", "email": email, "password": mdp})


def test(num, description, attendu, condition):
    resultats.append((num, description, attendu, "OK" if condition else "ÉCHEC"))


hugo, emma, nathan, anonyme, admin = Client(), Client(), Client(), Client(), Client()

# --- Authentification -------------------------------------------------------
code, _ = hugo.login("hugo.petit@ufc", "mauvais")
test("T01", "Connexion avec un mauvais mot de passe", "refus 401", code == 401)
code, r = hugo.login("hugo.petit@ufc")
test("T02", "Connexion d'un étudiant", "succès, pas admin", code == 200 and r["is_admin"] is False)
code, r = hugo.req("GET", "/auth.php?action=me")
test("T03", "Session conservée entre deux requêtes", "connected = true", r.get("connected") is True)
code, _ = anonyme.req("GET", "/matches.php")
test("T04", "Accès à l'API sans être connecté", "refus 401", code == 401)

# --- Mise en relation -------------------------------------------------------
code, r = hugo.req("GET", "/matches.php")
distances = [m["distance_km"] for m in r]
test("T05", "Étudiants compatibles (même créneau ±15 min)", "liste non vide",
     code == 200 and len(r) > 0)
test("T06", "Tri par distance entre domiciles", "ordre croissant", distances == sorted(distances))
test("T07", "Un étudiant n'apparaît qu'une fois", "pas de doublon",
     len({m["id"] for m in r}) == len(r))
code, r = hugo.req("GET", "/profil.php?action=lieux_trajet")
domiciles = [l for l in r if l["type"] == "domicile"]
test("T08", "Lieux proposés pour un trajet", "seul son propre domicile",
     len(domiciles) == 1 and "Hugo" in domiciles[0]["nom"])

# --- Véhicules --------------------------------------------------------------
code, r = hugo.req("POST", "/vehicules.php", {"immatriculation": "TS-001-AA", "nb_places": 2,
                                              "marque": "Renault", "modele": "Twingo"})
id_vehicule = r.get("id")
test("T09", "Ajout d'un véhicule", "succès", code == 200 and r.get("success"))
code, r = hugo.req("PUT", "/vehicules.php", {"id": id_vehicule, "immatriculation": "TS-001-AA",
                                             "nb_places": 2, "marque": "Renault", "modele": "Clio"})
_, liste = hugo.req("GET", "/vehicules.php")
test("T10", "Modification du véhicule", "modèle = Clio",
     any(v["modele"] == "Clio" for v in liste if v["id"] == int(id_vehicule)))
emma.login("emma.bernard@ufc")
emma.req("DELETE", f"/vehicules.php?id={id_vehicule}")
_, liste = hugo.req("GET", "/vehicules.php")
test("T11", "Suppression du véhicule d'un autre étudiant", "véhicule toujours présent",
     any(v["id"] == int(id_vehicule) for v in liste))

# --- Trajets et équipages ---------------------------------------------------
code, r = hugo.req("POST", "/trajets.php", {"action": "create", "date_trajet": "2026-05-04",
                                            "heure_depart": "07:30", "type": "aller",
                                            "id_lieu_depart": 12, "id_lieu_arrivee": 1,
                                            "id_vehicule": id_vehicule, "participation": "1 €"})
id_trajet = r.get("id")
test("T12", "Création d'un trajet", "succès", code == 200 and r.get("success"))
_, mes = hugo.req("GET", "/trajets.php?type=mes")
test("T13", "Le créateur devient conducteur", "rôle conducteur",
     any(t["id"] == int(id_trajet) and t["role"] == "conducteur" for t in mes))
_, dispo = emma.req("GET", "/trajets.php?type=disponibles")
test("T14", "Le trajet est proposé aux autres", "visible dans « disponibles »",
     any(t["id"] == int(id_trajet) for t in dispo))
code, r = emma.req("POST", "/trajets.php", {"action": "rejoindre", "id_trajet": id_trajet})
test("T15", "Rejoindre un trajet", "succès", code == 200 and r.get("success"))
nathan.login("nathan.moreau@ufc")
code, _ = nathan.req("POST", "/trajets.php", {"action": "rejoindre", "id_trajet": id_trajet})
test("T16", "Rejoindre un trajet complet (2 places)", "refus 400", code == 400)
code, _ = nathan.req("POST", "/trajets.php", {"action": "kick", "id_trajet": id_trajet, "id_etudiant": 2})
test("T17", "Retirer un passager sans être le conducteur", "refus 403", code == 403)
code, membres = hugo.req("GET", f"/trajets.php?type=membres&id_trajet={id_trajet}")
test("T18", "Le conducteur voit son équipage", "2 membres", code == 200 and len(membres) == 2)
code, r = emma.req("POST", "/trajets.php", {"action": "quitter", "id_trajet": id_trajet})
code2, r2 = nathan.req("POST", "/trajets.php", {"action": "rejoindre", "id_trajet": id_trajet})
test("T19", "Une place se libère quand un passager quitte", "un autre peut rejoindre",
     code2 == 200 and r2.get("success"))
code, r = hugo.req("POST", "/trajets.php", {"action": "kick", "id_trajet": id_trajet, "id_etudiant": 5})
test("T20", "Le conducteur retire un passager", "succès", code == 200 and r.get("success"))
code, _ = emma.req("DELETE", f"/trajets.php?id={id_trajet}")
test("T21", "Supprimer le trajet d'un autre", "refus 403", code == 403)
code, r = hugo.req("DELETE", f"/trajets.php?id={id_trajet}")
_, mes = hugo.req("GET", "/trajets.php?type=mes")
test("T22", "Le créateur supprime son trajet", "succès, trajet disparu",
     code == 200 and r.get("success") and all(t["id"] != int(id_trajet) for t in mes))
hugo.req("DELETE", f"/vehicules.php?id={id_vehicule}")

# --- Administration ---------------------------------------------------------
code, _ = hugo.req("GET", "/admin.php?table=etudiant")
test("T23", "Un étudiant appelle l'API d'administration", "refus 403", code == 403)
code, r = admin.login("admin", "root")
test("T24", "Connexion administrateur", "is_admin = true", r.get("is_admin") is True)
code, r = admin.req("GET", "/admin.php?table=etudiant")
test("T25", "L'admin liste les étudiants", "7 étudiants", code == 200 and len(r) == 7)
code, _ = admin.req("GET", "/admin.php?table=mysql.user")
test("T26", "L'admin demande une table hors liste blanche", "refus 400", code == 400)

# --- Rapport ----------------------------------------------------------------
print(f"{'N°':<4} {'Résultat':<8} {'Test':<52} Attendu")
for num, desc, attendu, res in resultats:
    print(f"{num:<4} {res:<8} {desc:<52} {attendu}")
echecs = sum(r[3] != "OK" for r in resultats)
print(f"\n{len(resultats) - echecs}/{len(resultats)} tests réussis")
sys.exit(1 if echecs else 0)
