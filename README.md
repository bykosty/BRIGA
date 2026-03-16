[README.md](https://github.com/user-attachments/files/26033597/README.md)
# BRIGA Core V5

## Ce que contient cette version
- Tout de V4
- Module Plan de salle : 48 tables (Salle Gilbert, Terrasse, Côté cuisine)
- Statuts tables : libre / réservée / occupée / nettoyage
- Fusion / déplacement de tables
- Compteurs par zone en temps réel

## Ce qui a été ajouté par rapport à la version précédente
Ajout du module Plan de salle — version complète.

## Comment tester
1. Uploader BRIGA-V5.zip → Désactiver V4 → Activer V5
2. Onglet Plan de salle → vérifier les 3 zones (Gilbert, Terrasse, Cuisine)
3. Cliquer sur une table → changer le statut
4. Vérifier les compteurs par zone
5. F12 → Console : `[BRIGA V5] ✅ Tous les modules OK`

## Ce qui reste à faire
- Casse & pertes
- Stock bar quotidien (Stock Bar)
- Offerts
- Réservations multi-sources
- Arrivée client (pont réservations ↔ salle)
- Journal de service
- Analyse 90 jours
- Mode Offline-First (PWA)
