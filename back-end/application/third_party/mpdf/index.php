<?php error_reporting(0);
$html = '

<h3>Title</h3>
<p>I want to go with my dogy in every morning 1</p>
<h3>Start Date </h3>
<p>11/12/18</p>
<h3>End Date </h3>
<p>12/12/18</p>

<table border="1" style="border: 1px solid #880000; font-family: Mono; font-size: 7pt; ">
<tbody>
<tr border="1" style="border: 1px solid #880000; font-family: Mono; font-size: 7pt; ">
	<td>Date</td> <td>Rating</td><td>Description</td></tr>
	
<tr border="1" style="border: 1px solid #880000; font-family: Mono; font-size: 7pt; ">
	<td>12/12/18</td> <td>5</td><td>Description</td></tr>


</tbody></table>';
include("mpdf60/mpdf.php");
ob_clean();

$mpdf=new mPDF(); 
//$mpdf->AddPage('L');
//$mpdf->WriteHTML($html);
//$mpdf->Output();


$mpdf->SetDisplayMode('fullpage');
$mpdf->list_indent_first_level = 0;	// 1 or 0 - whether to indent the first level of a list
// LOAD a stylesheet
$stylesheet = file_get_contents('assets/mpdfstyletables.css');
$mpdf->WriteHTML($stylesheet,1);	// The parameter 1 tells that this is css/style only and no body/html/text
$mpdf->WriteHTML($html);
$mpdf->Output();
exit;


?>