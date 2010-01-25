
<form method="POST">

<select name="fPeriode" onChange="this.form.submit()";>

<?php

if ( $fPeriode == NULL ){
	$now = time();
	$fMonth = date("m",$now);
	$fYear = date("Y",$now);
	$fPeriode = $fYear.'-'.$fMonth;
 }

if ((is_array ($list_date_ftp) and sizeof ($list_date_ftp) > 0))
  { 
		for ($i = 0; $i < sizeof ($list_date_ftp); $i++)
			{
				$selected = "";
				if ( $list_date_ftp[$i]['date'] == $fPeriode ) $selected="selected";
				print '<option value="'.$list_date_ftp[$i]['date'].'" '.$selected.'>'.$list_date_ftp[$i]['date'];
			}
	}

print ' <input type="hidden" name="account" value="'.$fFtplogin.'"> ';
?>
</select>
</form>

<?php

print '<img src="ftp-graph.php?fLogin='.$fFtplogin.'&date='.$fPeriode.'">';

?>
