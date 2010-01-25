<center>
<?php print $tMessage . "\n"; ?>

<table class="auto">
   <tr>
      <td align="center" colspan="3">
         <?php print $PALANG['pDataCenter_adminapc_apc_edit'] . ":\n"; ?>
         <br />
         <br />
      </td>
   </tr>
<form name="edit_apc" method="post" onSubmit="please_wait();">
<?php
  $apc_properties = get_apc_properties($fName);
  print "<input type=\"hidden\" name=\"fName\" value=\"".$fName."\">";
  print "<input type=\"hidden\" name=\"fIp\" value=\"".$apc_properties['ip']."\">";
  print "<tr><td>".$PALANG['pDataCenter_adminapc_apc_name'].":</td><td colspan=\"2\"><input name=\"fName2\" value=\"".$fName."\"></td><tr>";
  print "<tr><td>".$PALANG['pDataCenter_adminapc_apc_ip']  .":</td><td colspan=\"2\">".$apc_properties['ip']."</td><tr>";
  $apc_basic_stuff= apc_get_snmp_basic_stuff($apc_properties['ip']);
  print "<tr><td>".$PALANG['pDataCenter_adminapc_apc_netmask']   .":</td><td colspan=\"2\">".$apc_basic_stuff['netmask']."</td><tr>";
  print "<tr><td>".$PALANG['pDataCenter_adminapc_apc_mac']       .":</td><td colspan=\"2\">".$apc_basic_stuff['mac']."</td><tr>";
  print "<tr><td>".$PALANG['pDataCenter_adminapc_apc_model']     .":</td><td colspan=\"2\">".$apc_basic_stuff['model']."</td><tr>";
  print "<tr><td>".$PALANG['pDataCenter_adminapc_apc_serial']    .":</td><td colspan=\"2\">".$apc_basic_stuff['serial']."</td><tr>";
  print "<tr><td>".$PALANG['pDataCenter_adminapc_apc_firmware']  .":</td><td colspan=\"2\">".$apc_basic_stuff['firmware']."</td><tr>";
  print "<tr><td>".$PALANG['pDataCenter_adminapc_apc_hardware']  .":</td><td colspan=\"2\">".$apc_basic_stuff['hardware']."</td><tr>";
  print "<tr><td>".$PALANG['pDataCenter_adminapc_apc_bornon']    .":</td><td colspan=\"2\">".$apc_basic_stuff['bornon']."</td><tr>";
  print "<tr><td>".$PALANG['pDataCenter_adminapc_apc_uptime']    .":</td><td colspan=\"2\">".$apc_basic_stuff['uptime']."</td><tr>";
  print "<tr><td>".$PALANG['pDataCenter_adminapc_apc_amps']      .":</td><td colspan=\"2\">".$apc_basic_stuff['amps']." A </td><tr>";
  print "<tr><td>".$PALANG['pDataCenter_adminapc_apc_outlet_nb'] .":</td><td colspan=\"2\">".$apc_basic_stuff['numpdu']."</td><tr>";
  // Create outlet array depending of number of outlets detected in APC
  $outlets = array ();                                                         
  for ($itmp = 1; $itmp <= $apc_basic_stuff['numpdu']; $itmp++) {              
    array_push($outlets, $itmp);                                               
  }
  $outlet_status = array (
    1 => $PALANG['pDataCenter_adminapc_apc_outlet_on'],
    2 => $PALANG['pDataCenter_adminapc_apc_outlet_off'],
    3 => $PALANG['pDataCenter_adminapc_apc_outlet_reboot']
  );
  $outlet_status_popdown = array (
    1 => $PALANG['pDataCenter_adminapc_apc_edit_outlet_on'],
    2 => $PALANG['pDataCenter_adminapc_apc_edit_outlet_off'],
    3 => $PALANG['pDataCenter_adminapc_apc_edit_outlet_reboot']
  );
  foreach ($outlets as $i) {
    $pdustuff = apc_get_outlet_stuff($apc_properties['ip'], $i);
    print "<tr><td>".$PALANG['pDataCenter_adminapc_apc_outlet_name'];
    print  " ". $i . " :</td><td><input name=\"fPDU".$i."\" value=\"".$pdustuff['label']."\"></td><td>";
    print "<select name=\"fOutlet".$i."\">";
    for ($j = 1; $j< 4; $j++) {
      print "<option value=\"$j\"";
      if ($j == $pdustuff['status']) {
        print " selected";
      }
      print ">" . $outlet_status_popdown[$j] . "</option>";
    }
    print "</select>".$outlet_status[$pdustuff['status']] . "</td></tr>";
  }

?>
   <tr>
      <td align="center" colspan="3">
         <input type="submit" name="submit" value="<?php print $PALANG['pDataCenter_adminapc_apc_edit_button']; ?>" />
         </form>
      </td>
   </tr>

</form>
</table>
<p />
