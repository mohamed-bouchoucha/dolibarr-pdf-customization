<?php
/* Copyright (C) 2026-2027  Architecte PHP  <votre@email.com> */

/**
 *  \file       custom/pdfcustom/core/modules/expedition/doc/pdf_onrnegoce_livraison.modules.php
 *  \ingroup    expedition
 *  \brief      Modèle PDF ONR Négoce pour Bons de Livraison (BL)
 */

require_once DOL_DOCUMENT_ROOT.'/core/modules/expedition/modules_expedition.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/pdf.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';

class pdf_onrnegoce_livraison extends ModelePdfExpedition
{
    public $db;
    public $name;
    public $description;
    public $version = 'dolibarr';

    public function __construct($db)
    {
        global $langs;
        $this->db = $db;
        $this->name = "onrnegoce_livraison";
        $this->description = "Bon de Livraison ONR Negoce avec Checkboxes & Code-barres";
        
        $this->type = 'pdf';
        $formatarray = pdf_getFormat();
        $this->page_largeur = $formatarray['width'];
        $this->page_hauteur = $formatarray['height'];
        $this->format = array($this->page_largeur, $this->page_hauteur);
        
        $langs->loadLangs(array("main", "sendings", "orders"));
    }

    public function write_file($object, $outputlangs, $srctemplatepath = '', $hidedetails = 0, $hidedesc = 0, $hideref = 0)
    {
        global $conf, $mysoc;

        if (!is_object($outputlangs)) $outputlangs = $GLOBALS['langs'];

        $objectref = dol_sanitizeFileName($object->ref);
        $dir = $conf->expedition->multidir_output[$object->entity ?? $conf->entity]."/sending/" . $objectref;
        if (!file_exists($dir)) dol_mkdir($dir);
        $file = $dir."/".$objectref.".pdf";

        $pdf = pdf_getInstance($this->format);
        $pdf->SetTitle($outputlangs->convToOutputCharset($object->ref));
        $pdf->SetMargins(10, 10, 10);
        $pdf->setAutoPageBreak(true, 25);
        $pdf->AddPage();

        $this->_pagehead($pdf, $object, 1, $outputlangs);

        $curY = 65; 

        // --- 1. ADRESSES COTE A COTE ---
        $pdf->SetFont('', 'B', 9);
        $pdf->SetXY(10, $curY); $pdf->Cell(90, 5, "Expéditeur :", 0, 0);
        $pdf->SetXY(110, $curY); $pdf->Cell(90, 5, "Destinataire / Livraison :", 0, 1);
        
        $pdf->SetFont('', '', 9);
        $pdf->SetXY(10, $curY + 5);
        $pdf->MultiCell(90, 4, $mysoc->name."\n".$mysoc->address."\n".$mysoc->zip." ".$mysoc->town, 0, 'L');
        $pdf->SetXY(110, $curY + 5);
        $pdf->MultiCell(90, 4, pdf_build_address($outputlangs, $this->emetteur, $object->thirdparty, $object->contact_delivery, 1), 0, 'L');

        $curY = $pdf->GetY() + 5;

        // --- 2. INFOS BL & CODE-BARRES ---
        $pdf->SetFillColor(245, 247, 250);
        $pdf->Rect(10, $curY, 190, 25, 'F');
        
        $pdf->SetXY(15, $curY + 3);
        $pdf->SetFont('', 'B', 10);
        $pdf->Cell(100, 5, "BL N° : ".$object->ref, 0, 1);
        $pdf->SetFont('', '', 9);
        $pdf->SetX(15); $pdf->Cell(100, 5, "Date expédition : ".dol_print_date($object->date_delivery, 'day'), 0, 1);
        $pdf->SetX(15); $pdf->Cell(100, 5, "Commande liée : ".$object->origin_ref, 0, 1);
        
        if ($object->trueWeight > 0) {
            $pdf->SetX(15); $pdf->Cell(100, 5, "Poids Total : ".$object->trueWeight." kg", 0, 1);
        }

        $style = array('position' => '', 'align' => 'C', 'stretch' => false, 'fitwidth' => true, 'border' => false, 'hpadding' => 'auto', 'vpadding' => 'auto', 'fgcolor' => array(0,0,0), 'bgcolor' => false, 'text' => true, 'font' => 'helvetica', 'fontsize' => 8, 'stretchtext' => 4);
        $pdf->write1DBarcode($object->ref, 'C128', 120, $curY + 3, 70, 15, 0.4, $style, 'N');

        $curY += 28;
        if ($object->shipping_method_id > 0) {
            $pdf->SetFont('', 'B', 9); $pdf->SetXY(10, $curY);
            $pdf->Cell(190, 5, "Transporteur : ".$object->shipping_method_label, 0, 1);
            $curY += 6;
        }

        // --- 4. TABLEAU LOGISTIQUE (SANS PRIX) ---
        $col1 = 10; $w1 = 25; // Réf
        $col2 = 35; $w2 = 85; // Désignation
        $col3 = 120;$w3 = 20; // Qté Cmd
        $col4 = 140;$w4 = 20; // Qté Liv
        $col5 = 160;$w5 = 15; // Unité
        $col6 = 175;$w6 = 25; // Conforme

        $pdf->SetFillColor(26, 43, 94); $pdf->SetTextColor(255, 255, 255); $pdf->SetFont('', 'B', 9);
        $pdf->SetXY($col1, $curY);
        $pdf->Cell($w1, 8, "Réf", 1, 0, 'L', 1);
        $pdf->Cell($w2, 8, "Désignation", 1, 0, 'L', 1);
        $pdf->Cell($w3, 8, "Qté Cmd", 1, 0, 'C', 1);
        $pdf->Cell($w4, 8, "Qté Liv", 1, 0, 'C', 1);
        $pdf->Cell($w5, 8, "Unité", 1, 0, 'C', 1);
        $pdf->Cell($w6, 8, "Conforme", 1, 1, 'C', 1);

        $pdf->SetTextColor(0,0,0); $pdf->SetFont('', '', 9);
        $fill = false;
        foreach ($object->lines as $line) {
            $lineHeight = max(8, $pdf->getNumLines($line->description, $w2) * 5);
            if ($pdf->GetY() + $lineHeight > 250) $pdf->AddPage();
            
            $pdf->SetFillColor(245, 247, 250);
            if ($fill) $pdf->Rect($col1, $pdf->GetY(), 190, $lineHeight, 'F');
            
            $pdf->SetX($col1);
            $pdf->MultiCell($w1, $lineHeight, $line->ref, 'L', 'L');
            $pdf->SetXY($col2, $pdf->GetY() - $lineHeight);
            $pdf->MultiCell($w2, $lineHeight, $line->label, 'L', 'L');
            $pdf->SetXY($col3, $pdf->GetY() - $lineHeight);
            $pdf->MultiCell($w3, $lineHeight, $line->qty_asked, 'L', 'C');
            $pdf->SetXY($col4, $pdf->GetY() - $lineHeight);
            $pdf->MultiCell($w4, $lineHeight, $line->qty_shipped, 'L', 'C');
            $pdf->SetXY($col5, $pdf->GetY() - $lineHeight);
            $pdf->MultiCell($w5, $lineHeight, ($line->u_label ? $line->u_label : 'u'), 'L', 'C');
            
            $pdf->SetXY($col6, $pdf->GetY() - $lineHeight);
            $pdf->SetFont('zapfdingbats', '', 12);
            $pdf->Cell($w6, $lineHeight, "o", 'LR', 1, 'C'); 
            $pdf->SetFont('helvetica', '', 9);
            
            $fill = !$fill;
        }
        $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());

        $curY = $pdf->GetY() + 10;
        if ($curY > 240) { $pdf->AddPage(); $curY = 20; }
        
        $pdf->SetXY(10, $curY);
        $pdf->SetFont('', 'B', 9); $pdf->Cell(190, 5, "Observations livreur / Réserves :", 0, 1);
        $pdf->Rect(10, $pdf->GetY(), 190, 20);

        $this->_signatureBlock($pdf, $object, $outputlangs);

        $this->_pagefoot($pdf, $object, $outputlangs);
        $pdf->Output($file, 'F');
        return 1;
    }

    protected function _pagehead(&$pdf, $object, $showaddress, $outputlangs) 
    {
        global $conf, $mysoc;
        $pdf->SetTextColor(26, 43, 94); $pdf->SetFont('', 'B', 14);
        $pdf->SetXY(10, 10); $pdf->MultiCell(100, 6, $mysoc->name, 0, 'L');
        
        $pdf->SetFillColor(26, 43, 94); $pdf->SetTextColor(255, 255, 255);
        $pdf->SetXY(140, 10); $pdf->MultiCell(60, 8, "BON DE LIVRAISON", 0, 'C', 1);
    }

    protected function _pagefoot(&$pdf, $object, $outputlangs)
    {
        $pdf->SetY(-15); $pdf->SetFont('', 'I', 8);
        $pdf->Cell(0, 5, "Document logistique ONR Negoce - Page ".$pdf->getAliasNumPage()."/".$pdf->getAliasNbPages(), 0, 0, 'C');
    }

    protected function _signatureBlock(&$pdf, $object, $outputlangs)
    {
        $pdf->SetY(-50);
        $pdf->SetFont('', 'B', 9);
        $pdf->SetX(10); $pdf->Cell(90, 5, "Visa Client (Nom + Signature)", 0, 0);
        $pdf->SetX(110); $pdf->Cell(90, 5, "Visa Chauffeur", 0, 1);
        $pdf->Rect(10, $pdf->GetY(), 80, 20);
        $pdf->Rect(110, $pdf->GetY(), 80, 20);
    }
}
