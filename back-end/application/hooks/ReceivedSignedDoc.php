<?php
/**
 *
 */
class ReceivedSignedDoc extends MX_Controller
{

  function __construct()
  {
    // code...
  }

public function receive_signed_doc(){




    $data = file_get_contents('php://input');


    // var_dump($data);exit;
    $xml = simplexml_load_string ($data, "SimpleXMLElement", LIBXML_PARSEHUGE);
    $envelope_id = (string)$xml->EnvelopeStatus->EnvelopeID;
    $time_generated = (string)$xml->EnvelopeStatus->TimeGenerated;

    // Store the file. Create directories as needed
    // Some systems might still not like files or directories to start with numbers.
    // So we prefix the envelope ids with E and the timestamps with T
    $files_dir = getcwd() . '/' . $this->xml_file_dir;
    if(! is_dir($files_dir)) {mkdir ($files_dir, 0755);}
    $envelope_dir = $files_dir . "E" . $envelope_id;
    if(! is_dir($envelope_dir)) {mkdir ($envelope_dir, 0755);}

    $filename = $envelope_dir . "/T" .
        str_replace (':' , '_' , $time_generated) . ".xml"; // substitute _ for : for windows-land
    $ok = file_put_contents ($filename, $data);

    if ($ok === false) {
        // Couldn't write the file! Alert the humans!
        error_log ("!!!!!! PROBLEM DocuSign Webhook: Couldn't store $filename !");
        exit (1);
    }
    // log the event
    error_log ("DocuSign Webhook: created $filename");

    if ((string)$xml->EnvelopeStatus->Status === "Completed") {
        // Loop through the DocumentPDFs element, storing each document.
        foreach ($xml->DocumentPDFs->DocumentPDF as $pdf) {
            $filename = $this->doc_prefix . (string)$pdf->DocumentID . '.pdf';
            echo $full_filename = $envelope_dir . "/" . $filename;
            file_put_contents($full_filename, base64_decode ( (string)$pdf->PDFBytes ));
        }
    }
  }
}
