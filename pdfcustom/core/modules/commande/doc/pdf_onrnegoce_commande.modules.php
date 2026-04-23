<?php
/* Copyright (C) 2026-2027  Architecte PHP  <votre@email.com> */

/**
 *  \file       custom/pdfcustom/core/modules/commande/doc/pdf_onrnegoce_commande.modules.php
 *  \ingroup    commande
 *  \brief      Modèle de commande personnalisé ONR Negoce
 */

require_once DOL_DOCUMENT_ROOT.'/core/modules/commande/modules_commande.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/pdf.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';

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

        // --- DÉBUT DE LA SECTION TABLEAU DES LIGNES ---
        $curY = 120; 
        $col1 = 10;  $w1 = 30;  // Réf
        $col2 = 40;  $w2 = 70;  // Désignation
        $col3 = 110; $w3 = 15;  // Qté
        $col4 = 125; $w4 = 25;  // P.U. HT
        $col5 = 150; $w5 = 15;  // Remise %
        $col6 = 165; $w6 = 35;  // Total HT
        
        // 1. EN-TÊTE DU TABLEAU (#1A2B5E)
        $pdf->SetFillColor(26, 43, 94);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetDrawColor(26, 43, 94);
        $pdf->SetFont('', 'B', 9);
        
        $pdf->SetXY($col1, $curY);
        $pdf->Cell($w1, 8, "Réf", 1, 0, 'L', 1);
        $pdf->Cell($w2, 8, "Désignation", 1, 0, 'L', 1);
        $pdf->Cell($w3, 8, "Qté", 1, 0, 'C', 1);
        $pdf->Cell($w4, 8, "P.U. HT", 1, 0, 'R', 1);
        $pdf->Cell($w5, 8, "Rem. %", 1, 0, 'C', 1);
        $pdf->Cell($w6, 8, "Total HT", 1, 1, 'R', 1);
        
        $curY = $pdf->GetY();
        $pdf->SetTextColor(0, 0, 0);
        $fill = false; 
        
        foreach ($object->lines as $line) {
            if ($curY > 250) { $pdf->AddPage(); $curY = 20; if ($this->_pagehead($pdf, $object, 1, $outputlangs)) $curY = $pdf->GetY() + 5; }

            $pdf->SetFillColor(245, 247, 250);
            $tmpDesignation = !empty($line->description) ? $line->label."\n".$line->description : $line->label;
            $nbLines = $pdf->getNumLines($tmpDesignation, $w2);
            $lineHeight = max(7, $nbLines * 5); 

            if ($line->product_type == 9) {
                $pdf->SetFont('', 'I', 9);
                $pdf->SetTextColor(100, 100, 100);
                $pdf->SetXY($col1, $curY);
                $pdf->MultiCell($w1 + $w2 + $w3 + $w4 + $w5 + $w6, $lineHeight, $line->label, 'LRB', 'L', $fill);
            } 
            else {
                $pdf->SetFont('', ($line->product_type == 1 ? 'I' : ''), 9); 
                if ($fill) $pdf->Rect($col1, $curY, 190, $lineHeight, 'F');

                $pdf->SetXY($col1, $curY); $pdf->MultiCell($w1, $lineHeight, $line->ref, 'LR', 'L');
                $pdf->SetXY($col2, $curY); $pdf->MultiCell($w2, $lineHeight, $tmpDesignation, 'R', 'L');
                $pdf->SetXY($col3, $curY); $pdf->MultiCell($w3, $lineHeight, $line->qty, 'R', 'C');
                $pdf->SetXY($col4, $curY); $pdf->MultiCell($w4, $lineHeight, price($line->subprice), 'R', 'R');
                $pdf->SetXY($col5, $curY); 
                if ($line->remise_percent > 0) $pdf->SetTextColor(255, 0, 0);
                $pdf->MultiCell($w5, $lineHeight, ($line->remise_percent > 0 ? $line->remise_percent.'%' : ''), 'R', 'C');
                $pdf->SetTextColor(0, 0, 0); 
                $pdf->SetXY($col6, $curY); $pdf->SetFont('', 'B', 9); $pdf->MultiCell($w6, $lineHeight, price($line->total_ht), 'R', 'R');
            }
            $curY += $lineHeight;
            $fill = !$fill;
            $pdf->SetFont('', '', 9);
        }
        $pdf->Line($col1, $curY, $col1 + 190, $curY);

        $curY += 10;
        if ($curY > 230) { $pdf->AddPage(); $curY = 20; }
        $wTotalLabel = 40; $wTotalVal = 35; $rightPos = $col1 + 190 - $wTotalLabel - $wTotalVal;

        $pdf->SetFont('', 'B', 10);
        $pdf->SetXY($rightPos, $curY); $pdf->SetFillColor(245, 247, 250); $pdf->Cell($wTotalLabel, 8, "Total HT", 0, 0, 'L', 1);
        $pdf->Cell($wTotalVal, 8, price($object->total_ht).' '. $outputlangs->trans("Currency".$conf->currency), 0, 1, 'R', 1);
        
        $curY += 8; $pdf->SetXY($rightPos, $curY); $pdf->Cell($wTotalLabel, 8, "TVA", 0, 0, 'L', 0);
        $pdf->Cell($wTotalVal, 8, price($object->total_tva).' '. $outputlangs->trans("Currency".$conf->currency), 0, 1, 'R', 0);

        $curY += 10; $pdf->SetXY($rightPos, $curY); $pdf->SetFillColor(26, 43, 94); $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell($wTotalLabel, 10, "TOTAL TTC", 0, 0, 'L', 1); $pdf->SetFont('', 'B', 12);
        $pdf->Cell($wTotalVal, 10, price($object->total_ttc).' '. $outputlangs->trans("Currency".$conf->currency), 0, 1, 'R', 1);
        $pdf->SetTextColor(0, 0, 0);
        // --- FIN DE LA SECTION TABLEAU ---

        // --- BLOC INFORMATIONS COMPLÉMENTAIRES (EXTRAFIELDS) ---
        $extrafields = new ExtraFields($this->db);
        $extralabels = $extrafields->fetch_name_optionals_label($object->table_element);
        $object->fetch_optionals();

        $val_ref_cmd = $object->array_options['options_ref_commande_client'] ?? '';
        $val_mode_liv = $object->array_options['options_mode_livraison'] ?? '';
        $val_check_val = !empty($object->array_options['options_validation_client']) ? $outputlangs->trans("Yes") : '';

        if (!empty($val_ref_cmd) || !empty($val_mode_liv) || !empty($val_check_val)) {
            $curY = $pdf->GetY() + 10;
            if ($curY > 230) { $pdf->AddPage(); $curY = 20; }

            $pdf->SetFont('', 'B', 10); $pdf->SetXY(10, $curY); $pdf->SetFillColor(245, 247, 250);
            $pdf->Cell(190, 8, "Informations complémentaires", 'B', 1, 'L', 1);
            $curY += 10;

            $pdf->SetFont('', '', 9);
            if (!empty($val_ref_cmd)) {
                $pdf->SetXY(10, $curY);
                $pdf->SetFont('', 'B', 9); $pdf->Cell(45, 5, $extralabels['ref_commande_client'] . " :", 0, 0, 'L');
                $pdf->SetFont('', '', 9); $pdf->Cell(50, 5, $val_ref_cmd, 0, 0, 'L');
            }
            if (!empty($val_check_val)) {
                $pdf->SetXY(10, $curY + 6);
                $pdf->SetFont('', 'B', 9); $pdf->Cell(45, 5, $extralabels['validation_client'] . " :", 0, 0, 'L');
                $pdf->SetFont('', '', 9); $pdf->Cell(50, 5, $val_check_val, 0, 0, 'L');
            }
            if (!empty($val_mode_liv)) {
                $pdf->SetXY(105, $curY);
                $pdf->SetFont('', 'B', 9); $pdf->Cell(45, 5, $extralabels['mode_livraison'] . " :", 0, 0, 'L');
                $pdf->SetFont('', '', 9); $pdf->Cell(50, 5, $extrafields->showOutputField('mode_livraison', $val_mode_liv, '', $object->table_element), 0, 0, 'L');
            }
        }

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
        $label = $outputlangs->transnoentities("Order");
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
