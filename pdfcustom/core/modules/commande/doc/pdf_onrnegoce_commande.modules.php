<?php
/* Copyright (C) 2026-2027  Architecte PHP  <votre@email.com> */

/**
 *  \file       custom/pdfcustom/core/modules/commande/doc/pdf_onrnegoce_commande.modules.php
 *  \ingroup    commande
 *  \brief      Modèle de commande personnalisé ONR Negoce
 */

require_once DOL_DOCUMENT_ROOT.'/core/modules/commande/modules_commande.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/pdf.lib.php';

class pdf_onrnegoce_commande extends ModelePDFCommandes
{
    public $db;
    public $name;
    public $description;
    public $version = 'dolibarr';

    public function __construct($db)
    {
        global $langs;
        $this->db = $db;
        $this->name = "onrnegoce_commande";
        $this->description = "Modèle PDF ONR Négoce pour Commandes";
        
        $this->type = 'pdf';
        $formatarray = pdf_getFormat();
        $this->page_largeur = $formatarray['width'];
        $this->page_hauteur = $formatarray['height'];
        $this->format = array($this->page_largeur, $this->page_hauteur);
        
        $langs->loadLangs(array("main", "orders"));
    }

    public function write_file($object, $outputlangs, $srctemplatepath = '', $hidedetails = 0, $hidedesc = 0, $hideref = 0)
    {
        global $conf, $mysoc;
        if (!is_object($outputlangs)) $outputlangs = $GLOBALS['langs'];

        $objectref = dol_sanitizeFileName($object->ref);
        $dir = $conf->commande->multidir_output[$object->entity ?? $conf->entity]."/".$objectref;
        if (!file_exists($dir)) dol_mkdir($dir);
        $file = $dir."/".$objectref.".pdf";

        $pdf = pdf_getInstance($this->format);
        $pdf->SetTitle($outputlangs->convToOutputCharset($object->ref));
        $pdf->SetMargins(10, 10, 10);
        $pdf->setAutoPageBreak(true, 25);
        $pdf->AddPage();

        $this->_pagehead($pdf, $object, 1, $outputlangs);

        $pdf->SetFont('', 'B', 12);
        $pdf->SetXY(10, 100);
        $pdf->MultiCell(0, 10, "COMMANDE CLIENT - MODÈLE ONR NEGOCE", 1, 'C');

        $this->_pagefoot($pdf, $object, $outputlangs);
        $pdf->Output($file, 'F');
        return 1;
    }

    protected function _pagehead(&$pdf, $object, $showaddress, $outputlangs)
    {
        pdf_pagehead($pdf, $outputlangs, $this->page_hauteur);
        $pdf->SetFont('', 'B', 14);
        $pdf->SetXY(10, 60);
        $pdf->MultiCell(100, 5, $outputlangs->trans("Order")." : ".$object->ref, 0, 'L');
    }

    protected function _pagefoot(&$pdf, $object, $outputlangs)
    {
        return pdf_pagefoot($pdf, $outputlangs, 'COMMAND_FREE_TEXT', $this->emetteur, 10, 10, $this->page_largeur, $object);
    }
}
