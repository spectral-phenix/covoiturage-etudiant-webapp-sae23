"""Importe les données de data.json dans la base MySQL FCSM.

Le cahier des charges demande que les données puissent être saisies
d'abord sous forme de fichier JSON, puis en base : ce script fait le lien.

Usage :
    pip install mysql-connector-python
    python import_json.py               # importe database/data.json
    python import_json.py --vider       # vide d'abord les tables concernées
    python import_json.py autre.json

Connexion : variables d'environnement DB_HOST, DB_NAME, DB_USER, DB_PASS
(valeurs par défaut : localhost, FCSM, root, mot de passe vide).
"""
import json
import os
import sys
from pathlib import Path

import mysql.connector

TABLES = {
    # table : (clé dans le JSON, colonnes)
    "lieu": ("lieux", ["id", "nom", "adresse", "ville", "type", "code_postal", "longitude", "latitude"]),
    "etudiant": ("etudiants", ["id", "nom", "prenom", "email", "password", "groupe"]),
    "domicile": ("domiciles", ["type", "id_etudiant", "id_lieu"]),
    "vehicule": ("vehicules", ["id", "immatriculation", "nb_places", "id_etudiant",
                               "marque", "modele", "date_ct", "date_assurance"]),
    "horaire": ("horaires", ["id_etudiant", "jour_semaine", "heure_debut", "heure_fin", "id_lieu", "type"]),
}


def main():
    args = [a for a in sys.argv[1:] if not a.startswith("--")]
    vider = "--vider" in sys.argv
    fichier = Path(args[0]) if args else Path(__file__).with_name("data.json")

    with open(fichier, encoding="utf-8") as f:
        data = json.load(f)

    conn = mysql.connector.connect(
        host=os.getenv("DB_HOST", "localhost"),
        database=os.getenv("DB_NAME", "FCSM"),
        user=os.getenv("DB_USER", "root"),
        password=os.getenv("DB_PASS", ""),
    )
    cur = conn.cursor()
    try:
        if vider:
            # ordre inverse pour respecter les dépendances
            for table in ["membre_equipage", "trajet", *reversed(list(TABLES))]:
                cur.execute(f"DELETE FROM `{table}`")

        for table, (cle, colonnes) in TABLES.items():
            lignes = data.get(cle, [])
            requete = (f"INSERT INTO `{table}` ({', '.join(colonnes)}) "
                       f"VALUES ({', '.join(['%s'] * len(colonnes))})")
            cur.executemany(requete, [tuple(l[c] for c in colonnes) for l in lignes])
            print(f"{table:<10} {len(lignes):>3} ligne(s) importée(s)")

        conn.commit()
    except mysql.connector.Error as e:
        conn.rollback()
        sys.exit(f"Erreur MySQL, rien n'a été importé : {e}")
    finally:
        cur.close()
        conn.close()


if __name__ == "__main__":
    main()
