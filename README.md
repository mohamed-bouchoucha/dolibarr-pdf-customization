# 📦 Dolibarr Customizations - ONR Negoce

Ce répertoire centralise les personnalisations effectuées sur l'instance Dolibarr de **ONR Negoce**, notamment la suite complète de documents PDF.

## 📌 Aperçu du Projet
L'objectif principal est de fournir des documents commerciaux (Factures, Devis, Commandes) et logistiques (BL) avec un design **Premium** cohérent, intégrant des fonctionnalités avancées comme les signatures dynamiques et les codes-barres.

## ⚙️ Stack Technique
- **Core** : Dolibarr 23.0.2
- **Moteur PDF** : TCPDF
- **Développement** : PHP 8.1+ / Git
- **Architecture** : Module tiers isolé dans `/custom/`

## 📂 Structure du Répertoire
*   [`/pdfcustom/`](./pdfcustom/) : Le module principal regroupant tous les templates.
    *   `core/modules/` : Emplacement des fichiers de définition des modèles.
    *   `README.md` : Documentation détaillée du module PDF.

## 🚀 Fonctionnalités Implémentées
1.  **Identité Visuelle** : Palette de couleurs #1A2B5E, en-têtes stylisés, logo automatique.
2.  **Productivité** : Intégration des Extrafields pour éviter la saisie manuelle.
3.  **Logistique** : Modèle BL dédié avec scan par code-barres et cases à cocher.
4.  **Légal** : Blocs de signature paramétrables pour la validation des bons pour accord.

## 🧪 Installation Rapide
1.  Copiez le contenu de ce dossier dans le répertoire `custom/` de votre Dolibarr.
2.  Activez le module **PdfCustom** dans l'interface d'administration.
3.  Sélectionnez les modèles `onrnegoce_...` dans la configuration de chaque type de document.

## 👨‍💻 Auteur
**Mohamed Bouchoucha** - ONR Negoce