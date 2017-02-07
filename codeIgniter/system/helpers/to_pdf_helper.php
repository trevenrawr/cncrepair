<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

// WKPDF LIBRARY interface
function pdf_create($html, $filename, $stream=TRUE)
{
    require_once("wkhtmltopdf/wkpdf.php");

    $pdf = new WKPDF();
    $pdf->set_html($html);
    $pdf->render();
    if ($stream) {
	$pdf->output(WKPDF::$PDF_EMBEDDED, $filename.'.pdf');
    } else {
        $CI =& get_instance();
        $CI->load->helper('file');
        write_file("$filename.pdf", $pdf->output(WKPDF::$PDF_ASSTRING, $filename.'.pdf'));
    }
}

// DOMPDF LIBRARY BACK-UP
// function pdf_create($html, $filename, $stream=TRUE)
// {
//     require_once("dompdf/dompdf_config.inc.php");
//     spl_autoload_register('DOMPDF_autoload');
//
//     $dompdf = new DOMPDF();
//     $dompdf->load_html($html);
//     $dompdf->render();
//     if ($stream) {
//         $dompdf->stream($filename.".pdf");
//     } else {
//         $CI =& get_instance();
//         $CI->load->helper('file');
//         write_file("$filename.pdf", $dompdf->output());
//     }
// }