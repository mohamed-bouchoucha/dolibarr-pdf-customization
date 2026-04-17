<?php
require_once DOL_DOCUMENT_ROOT.'/core/modules/facture/modules_commande.php';
require_once DOL_DOCUMENT_ROOT.'/custom/pdfcustom/lib/pdf_onr.lib.php';

class pdf_onr_commande extends ModelePDFCommandes
{
    public $name = 'onr_commande';

    function write_file($object, $outputlangs)
    {
        $pdf = onr_init_pdf(array(210,297));

        onr_draw_header($pdf, $object);
        onr_draw_client($pdf, $object);
        onr_draw_doc_info($pdf, $object, 'commande');
        onr_draw_lines($pdf, $object);
        onr_draw_footer($pdf);

        return 1;
    }
}