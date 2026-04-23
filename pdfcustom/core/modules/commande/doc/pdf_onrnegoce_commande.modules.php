<?php
/* Copyright (C) 2026-2027  Architecte PHP  <votre@email.com> */

require_once DOL_DOCUMENT_ROOT.'/core/modules/commande/modules_commande.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/pdf.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';

class pdf_onrnegoce_commande extends ModelePDFCommandes
{
    public $db;
    public $name = "onrnegoce_commande";
    public $description;
    public $page_largeur;
    public $page_hauteur;
    public $format;

    public function __construct($db)
    {
        global $langs;
        $this->db = $db;
        $this->description = "Modèle Premium ONR Négoce pour Commandes";
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
        $pdf->setAutoPageBreak(true, 30);
        $pdf->AddPage();

        $this->_pagehead($pdf, $object, 1, $outputlangs);

        $curY = $pdf->GetY();
        $pdf->SetXY(110, $curY);
        $pdf->SetFont('', 'B', 9); $pdf->Cell(90, 5, "Destinataire :", 0, 1);
        $pdf->SetXY(110, $pdf->GetY()); $pdf->SetFont('', '', 9);
        $pdf->MultiCell(90, 4, pdf_build_address($outputlangs, $this->emetteur, $object->thirdparty, $object->contact_delivery, 1), 0, 'L');
        
        $curY = max($curY + 35, $pdf->GetY() + 10);

        $col1 = 10; $w1 = 25; $col2 = 35; $w2 = 75; $col3 = 110;$w3 = 15; $col4 = 125;$w4 = 25; $col5 = 150;$w5 = 15; $col6 = 165;$w6 = 35;
        $pdf->SetFillColor(26, 43, 94); $pdf->SetTextColor(255, 255, 255); $pdf->SetFont('', 'B', 9);
        $pdf->SetXY($col1, $curY);
        $pdf->Cell($w1, 8, "Réf", 1, 0, 'L', 1); $pdf->Cell($w2, 8, "Désignation", 1, 0, 'L', 1);
        $pdf->Cell($w3, 8, "Qté", 1, 0, 'C', 1); $pdf->Cell($w4, 8, "P.U. HT", 1, 0, 'R', 1);
        $pdf->Cell($w5, 8, "Rem.%", 1, 0, 'C', 1); $pdf->Cell($w6, 8, "Total HT", 1, 1, 'R', 1);

        $pdf->SetTextColor(0,0,0); $pdf->SetFont('', '', 9);
        $fill = false;
        if (!empty($object->lines)) {
            foreach ($object->lines as $line) {
            $nbLines = $pdf->getNumLines($line->label . "\n" . $line->description, $w2);
            $lineHeight = max(7, $nbLines * 5);
            if ($pdf->GetY() + $lineHeight > 245) $pdf->AddPage();
            
            $startY = $pdf->GetY();
            if ($fill) { $pdf->SetFillColor(245, 247, 250); $pdf->Rect($col1, $startY, 190, $lineHeight, 'F'); }
            
            $pdf->SetXY($col1, $startY); $pdf->MultiCell($w1, $lineHeight, $line->ref, 'L', 'L');
            $pdf->SetXY($col2, $startY); $pdf->SetFont('', ($line->product_type==1?'I':''), 9); $pdf->MultiCell($w2, $lineHeight, $line->label, 'L', 'L');
            $pdf->SetXY($col3, $startY); $pdf->SetFont('', '', 9); $pdf->MultiCell($w3, $lineHeight, $line->qty, 'L', 'C');
            $pdf->SetXY($col4, $startY); $pdf->MultiCell($w4, $lineHeight, price($line->subprice), 'L', 'R');
            $pdf->SetXY($col5, $startY); $pdf->SetTextColor($line->remise_percent>0?255:0, 0, 0); $pdf->MultiCell($w5, $lineHeight, ($line->remise_percent>0?$line->remise_percent.'%':''), 'L', 'C');
            $pdf->SetTextColor(0,0,0); $pdf->SetXY($col6, $startY); $pdf->SetFont('', 'B', 9); $pdf->MultiCell($w6, $lineHeight, price($line->total_ht), 'LR', 'R');
            $pdf->SetY($startY + $lineHeight);
            $fill = !$fill;
        }
        }
        $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());

        $curY = $pdf->GetY() + 5;
        if ($curY > 210) { $pdf->AddPage(); $curY = 20; }
        $wTotalLabel = 40; $wTotalVal = 35; $rightPos = 200 - $wTotalLabel - $wTotalVal;

        $pdf->SetXY($rightPos, $curY); $pdf->SetFillColor(245, 247, 250); $pdf->SetFont('', 'B', 10);
        $pdf->Cell($wTotalLabel, 8, "Total HT", 0, 0, 'L', 1); $pdf->Cell($wTotalVal, 8, price($object->total_ht).' '.$outputlangs->trans("Currency".$conf->currency), 0, 1, 'R', 1);
        $pdf->SetXY($rightPos, $pdf->GetY());
        $pdf->Cell($wTotalLabel, 8, "TVA", 0, 0, 'L', 0); $pdf->Cell($wTotalVal, 8, price($object->total_tva).' '.$outputlangs->trans("Currency".$conf->currency), 0, 1, 'R', 0);
        $pdf->SetXY($rightPos, $pdf->GetY() + 2); $pdf->SetFillColor(26, 43, 94); $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell($wTotalLabel, 10, "TOTAL TTC", 0, 0, 'L', 1); $pdf->SetFont('', 'B', 12);
        $pdf->Cell($wTotalVal, 10, price($object->total_ttc).' '.$outputlangs->trans("Currency".$conf->currency), 0, 1, 'R', 1);
        $pdf->SetTextColor(0,0,0);

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
        $pdf->MultiCell(70, 8, "COMMANDE ".$object->ref, 0, 'C', 1);
        $pdf->SetDrawColor(26, 43, 94); $pdf->SetLineWidth(0.5);
        $pdf->Line(10, $pdf->GetY() + 5, $page_width - 10, $pdf->GetY() + 5);
        $pdf->SetY($pdf->GetY() + 10);
    }

    protected function _extrafieldsBlock(&$pdf, $object, $outputlangs)
    {
        $extrafields = new ExtraFields($this->db);
        $extralabels = $extrafields->fetch_name_optionals_label($object->table_element);
        $object->fetch_optionals();
        $val_ref_cmd = $object->array_options['options_ref_commande_client'] ?? '';
        if (!empty($val_ref_cmd)) {
            $curY = $pdf->GetY() + 5; if ($curY > 230) { $pdf->AddPage(); $curY = 20; }
            $pdf->SetFont('', 'B', 9); $pdf->SetXY(10, $curY); $pdf->SetFillColor(245, 247, 250);
            $pdf->Cell(190, 7, "Informations complémentaires", 'B', 1, 'L', 1);
            $pdf->SetFont('', '', 9); $pdf->SetX(10); $pdf->SetFont('', 'B'); $pdf->Cell(40, 5, "Réf Client :"); $pdf->SetFont('', ''); $pdf->Cell(50, 5, $val_ref_cmd, 0, 1);
        }
    }

    protected function _signatureBlock(&$pdf, $object, $outputlangs)
    {
        $show_signature = !empty($object->array_options['options_show_signature']);
        if ($show_signature != 1) return;
        $curY = $pdf->GetY() + 10; if ($curY > 220) { $pdf->AddPage(); $curY = 20; }
        $pdf->SetXY(10, $curY); $pdf->SetFont('', 'B', 9); $pdf->Cell(90, 5, "Bon pour accord - Signature client", 0, 0);
        $pdf->Cell(90, 5, "Signature et cachet entreprise", 0, 1);
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
