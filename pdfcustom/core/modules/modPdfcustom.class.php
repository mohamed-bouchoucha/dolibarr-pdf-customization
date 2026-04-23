<?php
/* Copyright (C) 2026-2027  Architecte PHP  <votre@email.com> */

/**
 *  \defgroup   pdfcustom     Module PdfCustom
 *  \brief      Module to provide custom PDF templates
 *  \file       custom/pdfcustom/core/modules/modPdfcustom.class.php
 *  \ingroup    pdfcustom
 *  \brief      Descripteur du module PdfCustom pour Dolibarr
 */

include_once DOL_DOCUMENT_ROOT.'/core/modules/DolibarrModules.class.php';

/**
 *  Classe de description du module PdfCustom
 */
class modPdfcustom extends DolibarrModules
{
    /**
     *  Constructeur du module
     *
     *  @param      DoliDB      $db      Gestionnaire de base de données
     */
    public function __construct($db)
    {
        global $conf, $langs;

        $this->db = $db;

        // Identifiant unique du module (> 100000 pour les modules tiers)
        $this->numero = 500100;

        // Clé utilisée dans llx_const pour activer le module
        $this->rights_class = 'pdfcustom';

        // Famille du module (crm, financial, hr, projects, products, ecm, interface, other)
        $this->family = "other";

        // Position dans le menu de la famille
        $this->module_position = '90';

        // Nom du module (sans espaces)
        $this->name = preg_replace('/^mod/i', '', get_class($this));

        // Description affichée dans l'interface Dolibarr
        $this->description = "Module regroupant des modeles PDF personnalises (Factures, Commandes, Propales, Expeditions)";

        // Version du module
        $this->version = '1.0.0';

        // Constante pour l'activation
        $this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);

        // Icône du module
        $this->picto = 'pdf';

        // Auteur
        $this->editor_name = 'Architecte PHP';
        $this->editor_url = '';

        // Pas de dépendance
        $this->depends = array();
        $this->requiredby = array();
        $this->conflictwith = array();

        // Pas de fichier langue dédié
        $this->langfiles = array();

        // Déclare le répertoire du module pour le scan des modèles PDF
        // Sans cela, Dolibarr ne scannera pas /custom/pdfcustom/ pour trouver les templates
        $this->module_parts = array(
            'models' => 1,
        );

        // Pas de répertoire spécifique à créer
        $this->dirs = array();

        // Pas de page de configuration
        $this->config_page_url = array();

        // Constantes du module (vide)
        $this->const = array();

        // Pas de boîtes
        $this->boxes = array();

        // Pas de permissions spécifiques
        $this->rights = array();

        // Pas de menus
        $this->menu = array();
    }

    /**
     *  Fonction appelée lors de l'activation du module.
     *  Enregistre les modèles PDF dans la table llx_document_model.
     *
     *  @param      string  $options    Options
     *  @return     int                 1 si OK, 0 si KO
     */
    public function init($options = '')
    {
        global $conf;

        // Liste des modèles à enregistrer : array('nom_modele' => 'type_document')
        $models = array(
            'onrnegoce_facture'    => 'invoice',
            'onrnegoce_commande'   => 'order',
            'onrnegoce_propale'    => 'propal',
            'onrnegoce_expedition' => 'shipping',
            'onrnegoce_livraison'  => 'shipping',
        );

        $sql = array();
        foreach ($models as $nom => $type) {
            // Supprime l'entrée existante pour éviter les doublons
            $sql[] = "DELETE FROM ".MAIN_DB_PREFIX."document_model WHERE nom = '".$this->db->escape($nom)."' AND type = '".$this->db->escape($type)."' AND entity = ".((int) $conf->entity);
            // Insère le modèle
            $sql[] = "INSERT INTO ".MAIN_DB_PREFIX."document_model (nom, type, entity) VALUES('".$this->db->escape($nom)."', '".$this->db->escape($type)."', ".((int) $conf->entity).")";
        }

        return $this->_init($sql, $options);
    }

    /**
     *  Fonction appelée lors de la désactivation du module.
     *  Supprime les modèles PDF de la table llx_document_model.
     *
     *  @param      string  $options    Options
     *  @return     int                 1 si OK, 0 si KO
     */
    public function remove($options = '')
    {
        global $conf;

        $models = array(
            'onrnegoce_facture'    => 'invoice',
            'onrnegoce_commande'   => 'order',
            'onrnegoce_propale'    => 'propal',
            'onrnegoce_expedition' => 'shipping',
            'onrnegoce_livraison'  => 'shipping',
        );

        $sql = array();
        foreach ($models as $nom => $type) {
            $sql[] = "DELETE FROM ".MAIN_DB_PREFIX."document_model WHERE nom = '".$this->db->escape($nom)."' AND type = '".$this->db->escape($type)."' AND entity = ".((int) $conf->entity);
        }

        return $this->_remove($sql, $options);
    }
}
