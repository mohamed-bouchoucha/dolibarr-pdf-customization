<?php
/* Copyright (C) 2026-2027  Architecte PHP  <votre@email.com> */

/**
 *  \file       custom/pdfcustom/core/modules/propale/doc/pdf_onrnegoce_propale.modules.php
 *  \ingroup    propale
 *  \brief      Modèle de proposition commerciale personnalisé ONR Negoce
 */

require_once DOL_DOCUMENT_ROOT.'/core/modules/propale/modules_propale.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/pdf.lib.php';

class pdf_onrnegoce_propale extends ModelePDFPropales
{
    public $db;
    public $name;
    public $description;
    public $version = 'dolibarr';

    public function __construct($db)
    {
        global $langs;
        $this->db = $db;
        $this->name = "onrnegoce_propale";
        $this->description = "Modèle PDF ONR Négoce pour Propositions Commerciales";
        
        $this->type = 'pdf';
        $formatarray = pdf_getFormat();
        $this->page_largeur = $formatarray['width'];
        $this->page_hauteur = $formatarray['height'];
        $this->format = array($this->page_largeur, $this->page_hauteur);
        
        $langs->loadLangs(array("main", "propal"));
    }

    public function write_file($object, $outputlangs, $srctemplatepath = '', $hidedetails = 0, $hidedesc = 0, $hideref = 0)
    {
        global $conf, $mysoc;
        if (!is_object($outputlangs)) $outputlangs = $GLOBALS['langs'];

        $objectref = dol_sanitizeFileName($object->ref);
        $dir = $conf->propal->multidir_output[$object->entity ?? $conf->entity]."/".$objectref;
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
        $pdf->MultiCell(0, 10, "PROPOSITION COMMERCIALE - MODÈLE ONR NEGOCE", 1, 'C');

        $this->_pagefoot($pdf, $object, $outputlangs);
        $pdf->Output($file, 'F');
        return 1;
    }

    protected function _pagehead(&$pdf, $object, $showaddress, $outputlangs)
    {
        global $conf, $mysoc;

        $page_width = $this->page_largeur;
        $margins = $pdf->getMargins();
        $posy = $margins['top'];

        // 1. LOGO DE L'ENTREPRISE
        $logo = $conf->mycompany->dir_output . '/logos/' . $mysoc->logo;
        if (!empty($mysoc->logo) && is_readable($logo)) {
            $height = pdf_getHeightForLogo($logo);
            $pdf->Image($logo, 10, $posy, 0, $height);
            $posy = max($posy, $posy + $height + 5);
        }

        // 2. NOM DE LA SOCIÉTÉ (#1A2B5E, Bold 14pt)
        $pdf->SetTextColor(26, 43, 94);
        $pdf->SetFont('', 'B', 14);
        $pdf->SetXY(10, $posy); 
        $pdf->MultiCell(100, 6, $mysoc->name, 0, 'L');
        $posy = $pdf->GetY();

        // 3. ADRESSE DE LA SOCIÉTÉ (Noir, 9pt, 2 lignes)
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('', '', 9);
        $adresse_soc = $mysoc->address . "\n" . $mysoc->zip . " " . $mysoc->town;
        $pdf->SetXY(10, $posy);
        $pdf->MultiCell(100, 4, $adresse_soc, 0, 'L');

        // 4. NUMÉRO DE DOCUMENT (Badge aligné à droite, #1A2B5E)
        $label = $outputlangs->transnoentities("Proposal");
        $badge_text = strtoupper($label) . " " . $object->ref;

        $pdf->SetFillColor(26, 43, 94);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('', 'B', 12);
        
        $badge_width = 70;
        $pdf->SetXY($page_width - $margins['right'] - $badge_width, $margins['top']);
        $pdf->MultiCell($badge_width, 8, $badge_text, 0, 'C', 1);

        // 5. TRAIT SÉPARATEUR (#1A2B5E, épaisseur 0.5mm)
        $bottom_y = max($pdf->GetY(), $posy + 15);
        $pdf->SetDrawColor(26, 43, 94);
        $pdf->SetLineWidth(0.5);
        $pdf->Line(10, $bottom_y, $page_width - 10, $bottom_y);

        // Reset styles for body
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->SetLineWidth(0.2);
        $pdf->SetY($bottom_y + 5);
    }

    protected function _pagefoot(&$pdf, $object, $outputlangs, $hidefreetext = 0)
    {
        global $conf, $mysoc;

        $page_width = $this->page_largeur;
        $page_height = $this->page_hauteur;
        $footer_y = $page_height - 25;

        // 1. TRAIT SÉPARATEUR (#1A2B5E, épaisseur 0.5mm)
        $pdf->SetDrawColor(26, 43, 94);
        $pdf->SetLineWidth(0.5);
        $pdf->Line(10, $footer_y, $page_width - 10, $footer_y);

        // 2. MENTIONS LÉGALES (7pt, gris #888888, 1 ligne)
        $pdf->SetTextColor(136, 136, 136);
        $pdf->SetFont('', '', 7);
        $pdf->SetXY(10, $footer_y + 2);

        $legals = array();
        if (!empty($mysoc->forme_juridique_code)) $legals[] = $mysoc->forme_juridique_code;
        if (!empty($mysoc->capital)) $legals[] = $outputlangs->transnoentities("CapitalOf", $mysoc->capital) . ' ' . $outputlangs->transnoentities("Currency" . $conf->currency);
        if (!empty($mysoc->idprof1)) $legals[] = $outputlangs->transnoentities("ProfId1") . " : " . $mysoc->idprof1;
        if (!empty($mysoc->idprof2)) $legals[] = $outputlangs->transnoentities("ProfId2") . " : " . $mysoc->idprof2;
        if (!empty($mysoc->tva_intra)) $legals[] = $outputlangs->transnoentities("VATIntra") . " : " . $mysoc->tva_intra;

        $legal_text = implode(' - ', array_filter($legals));
        $pdf->MultiCell(0, 3, $legal_text, 0, 'C');

        // 3. SITE WEB ET EMAIL (Centré)
        $contact_info = array();
        if (!empty($mysoc->url)) $contact_info[] = $mysoc->url;
        if (!empty($mysoc->email)) $contact_info[] = $mysoc->email;
        $contact_text = implode(' | ', array_filter($contact_info));
        if ($contact_text) {
            $pdf->MultiCell(0, 3, $contact_text, 0, 'C');
        }

        // 4. NUMÉROTATION PAGE (Page X / Y)
        $pdf->SetFont('', 'B', 8);
        $pdf->SetTextColor(26, 43, 94);
        $pdf->SetY($page_height - 10);
        
        $pagetext = $outputlangs->transnoentities("Page") . " " . $pdf->getAliasNumPage() . " / " . $pdf->getAliasNbPages();
        $pdf->MultiCell(0, 4, $pagetext, 0, 'C');
    }
}
