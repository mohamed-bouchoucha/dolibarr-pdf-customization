# 💎 ONR Negoce PDF Suite for Dolibarr 23.0.x

Ce module fournit une suite complète de modèles PDF personnalisés, conçus avec une esthétique **Premium** et optimisés pour les besoins de **ONR Negoce**. Il respecte strictement l'architecture Dolibarr pour garantir une compatibilité maximale lors des mises à jour.

## 🚀 Fonctionnalités Clés / Key Features

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

## 📁 Liste des Modèles Inclus / Included Models

| Type de Document | Nom du Modèle | Position |
| :--- | :--- | :--- |
| **Facture** | `onrnegoce_facture` | Facturation > Factures |
| **Devis** | `onrnegoce_propale` | Commerce > Devis |
| **Commande** | `onrnegoce_commande` | Commerce > Commandes |
| **Expédition** | `onrnegoce_expedition` | Commerce > Expéditions |
| **Livraison (BL)** | `onrnegoce_livraison` | Commerce > Expéditions |

---

# 📖 Detailed Installation & Configuration Guide

## 1. Prerequisites
Before starting, ensure you have the following installed on your system:
- **Local Server Environment:** XAMPP (or similar like WAMP/MAMP)
- **Web Server:** Apache
- **PHP Version:** 8.1 or 8.2 (Strictly recommended for Dolibarr 23.x compatibility)
- **Database:** MariaDB (or MySQL 5.7+)
- **Version Control:** Git

## 2. Dolibarr Installation (Step-by-Step)
### Step 1: Download Dolibarr
Download the Dolibarr ERP/CRM version 23.0.2 from the official GitHub repository or website.

### Step 2: Setup in XAMPP
1. Extract the downloaded Dolibarr archive.
2. Copy the `htdocs` folder from the extracted archive.
3. Paste it into your XAMPP web root directory (usually `C:\xampp\htdocs\`).
4. Rename the folder from `htdocs` to `dolibarr` (resulting path: `C:\xampp\htdocs\dolibarr`).

### Step 3: Database Configuration
1. Start **Apache** and **MySQL** from the XAMPP Control Panel.
2. Open your browser and go to `http://localhost/phpmyadmin`.
3. Create a new database named `dolibarr`.
4. Create a dedicated database user (optional but recommended) or use `root` with no password for local testing.

### Step 4: Web Installer
1. Navigate to `http://localhost/dolibarr/` in your browser.
2. The Dolibarr installation wizard will start automatically.
3. Follow the on-screen instructions:
   - **Prerequisites check:** Ensure all PHP extensions are green.
   - **Directory setup:** Verify the web and document root paths.
   - **Database setup:** Enter your database name (`dolibarr`), user (`root`), and password.
   - **Admin account:** Create your administrator login credentials.

## 3. Post-Install Configuration
After logging in as administrator, configure the base system:
1. **Company Information:**
   - Go to **Home > Setup > Company/Organization**.
   - Fill in your company details and upload your `logo.jpg`. *The custom PDF templates will automatically use this logo.*
2. **Enable Core Modules:**
   - Go to **Home > Setup > Modules/Applications**.
   - Enable the following standard modules: Third Parties, Invoices, Commercial Proposals, Sales Orders, Shipments.
3. **Document Directories:**
   - Dolibarr automatically creates a `documents` folder. Ensure your web server has write permissions to this folder.

## 4. Custom Module Installation
The custom PDF templates are encapsulated in a standalone Dolibarr module named `pdfcustom`.

### Folder Structure
The module must be placed in the `/custom` directory to ensure core files remain untouched.
```text
C:\xampp\htdocs\dolibarr\custom\pdfcustom\
├── core\
│   └── modules\
│       ├── modPdfcustom.class.php  (Module descriptor)
│       ├── facture\doc\pdf_onrnegoce_facture.modules.php
│       ├── commande\doc\pdf_onrnegoce_commande.modules.php
│       ├── propale\doc\pdf_onrnegoce_propale.modules.php
│       ├── expedition\doc\pdf_onrnegoce_expedition.modules.php
│       └── expedition\doc\pdf_onrnegoce_livraison.modules.php
├── README.md
└── TESTS.md
```

### Installation Steps
1. Place the `pdfcustom` folder inside `C:\xampp\htdocs\dolibarr\custom\`.
2. Ensure the `custom` directory is enabled in your `conf/conf.php` file:
   ```php
   $dolibarr_main_url_root_alt='/custom';
   $dolibarr_main_document_root_alt='C:/xampp/htdocs/dolibarr/custom';
   ```
3. Go to **Home > Setup > Modules/Applications** in Dolibarr.
4. Locate the **PdfCustom** module under the "Other" tab and click the toggle switch to enable it.

## 5. Activating PDF Templates
Once the module is active, the custom templates become available for selection.
1. **Invoices:** Go to **Setup > Modules/Applications > Invoices** (gear icon). Select `onrnegoce_facture` as the default model.
2. **Proposals:** Go to **Setup > Modules/Applications > Proposals**. Select `onrnegoce_propale`.
3. **Orders:** Go to **Setup > Modules/Applications > Sales Orders**. Select `onrnegoce_commande`.
4. **Shipments & Delivery Notes:** Go to **Setup > Modules/Applications > Shipments**. Select `onrnegoce_expedition` and `onrnegoce_livraison`.
5. **Extrafields:** To activate the signature on invoices, create a supplementary attribute of type "Checkbox" with the code `show_signature` in the Invoices module setup.

## 6. Running the Project (Example Workflow)
To test the custom Invoice PDF generation:
1. Navigate to **Billing | Payment > New Invoice**.
2. Select a customer and create a draft invoice.
3. Add a few products or services to the invoice.
4. Scroll down to the **Linked Files** (Fichiers joints) section.
5. Ensure `onrnegoce_facture` is selected in the "Document model" dropdown.
6. Click the **GENERATE** button.
7. Click the magnifying glass icon next to the generated `(PROV).pdf` file to preview the custom premium design.

## 7. Multilingual Configuration
Dolibarr handles translations dynamically.
1. **Language Setup:** Go to **Home > Setup > Display** to set the default application language (e.g., `fr_FR` or `en_US`).
2. **Dynamic Translations:** The custom PDF templates use Dolibarr's built-in translation system (`$outputlangs->trans()`). This ensures that labels like "Total HT" or "Invoice" automatically translate based on the customer's preferred language.

## 8. Troubleshooting
| Issue | Cause | Solution |
| :--- | :--- | :--- |
| **PDF template not showing in dropdown** | Module not active or `custom` folder not configured. | Check `conf.php` for `document_root_alt`. Re-disable and re-enable the `PdfCustom` module. |
| **"Undefined array key" PHP Warning** | Strict PHP 8.x behavior in Dolibarr core. | Set `display_errors=Off` in your `php.ini` or patch the specific core file with `isset()` checks. |
| **Logo not appearing on PDF** | Logo not uploaded or wrong permissions. | Upload logo via **Setup > Company**. Ensure the `documents/mycompany/logos/` folder is readable. |
| **Blank page when generating PDF** | PHP Fatal Error. | Check Apache `error.log`. Ensure Dolibarr 23.x core files are intact. |

## 9. Git Usage
To manage the custom module via Git:
1. Open your terminal (Git Bash or PowerShell).
2. Navigate to the custom directory: `cd C:/xampp/htdocs/dolibarr/custom`
3. Clone the repository (if setting up on a new machine):
   `git clone https://github.com/mohamed-bouchoucha/dolibarr-pdf-customization.git pdfcustom`
4. To update the repository with new changes:
   ```bash
   git add pdfcustom/
   git commit -m "feat: description of changes"
   git push origin main
   ```

## 10. Best Practices
- **Never Modify Core Files:** Always use the `/custom` directory for modules, overrides, and templates. This ensures your changes survive Dolibarr updates.
- **Use Subclasses:** The custom PDFs extend core Dolibarr classes (e.g., `ModelePDFFactures`). This maintains compatibility with core logic.
- **Backups:** Regularly backup your `documents` folder and your MariaDB database before applying any Dolibarr version upgrades.

---
## 🛡️ Conformité Technique
*   **Dolibarr** : Testé sur v23.0.x.
*   **TCPDF** : Utilise la version native incluse.
*   **PHP** : Compatible PHP 8.1 / 8.2+.
*   **Audit** : Audit de sécurité et de robustesse effectué (Avril 2026).

---
*Développé pour ONR Negoce par Architecte PHP.*
