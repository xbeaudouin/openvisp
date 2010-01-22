<?php

$radius = 200;
$margin = 20;
$pagecount = 1;


$pdf=new FPDF('P','mm','A4');
$pdf->Open();

if (sizeof ($tMailbox) > 0)
{

   for ($i = 0; $i < sizeof ($tMailbox); $i++)
   {
		 


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

$string = $tMailbox[$i]['name']."
".$tMailbox[$i]['pdf_custadd']."
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
$string = $PALANG['pPdf_string1'];;

$pdf->MultiCell(0,4, html_entity_decode($string), 0, 'L');

$string = sprintf($PALANG['pPdf_string2'], $tCompany);

$pdf->SetXY(10,115);
$pdf->MultiCell(180,4, html_entity_decode($string), 0, 'L');


$pdf->Rect(11,160,180,35,'S');

$string  = $PALANG['pPdf_email']."\n".$PALANG['pPdf_login']."\n".$PALANG['pPdf_password']."\n".$PALANG['pPdf_quota']."\n";
$string .= $PALANG['pPdf_serverpop3']."\n".$PALANG['pPdf_serverimap']."\n".$PALANG['pPdf_serversmtp']."\n".$PALANG['pPdf_serverwebmail'] ;

$pdf->SetXY(21,161);
$pdf->MultiCell(180,4, $string, 0, 'L');


$string = ":
:
:
:
:
:
:
:";

$pdf->SetXY(61,161);
$pdf->MultiCell(180,4, $string, 0, 'L');


$string = $tMailbox[$i]['username']."
".$tMailbox[$i]['username']."
".$tMailbox[$i]['password']."\n";

if ( ($tMailbox[$i]['quota'] / $CONF['quota_multiplier']) == "-1" ){
	$string .= $PALANG['pPdf_noquota']."\n";
 }
 else {
	 $string .= ($tMailbox[$i]['quota'] / $CONF['quota_multiplier'])." ".$PALANG['pPdf_quota_mb']."\n";
 }

$string .= $tMailbox[$i]['pdf_pop']."
".$tMailbox[$i]['pdf_imap']."\n";

if ( $tSmtpauth == "1" ){
	$string .= $tMailbox[$i]['pdf_smtp'].$PALANG['pPdf_string3'];
 }
 else {
	 $string .= $PALANG['pPdf_string4'];
 }

$string .="\nhttp://".$tMailbox[$i]['pdf_webmail'];

$pdf->SetXY(71,161);
$pdf->MultiCell(180,4, $string, 0, 'L');

if ( $tSmtpauth == "1" ) {

	$string  = $PALANG['pPdf_smtpauth'];
	$string .= "\n\n";
	$pdf->SetXY(10,200);
	$pdf->MultiCell(200,4, html_entity_decode($string), 0, 'L');
}
$string = $PALANG['pPdf_string5'];

$pdf->SetXY(10,210);
$pdf->MultiCell(210,4, html_entity_decode($string), 0, 'L');

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
$pdf->MultiCell(210,4, html_entity_decode($string2), 0, 'L');
$pdf->SetXY(110,220);
$pdf->MultiCell(210,4, html_entity_decode($string3), 0, 'L');


	 }
}

$pdf->Close();

$pdf->Output();


?>
