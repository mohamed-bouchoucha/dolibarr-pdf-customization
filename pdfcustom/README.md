# 💎 ONR Negoce PDF Suite for Dolibarr 23.0.x

Ce module fournit une suite complète de modèles PDF personnalisés, conçus avec une esthétique **Premium** et optimisés pour les besoins de **ONR Negoce**. Il respecte strictement l'architecture Dolibarr pour garantir une compatibilité maximale lors des mises à jour.

## 🚀 Fonctionnalités Clés

### 🎨 Design Premium Unifié
*   **Charte Graphique** : Utilisation systématique du bleu nuit (#1A2B5E) pour les en-têtes et les éléments structurants.
*   **Tableaux Modernes** : Lignes alternées (gris clair #F5F7FA) pour une lisibilité accrue.
*   **Badges Document** : Titre du document stylisé en haut à droite avec fond coloré.
*   **En-tête Dynamique** : Logo redimensionné intelligemment et informations de société claires.

### 🧠 Intelligence & Automatisation
*   **Gestion des Extrafields** : Affichage dynamique des champs personnalisés (Réf Commande, Mode de livraison, etc.) uniquement s'ils sont renseignés.
*   **Bloc de Signature Intelligent** : 
    *   Automatique sur les Devis (Propales).
    *   Conditionnel sur Factures/Commandes via l'attribut `show_signature`.
    *   Zone d'observations et cadre pour cachet entreprise.
*   **Sécurité des Sauts de Page** : Calcul dynamique de la hauteur des lignes pour éviter les textes coupés ou les chevauchements de pied de page.

### 🚛 Spécificités Logistiques (Bons de Livraison)
*   **Zéro Prix** : Confidentialité commerciale totale sur les documents de transport.
*   **Code-barres C128** : Pour un scan rapide en entrepôt.
*   **Checkboxes de Conformité** : Cases à cocher sur chaque ligne pour faciliter la réception client.
*   **Gestion du Poids** : Affichage du poids total du colis.

## 📁 Liste des Modèles Inclus

| Type de Document | Nom du Modèle | Position |
| :--- | :--- | :--- |
| **Facture** | `onrnegoce_facture` | Facturation > Factures |
| **Devis** | `onrnegoce_propale` | Commerce > Devis |
| **Commande** | `onrnegoce_commande` | Commerce > Commandes |
| **Expédition** | `onrnegoce_expedition` | Commerce > Expéditions |
| **Livraison (BL)** | `onrnegoce_livraison` | Commerce > Expéditions |

## 🛠️ Installation & Configuration

1.  **Installation** : Déposez le dossier `pdfcustom` dans le répertoire `/custom/` de votre Dolibarr.
2.  **Activation** : Allez dans `Configuration -> Modules` et activez le module **PdfCustom**.
3.  **Choix des modèles** : Pour chaque type de document (ex: Facture), allez dans sa configuration et sélectionnez le modèle correspondant dans la liste déroulante des modèles de documents.
4.  **Extrafields** : Pour activer la signature sur facture, créez un attribut supplémentaire de type "Case à cocher" avec le code `show_signature`.

## 🛡️ Conformité Technique
*   **Dolibarr** : Testé sur v23.0.x.
*   **TCPDF** : Utilise la version native incluse.
*   **PHP** : Compatible PHP 8.1 / 8.2+.
*   **Audit** : Audit de sécurité et de robustesse effectué (Avril 2026).

---
*Développé pour ONR Negoce par Architecte PHP.*
