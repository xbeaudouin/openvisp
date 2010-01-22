<?php


$radius = 200;
$margin = 20;
$pagecount = 1;


$pdf=new FPDF('P','mm','A4');
$pdf->Open();
$pdf->AddPage();

if (file_exists("logos/".$tLogo)) {
	list($logo_width, $logo_height, $logo_type, $logo_attr) = getimagesize("logos/$tLogo");

	//$logo = pdf_open_image_file($pdf, "jpeg", "logos/$tLogo","",1);

	$pdf->Image("logos/$tLogo", 10, 10, 0, 24);
	//pdf_place_image($pdf,$logo,(($hsize/2)-($logo_width/2)),($max_vsize-$logo_height),1);
	$pdf->SetXY(10,35);
} else {
	$pdf->SetXY(10,15);
}

$pdf->SetTextColor(0,0,200);
$pdf->SetFont('Times','B',14);
$pdf->MultiCell(76, 1, $tCompany, 0, 'L');

$string = "
$tAddress
$tPostalcode $tCity
".$PALANG['pPdf_tel']." : $tPhone
".$PALANG['pPdf_fax']." : $tFax
".$PALANG['pPdf_web']." : $tWebUrl
".$PALANG['pPdf_email']." : $tEmail
";

$pdf->SetTextColor(0,0,0);
$pdf->SetFont('Times','',10);
$pdf->MultiCell(76,4, $string, 0, 'L');


setlocale(LC_TIME, $PALANG['LOCALE']);
$string = strftime("%A %e %B %Y", time());
$pdf->SetXY(165,14);
$pdf->MultiCell(76,4, $string, 0, 'L');

$tCustadd = str_replace("\\n","\n",$tCustadd);

$string = "$tName
$tCustadd
";

$pdf->SetXY(130,70);
$pdf->MultiCell(100,4, $string, 0, 'L');


$pdf->SetXY(10,100);
$pdf->SetFont('Times','BU',10);
$string = $PALANG['pPdf_object']." :";
$pdf->MultiCell(0,4, $string, 0, 'L');
$w=$pdf->GetStringWidth($string)+16;

$pdf->SetXY($w,100);
$pdf->SetFont('Times','',10);
$Subject = $PALANG['pWhost_pdf_db_subject'];

$pdf->MultiCell(0,4, html_entity_decode($Subject), 0, 'L');

$string = html_entity_decode(sprintf($PALANG['pWhost_pdf_db_body'], $tCompany));

$pdf->SetXY(10,115);
$pdf->MultiCell(180,4, html_entity_decode($string), 0, 'L');

$string = html_entity_decode(sprintf($PALANG['pWhost_pdf_dbhost'],$ip_info['hostname']));

//$pdf->SetXY(10,115);
$pdf->MultiCell(180,4, html_entity_decode($string), 0, 'L');



$header=array($PALANG['pWhost_pdf_dbname'],$PALANG['pPdf_login'],$PALANG['pPdf_password'],$PALANG['pWhost_pdf_dbport']);

foreach($header as $col)
	$pdf->Cell(40,7,$col,1);
$pdf->Ln();
//Données
foreach($list_accounts as $row)
	{
		$pdf->Cell(40,6,$row['Db'],1);
		$pdf->Cell(40,6,$row['User'],1);
		$pdf->Cell(40,6,$row['password'],1);
		$pdf->Cell(40,6,$fServer_port,1);
		$pdf->Ln();
	}


$string1 = "";
$string2 = "";
$string3 = "";

if ( strlen($tSupportPhone) > 1 ) {
	$string1 .= $PALANG['pPdf_byphone'];;
	$string1 .= "\n";
	$string2 .= ":\n";
	$string3 .= "$tSupportPhone\n";
 }
if ( strlen($tSupportmail) > 1 ) {
	$string1 .= $PALANG['pPdf_bymail'];
	$string1 .= "\n";
	$string2 .= ":\n";
	$string3 .= "$tSupportmail\n";
 } 
if ( strlen($tSupportweb) > 1 ) {
	$string1 .= $PALANG['pPdf_website'];
	$string1 .= "\n";
	$string2 .= ":
";
	$string3 .= "$tSupportweb\n";
 } 
if ( strlen($tSupportfaq) > 1 ) {
	$string1 .= $PALANG['pPdf_wwwfaq'];
	$string1 .= "\n";
	$string2 .= ":
";
	$string3 .= "$tSupportfaq\n";
 } 


$pdf->SetXY(20,220);
$pdf->MultiCell(210,4, html_entity_decode($string1), 0, 'L');
$pdf->SetXY(100,220);
$pdf->MultiCell(210,4, $string2, 0, 'L');
$pdf->SetXY(110,220);
$pdf->MultiCell(210,4, html_entity_decode($string3), 0, 'L');



$pdf->Close();

$pdf->Output();


?>
