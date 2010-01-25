<center>
<?php print $tMessage . "\n"; ?>
<?php
  if (is_array ($list_admins) && (sizeof($list_admins) > 0)) {
?>

<table class="auto">
   <tr>
      <td align="center" colspan="2">
         <?php print $PALANG['pDataCenter_adminuser_add'] . "\n"; ?>
         <br />
         <br />
      </td>
   </tr>
   <form name="add_user" method="post">
   <tr>
      <td>
         <?php print $PALANG['pDataCenter_adminuser_add_user'] . ":\n"; ?>
      </td>
      <td>
<?php
 if (sizeof ($list_admins) == 1) {
  print "<b>".$list_admins[0]."</b>";
  print "<input type=\"hidden\" name=\"fUser\" value=\"".$list_admins[0]."\" />";
 } else {
    print "<select name=\"fUser\">";
    for ($i = 0; $i < sizeof ($list_admins); $i++)
    { 
     print "<option value=\"".$list_admins[$i]."\">".$list_admins[$i]."</option>";
    }
  print "</select>";
 }

?>
      </td>
   </tr>
   <tr>
      <td>
         <?php print $PALANG['pDataCenter_adminuser_add_full'] ; ?>
       </td>
      <td>
         <?php $checked = (!empty ($tFull)) ? 'checked' : ''; ?>
         <input type="checkbox" name="fFull" <?php print $checked; ?> />
      </td>
   </tr>
   <tr>
      <td align="center" colspan="2">
         <input type="submit" name="submit" value="<?php print $PALANG['pDataCenter_adminuser_add_submit']; ?>" />
         </form>
      </td>
   </tr>
</table>
<?php
} else {
  print $PALANG['pDataCenter_adminuser_add_impossible'] ;
  print "<br/><a href=\"admin_list.php\">".$PALANG['pDataCenter_adminuser_add_back'] ."</a>";
}
?>
  
<p />
