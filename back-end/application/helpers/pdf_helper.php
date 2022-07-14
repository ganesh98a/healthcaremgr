<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

function pdf_create($filePath, $html, $filename='', $stream=TRUE)
{ 
  error_reporting(0);

    include APPPATH.'third_party/dompdf/dompdf_config.inc.php';
    if (!is_dir($filePath)) {
     mkdir($filePath, 0777, TRUE);
    }
    $dompdf = new DOMPDF();
    $dompdf->set_paper('a4','portrait');
    $dompdf->load_html($html);
    $dompdf->render();
    // if ($stream) {
    //     $dompdf->stream($filename.".pdf", array("Attachment" => 0));
    // } else {
    //     return $pdf = $dompdf->output();
    // }
    $pdf = $dompdf->output();
    file_put_contents($filePath.$filename.".pdf", $pdf);
    if($pdf){
      return true;
    } else {
      return false;
    }

}
