<?php

function onr_init_pdf($format)
{
    $pdf = pdf_getInstance($format);
    $pdf->SetAutoPageBreak(true, 10);
    $pdf->AddPage();
    return $pdf;
}

/**
 * HEADER GLOBAL
 */
function onr_draw_header($pdf, $object)
{
    $pdf->SetFont('helvetica', 'B', 12);

    // Logo
    $pdf->Image(DOL_DOCUMENT_ROOT.'/custom/logo.png', 10, 10, 40);

    // Nom entreprise
    $pdf->SetXY(120, 10);
    $pdf->Cell(80, 5, "ONR Négoce", 0, 1, 'R');

    // Ligne séparation
    $pdf->Line(10, 30, 200, 30);
}

/**
 * CLIENT
 */
function onr_draw_client($pdf, $object)
{
    $pdf->SetXY(10, 40);

    $client = $object->thirdparty->name."\n".$object->thirdparty->address;

    $pdf->MultiCell(100, 5, "Client :\n".$client);
}

/**
 * INFOS DOCUMENT
 */
function onr_draw_doc_info($pdf, $object, $type)
{
    $pdf->SetXY(120, 40);

    $text = "Ref : ".$object->ref."\n";

    if ($type === 'facture') {
        $text .= "Date : ".dol_print_date($object->date, 'day');
    }

    if ($type === 'propale') {
        $text .= "Validité : ".dol_print_date($object->fin_validite, 'day');
    }

    if ($type === 'commande') {
        $text .= "Statut : ".$object->getLibStatut(1);
    }

    $pdf->MultiCell(80, 5, $text);
}

/**
 * LIGNES PRODUITS
 */
function onr_draw_lines($pdf, $object)
{
    $pdf->SetXY(10, 80);

    // Header table
    $pdf->Cell(80, 6, "Produit", 1);
    $pdf->Cell(30, 6, "Qté", 1);
    $pdf->Cell(40, 6, "PU", 1);
    $pdf->Cell(40, 6, "Total", 1, 1);

    foreach ($object->lines as $line) {
        $pdf->Cell(80, 6, $line->desc, 1);
        $pdf->Cell(30, 6, $line->qty, 1);
        $pdf->Cell(40, 6, price($line->subprice), 1);
        $pdf->Cell(40, 6, price($line->total_ht), 1, 1);
    }
}

/**
 * TOTAUX (facture seulement)
 */
function onr_draw_totals($pdf, $object)
{
    $pdf->Ln(10);

    $pdf->SetX(100);
    $pdf->Cell(50, 6, "Total HT", 1);
    $pdf->Cell(40, 6, price($object->total_ht), 1, 1);

    $pdf->SetX(100);
    $pdf->Cell(50, 6, "TVA", 1);
    $pdf->Cell(40, 6, price($object->total_tva), 1, 1);

    $pdf->SetX(100);
    $pdf->Cell(50, 6, "Total TTC", 1);
    $pdf->Cell(40, 6, price($object->total_ttc), 1, 1);
}

/**
 * FOOTER GLOBAL
 */
function onr_draw_footer($pdf)
{
    $pdf->SetY(-15);
    $pdf->SetFont('helvetica', 'I', 8);
    $pdf->Cell(0, 10, "Page ".$pdf->getAliasNumPage()."/".$pdf->getAliasNbPages(), 0, 0, 'C');
}