<center>
<?php print $tMessage . "\n"; ?>

<table class="auto">
   <tr>
      <td align="center" colspan="3">
         <?php print $PALANG['pDataCenter_apc_welcome'] . ":\n"; ?>
         <br />
         <br />
      </td>
   </tr>
<form name="apc" method="post" onSubmit="please_wait();" >

<?php
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

  $pdu = 0;
  foreach($apc_list as $apc) {
   $pdu++;
   print "<tr>";
   $apc_name = string_to_apc ($apc);
   $apc_outlet = string_to_apc_outlet($apc);
   $apc_properties = get_apc_properties($apc_name);
   $apc_ip = $apc_properties['ip'];
   $apc_outlet_stuff = apc_get_outlet_stuff($apc_ip, $apc_outlet); 
   $apc_outlet_name = $apc_outlet_stuff['label'];
   $apc_outlet_status = $apc_outlet_stuff['status'];
   print "<td>" . $PALANG['pDataCenter_apc_legend']. " : </td>";
   print "<td><b>" . $apc ."</tb></td>";
   print "<td> ";
   print "<select name=\"fPDU".$pdu."\">";
    for ($j = 1; $j< 4; $j++) {
      print "<option value=\"$apc_name :: $apc_outlet :: $j\"";
      if ($j == $apc_outlet_status) {
        print " selected";
      }
      print ">" . $outlet_status_popdown[$j] . "</option>";
    }
   print "</select> ". $outlet_status[$apc_outlet_status]. "</td></tr>";
  }
  print "<input type=\"hidden\" name=\"fNbPdu\" value=\"$pdu\">";
?>
   <tr>
     <td><?php print $PALANG['pDataCenter_apc_password'] . " : "; ?> </td>
     <td colspan="2"><input type="password" name="fPassword"> </td>
   </tr>
   <tr>
      <td align="center" colspan="3">
         <input type="submit" name="submit" value="<?php print $PALANG['pDataCenter_apc_submit']; ?>"  />
         </form>
      </td>
   </tr>

</form>
</table>
<p />
