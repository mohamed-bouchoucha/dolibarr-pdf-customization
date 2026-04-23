<?php
/* Copyright (C) 2026-2027  Architecte PHP  <votre@email.com> */

require_once DOL_DOCUMENT_ROOT.'/core/modules/expedition/modules_expedition.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/pdf.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';

class pdf_onrnegoce_expedition extends ModelePdfExpedition
{
    public $db;
    public $name = "onrnegoce_expedition";
    public $description;
    public $page_largeur;
    public $page_hauteur;
    public $format;

    public function __construct($db)
    {
        global $langs;
        $this->db = $db;
        $this->description = "Modèle Premium ONR Négoce pour Expéditions";
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
        $pdf->SetFont('', 'B', 9); $pdf->Cell(90, 5, "Lieu de Livraison :", 0, 1);
        $pdf->SetXY(110, $pdf->GetY()); $pdf->SetFont('', '', 9);
        $pdf->MultiCell(90, 4, pdf_build_address($outputlangs, $this->emetteur, $object->thirdparty, $object->contact_delivery, 1), 0, 'L');
        
        $curY = max($curY + 35, $pdf->GetY() + 10);

        $col1 = 10; $w1 = 30; $col2 = 40; $w2 = 100; $col3 = 140;$w3 = 30; $col4 = 170;$w4 = 30; 
        $pdf->SetFillColor(26, 43, 94); $pdf->SetTextColor(255, 255, 255); $pdf->SetFont('', 'B', 9);
        $pdf->SetXY($col1, $curY);
        $pdf->Cell($w1, 8, "Réf", 1, 0, 'L', 1); $pdf->Cell($w2, 8, "Désignation", 1, 0, 'L', 1);
        $pdf->Cell($w3, 8, "Qté Cmd", 1, 0, 'C', 1); $pdf->Cell($w4, 8, "Qté Livrée", 1, 1, 'C', 1);

        $pdf->SetTextColor(0,0,0); $pdf->SetFont('', '', 9);
        $fill = false;
        foreach ($object->lines as $line) {
            $nbLines = $pdf->getNumLines($line->label, $w2);
            $lineHeight = max(7, $nbLines * 5);
            if ($pdf->GetY() + $lineHeight > 245) $pdf->AddPage();
            
            $startY = $pdf->GetY();
            if ($fill) { $pdf->SetFillColor(245, 247, 250); $pdf->Rect($col1, $startY, 190, $lineHeight, 'F'); }
            
            $pdf->SetXY($col1, $startY); $pdf->MultiCell($w1, $lineHeight, $line->ref, 'L', 'L');
            $pdf->SetXY($col2, $startY); $pdf->MultiCell($w2, $lineHeight, $line->label, 'L', 'L');
            $pdf->SetXY($col3, $startY); $pdf->MultiCell($w3, $lineHeight, $line->qty_asked, 'L', 'C');
            $pdf->SetXY($col4, $startY); $pdf->MultiCell($w4, $lineHeight, $line->qty_shipped, 'LR', 'C');
            $pdf->SetY($startY + $lineHeight);
            $fill = !$fill;
        }
        $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());

        $this->_extrafieldsBlock($pdf, $object, $outputlangs);
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
        $pdf->SetTextColor(26, 43, 94); $pdf->SetFont('', 'B', 12);
        $pdf->SetXY(10, $posy); $pdf->MultiCell(100, 5, $mysoc->name, 0, 'L');
        $pdf->SetTextColor(0, 0, 0); $pdf->SetFont('', '', 9);
        $pdf->MultiCell(100, 4, $mysoc->address."\n".$mysoc->zip." ".$mysoc->town, 0, 'L');
        $pdf->SetFillColor(26, 43, 94); $pdf->SetTextColor(255, 255, 255); $pdf->SetFont('', 'B', 12);
        $pdf->SetXY($page_width - 10 - 70, $margins['top']);
        $pdf->MultiCell(70, 8, "EXPEDITION ".$object->ref, 0, 'C', 1);
        $pdf->SetDrawColor(26, 43, 94); $pdf->SetLineWidth(0.5);
        $pdf->Line(10, $pdf->GetY() + 5, $page_width - 10, $pdf->GetY() + 5);
        $pdf->SetY($pdf->GetY() + 10);
    }

    protected function _extrafieldsBlock(&$pdf, $object, $outputlangs)
    {
        $extrafields = new ExtraFields($this->db);
        $extralabels = $extrafields->fetch_name_optionals_label($object->table_element);
        $object->fetch_optionals();
        $val_mode_liv = $object->array_options['options_mode_livraison'] ?? '';
        if (!empty($val_mode_liv)) {
            $curY = $pdf->GetY() + 5; if ($curY > 230) { $pdf->AddPage(); $curY = 20; }
            $pdf->SetFont('', 'B', 9); $pdf->SetXY(10, $curY); $pdf->SetFillColor(245, 247, 250);
            $pdf->Cell(190, 7, "Informations logistiques", 'B', 1, 'L', 1);
            $pdf->SetFont('', '', 9); $pdf->SetX(10); $pdf->SetFont('', 'B'); $pdf->Cell(40, 5, "Transporteur :"); $pdf->SetFont('', ''); $pdf->Cell(50, 5, $extrafields->showOutputField('mode_livraison', $val_mode_liv), 0, 1);
        }
    }

    protected function _signatureBlock(&$pdf, $object, $outputlangs)
    {
        $curY = $pdf->GetY() + 10; if ($curY > 220) { $pdf->AddPage(); $curY = 20; }
        $pdf->SetXY(10, $curY); $pdf->SetFont('', 'B', 9); $pdf->Cell(90, 5, "Visa Entrepôt", 0, 0);
        $pdf->Cell(90, 5, "Visa Client", 0, 1);
        $pdf->Rect(10, $pdf->GetY(), 80, 20); $pdf->Rect(110, $pdf->GetY(), 80, 20);
    }

    protected function _pagefoot(&$pdf, $object, $outputlangs)
    {
        global $mysoc;
        $pdf->SetY(-25); $pdf->SetDrawColor(26, 43, 94); $pdf->SetLineWidth(0.5);
        $pdf->Line(10, $pdf->GetY(), $this->page_largeur - 10, $pdf->GetY());
        $pdf->SetTextColor(136, 136, 136); $pdf->SetFont('', '', 7);
        $pdf->MultiCell(0, 4, $mysoc->name." - CP : ".$mysoc->zip." ".$mysoc->town, 0, 'C');
        $pdf->SetTextColor(26, 43, 94); $pdf->SetFont('', 'B', 8);
        $pdf->MultiCell(0, 4, "Page ".$pdf->getAliasNumPage()." / ".$pdf->getAliasNbPages(), 0, 'C');
    }
}
