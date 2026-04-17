<?php
require_once DOL_DOCUMENT_ROOT.'/core/modules/facture/modules_facture.php';
require_once DOL_DOCUMENT_ROOT.'/custom/pdfcustom/lib/pdf_onr.lib.php';

class pdf_onr_facture extends ModelePDFFactures
{
    public $name = 'onr_facture';

    function write_file($object, $outputlangs)
    {
        global $conf;

        $pdf = onr_init_pdf(array(210,297));

        onr_draw_header($pdf, $object);
        onr_draw_client($pdf, $object);
        onr_draw_doc_info($pdf, $object, 'facture');
        onr_draw_lines($pdf, $object);
        onr_draw_totals($pdf, $object);
        onr_draw_footer($pdf);

        $file = $conf->facture->dir_output.'/'.$object->ref.'.pdf';
        $pdf->Output($file, 'F');

        return 1;
    }
}