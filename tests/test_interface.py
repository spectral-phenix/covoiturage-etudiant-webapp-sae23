"""Tests de l'interface dans un vrai navigateur (Chromium piloté par Playwright).

Chaque test clique dans l'application comme un utilisateur, puis vérifie
directement en base que l'action a bien été enregistrée.

Prérequis :
    pip install playwright && playwright install chromium
    client en ligne de commande `mysql` (ou `mariadb`) ayant accès à la base
    base de démonstration database/fcsm.sql fraîchement importée

Usage :
    python tests/test_interface.py [URL]          # défaut http://localhost:8000
    MYSQL_ARGS="-h 127.0.0.1 -u fcsm -pfcsm" python tests/test_interface.py
"""
import os
import shlex
import subprocess
import sys

from playwright.sync_api import sync_playwright

B = (sys.argv[1] if len(sys.argv) > 1 else "http://localhost:8000").rstrip("/")
MYSQL = ["mysql", *shlex.split(os.getenv("MYSQL_ARGS", "")), "-N", os.getenv("DB_NAME", "FCSM")]
R = {}


def q(sql):
    """Exécute une requête SQL et renvoie le résultat brut (pour vérifier la base)."""
    return subprocess.run([*MYSQL, "-e", sql], capture_output=True, text=True).stdout.strip()

with sync_playwright() as p:
    b=p.chromium.launch(); pg=b.new_page(); pg.on('dialog',lambda d:d.accept())
    pg.goto(B+'/index.html'); pg.wait_for_timeout(500)
    pg.fill('#login-form input[type=text]','hugo.petit@ufc'); pg.fill('#login-form input[type=password]','faux'); pg.click('#login-form button'); pg.wait_for_timeout(800)
    R['Connexion refusée avec un mauvais mot de passe, message affiché']=pg.inner_text('body').count('incorrect')>0 and pg.is_visible('#login-form')
    pg.fill('#login-form input[type=password]','butrt'); pg.click('#login-form button'); pg.wait_for_timeout(1500)
    R['Accueil : 4 étudiants compatibles + compteur des suivants']=pg.locator('#page-accueil').inner_text().count('autres étudiants compatibles')==1
    pg.click('.nav-item[data-page=creer]'); pg.wait_for_timeout(700)
    pg.select_option('#f-type','aller'); pg.wait_for_timeout(200); v1=pg.is_visible('#retour-fields')
    pg.select_option('#f-type','aller-retour'); pg.wait_for_timeout(200); v2=pg.is_visible('#retour-fields')
    R['Champs du retour affichés seulement en aller-retour']=(not v1) and v2
    opts=pg.eval_on_selector_all('#f-lieu-depart option','e=>e.map(o=>o.textContent)')
    R['Seul son propre domicile est proposé comme lieu']=[o for o in opts if 'Domicile' in o]==['Domicile Hugo Petit — Valentigney']
    pg.click('.nav-item[data-page=vehicule]'); pg.wait_for_timeout(700)
    pg.click('text=Modifier'); pg.wait_for_timeout(400)
    R['Modifier un véhicule : formulaire pré-rempli']=pg.input_value('#modal-marque')=='Porsche'
    pg.fill('#modal-modele','911'); pg.click('#modal-save'); pg.wait_for_timeout(700)
    R['Modification du véhicule enregistrée en base']=q("select modele from vehicule where id=4")=='911'
    pg.fill('#form-vehicule input[name=marque]','Fiat'); pg.fill('#form-vehicule input[name=modele]','Panda')
    pg.fill('#form-vehicule input[name=immatriculation]','ZZ-999-ZZ'); pg.click('#form-vehicule button[type=submit]'); pg.wait_for_timeout(800)
    R['Ajout d\'un véhicule enregistré en base']=q("select count(*) from vehicule where immatriculation='ZZ-999-ZZ'")=='1'
    pg.locator('.card',has_text='Panda').locator('text=Supprimer').click(); pg.wait_for_timeout(800)
    R['Suppression d\'un véhicule (avec confirmation)']=q("select count(*) from vehicule where immatriculation='ZZ-999-ZZ'")=='0'
    pg.click('.nav-item[data-page=profil]'); pg.wait_for_timeout(700)
    pg.fill('#p-ville','Audincourt'); pg.click('#form-domicile button'); pg.wait_for_timeout(700)
    R['Modification du domicile enregistrée']=q("select ville from lieu where id=12")=='Audincourt'
    pw=pg.locator('#form-mdp input[type=password]'); pw.nth(0).fill('nouveau1'); pw.nth(1).fill('different'); pg.click('#form-mdp button'); pg.wait_for_timeout(600)
    a=q("select password from etudiant where id=3")
    pw.nth(0).fill('nouveau1'); pw.nth(1).fill('nouveau1'); pg.click('#form-mdp button'); pg.wait_for_timeout(600)
    R['Mot de passe changé seulement si les deux saisies concordent']=a=='butrt' and q("select password from etudiant where id=3")=='nouveau1'
    pg.click('.nav-item[data-page=rejoindre]'); pg.wait_for_timeout(700)
    n=pg.locator('#page-rejoindre .card').count(); pg.locator('#page-rejoindre button',has_text='Rejoindre').first.click(); pg.wait_for_timeout(900)
    pg.click('.nav-item[data-page=rejoindre]'); pg.wait_for_timeout(700)
    R['Un trajet rejoint quitte la liste des trajets disponibles']=pg.locator('#page-rejoindre .card').count()==n-1
    a=b.new_page(); a.on('dialog',lambda d:d.accept())
    a.goto(B+'/index.html'); a.wait_for_timeout(400); a.fill('#login-form input[type=text]','admin'); a.fill('#login-form input[type=password]','root'); a.click('#login-form button'); a.wait_for_timeout(1500)
    R['Le compte admin est redirigé vers le panneau d\'administration']='admin_panel' in a.url
    a.click('.admin-nav-item[data-panel=lieux]'); a.wait_for_timeout(700)
    a.click("button[onclick=\"openModal('lieu')\"]"); a.wait_for_timeout(300)
    a.fill('#mf-nom','Piscine Pierre-de-Coubertin'); 
    if a.locator('#mf-ville').count(): a.fill('#mf-ville','Montbéliard')
    a.click('#modal-save-btn'); a.wait_for_timeout(800)
    R['Admin : ajout d\'une ligne']=q("select count(*) from lieu where nom like 'Piscine%'")=='1'
    lid=q("select id from lieu where nom like 'Piscine%'")
    row=a.locator('#table-lieux tr',has_text='Piscine'); row.locator('text=Modifier').click(); a.wait_for_timeout(300)
    a.fill('#mf-nom','Piscine municipale'); a.click('#modal-save-btn'); a.wait_for_timeout(800)
    R['Admin : modification d\'une ligne']=q(f"select nom from lieu where id={lid}")=='Piscine municipale'
    a.locator('#table-lieux tr',has_text='Piscine').locator('button').last.click(); a.wait_for_timeout(800)
    R['Admin : suppression d\'une ligne']=q(f"select count(*) from lieu where id={lid}")=='0'
    b.close()
for k, v in R.items():
    print("OK    " if v else "ÉCHEC ", k)
echecs = sum(not v for v in R.values())
print(f"\n{len(R) - echecs}/{len(R)} tests réussis")
sys.exit(1 if echecs else 0)
