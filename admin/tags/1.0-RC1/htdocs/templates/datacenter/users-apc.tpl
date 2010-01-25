<center>
<?php 
print $tMessage . "\n"; 
if (is_array($list_apc)) 
{
?>
<table class="auto">
   <tr>
      <td align="center" colspan="3">
         <?php print $PALANG['pDataCenter_adminuser_apc_welcome'] . "\n"; ?>
         <br />
         <br />
      </td>
   </tr>
   <form name="apc" method="post">
   <tr>
      <td>
        <?php
          print "<input type=\"hidden\" name=\"name\" value=\"$fName\" />";
          print $PALANG['pDataCenter_adminuser_apc_select_outlets'] . " :";
        ?>
       </td>
      <td colspan=2 align=center>
<select name="fApcs[]" size="10" multiple="multiple">
<?php
for ($i = 0; $i < sizeof ($list_apc); $i++)
{  
   if (in_array ($list_apc[$i], $tApc))
   {
      print "<option value=\"" . $list_apc[$i] . "\" selected=\"selected\">" . $list_apc[$i] . "</option>\n";
   }
   else
   {
      print "<option value=\"" . $list_apc[$i] . "\">" . $list_apc[$i] . "</option>\n";
   }
}
?>
</select>

      </td>
   </tr>
   <tr>
      <td align="center" colspan="3">
         <input type="hidden" name="name" value="<?php print $fName; ?>" />
         <input type="submit" name="submit" value="<?php print $PALANG['pDataCenter_adminuser_apc_select_submit']; ?>" />
         </form>
      </td>
   </tr>
</table>
<?php
} else {
  print $PALANG['pDataCenter_adminuser_add_apc_impossible'] ;
  print "<br/><a href=\"admin_list.php\">".$PALANG['pDataCenter_adminuser_add_back'] ."</a>";
}

?>
<p />
