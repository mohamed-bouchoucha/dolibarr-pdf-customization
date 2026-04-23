<?php

function onr_init_pdf($format)
{
    $pdf = pdf_getInstance($format);
    $pdf->SetAutoPageBreak(true, 10);
    $pdf->AddPage();
    return $pdf;
}

/**
 * HEADER
 */
function onr_draw_header($pdf, $object, $outputlangs)
{
    global $conf;

    // Logo
    $logo = $conf->mycompany->dir_output.'/logos/mylogo.png';
    if (file_exists($logo)) {
        $pdf->Image($logo, 10, 10, 40);
    }

    // Nom entreprise (traduit)
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetXY(120, 10);
    $pdf->Cell(80, 5, $outputlangs->trans("ONRCompany"), 0, 1, 'R');

    $pdf->Line(10, 30, 200, 30);
}

/**
 * CLIENT
 */
function onr_draw_client($pdf, $object, $outputlangs)
{
    $pdf->SetXY(10, 40);

    $client = $object->thirdparty->name."\n".$object->thirdparty->address;

    $pdf->MultiCell(100, 5, $outputlangs->trans("Customer")." :\n".$client);
}

/**
 * INFOS DOCUMENT
 */
function onr_draw_doc_info($pdf, $object, $type, $outputlangs)
{
    $pdf->SetXY(120, 40);

    $text = $outputlangs->trans("Ref")." : ".$object->ref."\n";

    if ($type === 'facture') {
        $text .= $outputlangs->trans("Date")." : ".dol_print_date($object->date, 'day');
    }

    if ($type === 'propale') {
        $text .= $outputlangs->trans("ValidityDate")." : ".dol_print_date($object->fin_validite, 'day');
    }

    if ($type === 'commande') {
        $text .= $outputlangs->trans("Status")." : ".$object->getLibStatut(1);
    }

    $pdf->MultiCell(80, 5, $text);
}

/**
 * LIGNES PRODUITS
 */
function onr_draw_lines($pdf, $object, $outputlangs)
{
    $pdf->SetXY(10, 80);

    // Header tableau
    $pdf->Cell(80, 6, $outputlangs->trans("Product"), 1);
    $pdf->Cell(30, 6, $outputlangs->trans("Qty"), 1);
    $pdf->Cell(40, 6, $outputlangs->trans("Price"), 1);
    $pdf->Cell(40, 6, $outputlangs->trans("Total"), 1, 1);

    foreach ($object->lines as $line) {
        $pdf->Cell(80, 6, $line->desc, 1);
        $pdf->Cell(30, 6, $line->qty, 1);
        $pdf->Cell(40, 6, price($line->subprice), 1);
        $pdf->Cell(40, 6, price($line->total_ht), 1, 1);
    }
}

/**
 * TOTAUX (FACTURE)
 */
function onr_draw_totals($pdf, $object, $outputlangs)
{
    $pdf->Ln(10);

    $pdf->SetX(100);
    $pdf->Cell(50, 6, $outputlangs->trans("TotalHT"), 1);
    $pdf->Cell(40, 6, price($object->total_ht), 1, 1);

    $pdf->SetX(100);
    $pdf->Cell(50, 6, $outputlangs->trans("VAT"), 1);
    $pdf->Cell(40, 6, price($object->total_tva), 1, 1);

    $pdf->SetX(100);
    $pdf->Cell(50, 6, $outputlangs->trans("TotalTTC"), 1);
    $pdf->Cell(40, 6, price($object->total_ttc), 1, 1);
}

/**
 * FOOTER
 */
function onr_draw_footer($pdf, $outputlangs)
{
    $pdf->SetY(-15);
    $pdf->SetFont('helvetica', 'I', 8);

    $pdf->Cell(
        0,
        10,
        $outputlangs->trans("Page")." ".$pdf->getAliasNumPage()."/".$pdf->getAliasNbPages(),
        0,
        0,
        'C'
    );
}