<?php

require ("../variables.inc.php");
require ("../config.inc.php");
require ("../lib/functions.inc.php");
require ("../lib/hosting.inc.php");
include ("../languages/" . check_language () . ".lang");

function nb_format($string){
	return number_format($string,0, ',', ' ') ;
}

$SESSID_USERNAME = check_user_session();

$fLogin = get_get('fLogin');
$fDate = get_get('date');

if ( $fDate == NULL ){
	$now = time();
	$fMonth = date("m",$now);
	$fYear = date("Y",$now);
 }
 else{
	 $full_date = explode('-', $fDate);
	 $fMonth = $full_date[1];
	 $fYear= $full_date[0];
 }

$uldl_month = list_ftp_month($fLogin,$fMonth,$fYear);

if ((is_array ($uldl_month) and sizeof ($uldl_month) > 0))
  { 
		for ($i = 0; $i < sizeof ($uldl_month); $i++)
			{
				$day[] = $uldl_month[$i]['date'];
				$dl[] = $uldl_month[$i]['upload'];
				$ul[] = $uldl_month[$i]['download'];
			}
	}


require_once "../lib/graphics/BarPlot.class.php";



$graph = new Graph(800, 400);
$graph->setBackgroundColor(new Color(0xF4, 0xF4, 0xF4));
$graph->shadow->setSize(3);

$graph->title->set('Traffic FTP '.$fLogin.' ('.$fMonth.' '.$fYear.')');
$graph->title->setFont(new Tuffy(15));
$graph->title->setColor(new Color(0x00, 0x00, 0x8B));


$group = new PlotGroup;
$group->setSize(0.82, 1);
$group->setCenter(0.41, 0.5);
$group->setPadding(80, 26, 80, 27);
$group->setSpace(2, 2);

$group->grid->setColor(new Color(0xC4, 0xC4, 0xC4));
$group->grid->setType(Line::DASHED);
$group->grid->hideVertical(TRUE);
$group->grid->setBackgroundColor(new White);
$group->axis->left->setColor(new DarkGreen);
$group->axis->left->label->setFont(new Font2);

$group->axis->right->setColor(new DarkBlue);
$group->axis->right->label->setFont(new Font2);

$group->axis->bottom->label->setFont(new Font2);

$group->legend->setPosition(1.18);
$group->legend->setTextFont(new Tuffy(8));
$group->legend->setSpace(10);

$plot = new BarPlot($ul, 1, 2);
$plot->setBarColor(new MidYellow);
$plot->setBarPadding(0.15, 0.15);
$plot->barShadow->setSize(3);
$plot->barShadow->smooth(TRUE);
$plot->barShadow->setColor(new Color(200, 200, 200, 10));
$plot->move(1, 0);

$plot->label->set($ul);
$plot->label->setInterval(1);
$plot->label->setAngle(90);
$plot->label->setAlign(NULL, Label::TOP);
$plot->label->setPadding(3, 1, 0, 6);
$plot->label->setCallbackFunction('nb_format');
$plot->label->setBackgroundColor(
    new Color(227, 223, 241, 15)
);
$plot->label->setFont(new Tuffy(7));



$group->legend->add($plot, "Upload", Legend::BACKGROUND);
$group->add($plot);


$plot = new BarPlot($dl, 2, 2);
$plot->setBarColor(new Color(120, 175, 80, 10));
$plot->setBarPadding(0.15, 0.15);
$plot->barShadow->setSize(3);
$plot->barShadow->smooth(TRUE);
$plot->barShadow->setColor(new Color(200, 200, 200, 10));

$group->axis->bottom->setLabelText($day);

$group->legend->add($plot, "Download", Legend::BACKGROUND );
$group->axis->left->label->setCallbackFunction('convert_number_size_string');

$plot->label->set($dl);
$plot->label->setInterval(1);
$plot->label->setAngle(90);
$plot->label->setAlign(NULL, Label::TOP);
$plot->label->setPadding(3, 1, 0, 6);
$plot->label->setCallbackFunction('nb_format');
$plot->label->setBackgroundColor(
    new Color(227, 223, 241, 15)
);
$plot->label->setFont(new Tuffy(7));

$group->add($plot);

$graph->add($group);
$graph->draw();


?>