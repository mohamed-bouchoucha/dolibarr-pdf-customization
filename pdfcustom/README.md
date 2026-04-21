# Module PDF Custom pour Dolibarr 23.0.x

Ce module est conçu pour héberger des modèles PDF personnalisés sans modifier les fichiers du noyau (core) de Dolibarr, conformément aux bonnes pratiques.

## Contenu du module
- **Factures** : Modèle `onrnegoce_facture` (Basé sur le squelette `sponge`).
- **Autres documents** : Dossiers prêts pour Commandes, Propales et Expéditions.

## Installation
1. Copiez ce dossier dans le répertoire `custom/` de votre installation Dolibarr.
2. Activez le module nommé **PdfCustom** dans `Configuration -> Modules`.
3. Configurez les modèles individuels dans la partie configuration de chaque module (ex: Configuration du module Facture pour activer `onrnegoce_facture`).

## Convention de nommage
Les fichiers doivent suivre la convention suivante pour être détectés automatiquement par Dolibarr :
- **Fichier** : `pdf_[nom].modules.php`
- **Classe** : `pdf_[nom]` héritant de `ModelePDFFactures` (ou équivalent pour les autres types).
- **Emplacement** : `core/modules/[type]/doc/`

## Support TCPDF
Tous les modèles utilisent nativement la librairie TCPDF incluse dans Dolibarr.
