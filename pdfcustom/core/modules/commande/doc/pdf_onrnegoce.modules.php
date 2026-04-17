<?php
require_once DOL_DOCUMENT_ROOT.'/core/modules/commande/modules_commande.php';
require_once DOL_DOCUMENT_ROOT.'/custom/pdfcustom/lib/pdf_onr.lib.php';

class pdf_onr_commande extends ModelePDFCommandes
{
    public $name = 'onr_commande';

    function write_file($object, $outputlangs)
    {
        $outputlangs->load("main");
        $outputlangs->load("orders");
        $outputlangs->load("pdfonr@pdfcustom");

        $pdf = onr_init_pdf(array(210,297));

        onr_draw_header($pdf, $object, $outputlangs);
        onr_draw_client($pdf, $object, $outputlangs);
        onr_draw_doc_info($pdf, $object, 'commande', $outputlangs);
        onr_draw_lines($pdf, $object, $outputlangs);
        onr_draw_footer($pdf, $outputlangs);

        return 1;
    }
}