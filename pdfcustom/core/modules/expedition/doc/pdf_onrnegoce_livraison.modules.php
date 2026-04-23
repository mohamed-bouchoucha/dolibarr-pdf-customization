<?php
/* Copyright (C) 2026-2027  Architecte PHP  <votre@email.com> */

require_once DOL_DOCUMENT_ROOT.'/core/modules/expedition/modules_expedition.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/pdf.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';

class pdf_onrnegoce_livraison extends ModelePdfExpedition
{
    public $db;
    public $name = "onrnegoce_livraison";
    public $description;
    public $page_largeur;
    public $page_hauteur;
    public $format;

    public function __construct($db)
    {
        global $langs;
        $this->db = $db;
        $this->description = "Modèle Premium ONR Négoce pour Bons de Livraison (BL)";
        $formatarray = pdf_getFormat();
        $this->page_largeur = $formatarray['width'];
        $this->page_hauteur = $formatarray['height'];
        $this->format = array($this->page_largeur, $this->page_hauteur);
        $langs->loadLangs(array("main", "sendings"));
    }

    public function write_file($object, $outputlangs, $srctemplatepath = '', $hidedetails = 0, $hidedesc = 0, $hideref = 0)
    {
        global $conf, $mysoc;
        if (!is_object($outputlangs)) $outputlangs = $GLOBALS['langs'];

        $objectref = dol_sanitizeFileName($object->ref);
        $dir = $conf->expedition->multidir_output[$object->entity ?? $conf->entity]."/sending/".$objectref;
        if (!file_exists($dir)) dol_mkdir($dir);
        $file = $dir."/".$objectref.".pdf";

        $pdf = pdf_getInstance($this->format);
        $pdf->SetTitle($outputlangs->convToOutputCharset($object->ref));
        $pdf->SetMargins(10, 10, 10);
        $pdf->setAutoPageBreak(true, 25);
        $pdf->AddPage();

        $this->_pagehead($pdf, $object, 1, $outputlangs);

        $curY = $pdf->GetY();
        $pdf->SetXY(110, $curY);
        $pdf->SetFont('', 'B', 9); $pdf->Cell(90, 5, "Destinataire / Livraison :", 0, 1);
        $pdf->SetXY(110, $pdf->GetY()); $pdf->SetFont('', '', 9);
        $pdf->MultiCell(90, 4, pdf_build_address($outputlangs, $this->emetteur, $object->thirdparty, $object->contact_delivery, 1), 0, 'L');
        
        $curY = max($curY + 30, $pdf->GetY() + 5);

        // --- CODE-BARRES ---
        $pdf->SetFillColor(245, 247, 250); $pdf->Rect(10, $curY, 190, 20, 'F');
        $pdf->SetXY(15, $curY + 3); $pdf->SetFont('', 'B', 10); $pdf->Cell(100, 5, "BL N° : ".$object->ref, 0, 1);
        $pdf->SetFont('', '', 9); $pdf->SetX(15); $pdf->Cell(100, 5, "Date : ".dol_print_date($object->date_delivery, 'day'), 0, 1);
        
        $style = array('position' => '', 'align' => 'C', 'stretch' => false, 'fitwidth' => true, 'fgcolor' => array(0,0,0), 'bgcolor' => false, 'text' => true, 'font' => 'helvetica', 'fontsize' => 8);
        $pdf->write1DBarcode($object->ref, 'C128', 125, $curY + 2, 70, 15, 0.4, $style, 'N');
        $curY += 25;

        $col1 = 10; $w1 = 25; $col2 = 35; $w2 = 85; $col3 = 120;$w3 = 20; $col4 = 140;$w4 = 20; $col5 = 160;$w5 = 15; $col6 = 175;$w6 = 25;
        $pdf->SetFillColor(26, 43, 94); $pdf->SetTextColor(255, 255, 255); $pdf->SetFont('', 'B', 9);
        $pdf->SetXY($col1, $curY);
        $pdf->Cell($w1, 8, "Réf", 1, 0, 'L', 1); $pdf->Cell($w2, 8, "Désignation", 1, 0, 'L', 1);
        $pdf->Cell($w3, 8, "Cmd", 1, 0, 'C', 1); $pdf->Cell($w4, 8, "Livré", 1, 0, 'C', 1);
        $pdf->Cell($w5, 8, "Unité", 1, 0, 'C', 1); $pdf->Cell($w6, 8, "OK", 1, 1, 'C', 1);

        $pdf->SetTextColor(0,0,0); $pdf->SetFont('', '', 9);
        $fill = false;
        foreach ($object->lines as $line) {
            $nbLines = $pdf->getNumLines($line->label, $w2);
            $lineHeight = max(8, $nbLines * 5);
            if ($pdf->GetY() + $lineHeight > 250) $pdf->AddPage();
            $startY = $pdf->GetY();
            if ($fill) { $pdf->SetFillColor(245, 247, 250); $pdf->Rect($col1, $startY, 190, $lineHeight, 'F'); }
            $pdf->SetXY($col1, $startY); $pdf->MultiCell($w1, $lineHeight, $line->ref, 'L', 'L');
            $pdf->SetXY($col2, $startY); $pdf->MultiCell($w2, $lineHeight, $line->label, 'L', 'L');
            $pdf->SetXY($col3, $startY); $pdf->MultiCell($w3, $lineHeight, $line->qty_asked, 'L', 'C');
            $pdf->SetXY($col4, $startY); $pdf->MultiCell($w4, $lineHeight, $line->qty_shipped, 'L', 'C');
            $pdf->SetXY($col5, $startY); $pdf->MultiCell($w5, $lineHeight, ($line->u_label ?: 'u'), 'L', 'C');
            $pdf->SetXY($col6, $startY); $pdf->SetFont('zapfdingbats', '', 12); $pdf->Cell($w6, $lineHeight, "o", 'LR', 1, 'C');
            $pdf->SetFont('helvetica', '', 9);
            $pdf->SetY($startY + $lineHeight);
            $fill = !$fill;
        }
        $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());

        $this->_signatureBlock($pdf, $object, $outputlangs);
        $this->_pagefoot($pdf, $object, $outputlangs);
        $pdf->Output($file, 'F');
        return 1;
    }

    protected function _pagehead(&$pdf, $object, $showaddress, $outputlangs) 
    {
        global $conf, $mysoc;
        $page_width = $this->page_largeur; $margins = $pdf->getMargins(); $posy = $margins['top'];
        $logo = $conf->mycompany->dir_output . '/logos/' . $mysoc->logo;
        if (!empty($mysoc->logo) && is_readable($logo)) {
            $height = pdf_getHeightForLogo($logo);
            $pdf->Image($logo, 10, $posy, 0, $height);
            $posy = max($posy, $posy + $height + 5);
        }
        $pdf->SetTextColor(26, 43, 94); $pdf->SetFont('', 'B', 14);
        $pdf->SetXY(10, $posy); $pdf->MultiCell(100, 6, $mysoc->name, 0, 'L');
        $pdf->SetFillColor(26, 43, 94); $pdf->SetTextColor(255, 255, 255); $pdf->SetFont('', 'B', 12);
        $pdf->SetXY($page_width - 10 - 70, $margins['top']);
        $pdf->MultiCell(70, 8, "LIVRAISON (BL)", 0, 'C', 1);
        $pdf->SetDrawColor(26, 43, 94); $pdf->SetLineWidth(0.5);
        $pdf->Line(10, $pdf->GetY() + 5, $page_width - 10, $pdf->GetY() + 5);
        $pdf->SetY($pdf->GetY() + 10);
    }

    protected function _signatureBlock(&$pdf, $object, $outputlangs)
    {
        $curY = $pdf->GetY() + 10; if ($curY > 220) { $pdf->AddPage(); $curY = 20; }
        $pdf->SetXY(10, $curY); $pdf->SetFont('', 'B', 9);
        $pdf->Cell(190, 5, "Observations / Réserves :", 0, 1);
        $pdf->Rect(10, $pdf->GetY(), 190, 15);
        $pdf->SetY($pdf->GetY() + 18);
        $pdf->Cell(95, 5, "Visa Client", 0, 0, 'L');
        $pdf->Cell(95, 5, "Visa Chauffeur", 0, 1, 'L');
        $pdf->Rect(10, $pdf->GetY(), 85, 20); $pdf->Rect(110, $pdf->GetY(), 85, 20);
    }

    protected function _pagefoot(&$pdf, $object, $outputlangs)
    {
        global $mysoc;
        $pdf->SetY(-15); $pdf->SetTextColor(136, 136, 136); $pdf->SetFont('', '', 7);
        $pdf->MultiCell(0, 4, $mysoc->name." - Bon de Livraison - Ne peut servir de facture", 0, 'C');
        $pdf->SetTextColor(26, 43, 94); $pdf->SetFont('', 'B', 8);
        $pdf->MultiCell(0, 4, "Page ".$pdf->getAliasNumPage()." / ".$pdf->getAliasNbPages(), 0, 'C');
    }
}
