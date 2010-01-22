<center>
<?php print $tMessage . "\n"; ?>

<table class="auto">
   <tr>
      <td align="center" colspan="3">
         <?php print $PALANG['pDataCenter_adminapc_apc_add_done'] . ":\n"; ?>
         <br />
         <br />
      </td>
   </tr>
   <tr>
      <td>
         <?php print $PALANG['pDataCenter_adminapc_apc_name'] . ":\n"; ?>
      </td>
      <td colspan="2">
         <?php print $tName; ?>
      </td>
   </tr>
   <tr>
      <td>
         <?php print $PALANG['pDataCenter_adminapc_apc_ip'] . ":\n"; ?>
      </td>
      <td>
         <?php print $tIp; ?>
      </td colspan="2">
   </tr>
<?php
 if ($fPrint == "on") {
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
  // Create outlet array depending of number of outlet in detected APC
  $outlets = array ();
  for ($itmp = 1; $itmp <= $apc_basic_stuff['numpdu']; $itmp++) {
    array_push($outlets, $itmp);
  }
  $outlet_status = array (
    1 => $PALANG['pDataCenter_adminapc_apc_outlet_on'],
    2 => $PALANG['pDataCenter_adminapc_apc_outlet_off'],
    3 => $PALANG['pDataCenter_adminapc_apc_outlet_reboot']
  );
  foreach ($outlets as $i) {
    $pdustuff = apc_get_outlet_stuff($tIp, $i);
    print "<tr><td>".$PALANG['pDataCenter_adminapc_apc_outlet_name'];
    print  " ". $i . " :</td><td>".$pdustuff['label']."</td><td>";
    print $outlet_status[$pdustuff['status']] . "</td></tr>";
  }

 }
?>
</table>
<p />
