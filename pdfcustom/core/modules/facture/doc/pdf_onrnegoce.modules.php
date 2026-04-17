<?php
require_once DOL_DOCUMENT_ROOT.'/core/modules/facture/modules_facture.php';
require_once DOL_DOCUMENT_ROOT.'/custom/pdfcustom/lib/pdf_onr.lib.php';

class pdf_onr_facture extends ModelePDFFactures
{
    public $name = 'onr_facture';

    function write_file($object, $outputlangs)
    {
        global $conf;

        $outputlangs->load("main");
        $outputlangs->load("bills");
        $outputlangs->load("pdfonr@pdfcustom");

        $pdf = onr_init_pdf(array(210,297));

        onr_draw_header($pdf, $object, $outputlangs);
        onr_draw_client($pdf, $object, $outputlangs);
        onr_draw_doc_info($pdf, $object, 'facture', $outputlangs);
        onr_draw_lines($pdf, $object, $outputlangs);
        onr_draw_totals($pdf, $object, $outputlangs);
        onr_draw_footer($pdf, $outputlangs);

        $file = $conf->facture->dir_output.'/'.$object->ref.'.pdf';
        $pdf->Output($file, 'F');

        return 1;
    }
}