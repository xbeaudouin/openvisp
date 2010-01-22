<?php print $tMessage . "\n"; ?>
<table class="auto">
   <tr>
      <td align="center" colspan="3">
         <?php print $PALANG['pCreate_domain_alias_welcome'] . "\n"; ?>
         <br />
         <br />
      </td>
   </tr>
   <tr>
      <td>
         <form name="alias" method="post">
         <?php print $PALANG['pCreate_domain_alias_new_domain'] . ":\n"; ?>
      </td>
      <td>
         <input type="text" name="fNewDomain" value="<?php print $tNewDomain; ?>" />
      </td>
      <td>
         <?php print $PALANG['pCreate_domain_alias_is']; ?>
      </td>
      <td>
         <select name="fDomain_id">
         <?php
         for ($i = 0; $i < sizeof ($list_domains); $i++)
         {
            if ($tDomain == $list_domains[$i])
            {
               print "            <option value=\"".$list_domains[$i]['id']."\" selected>".$list_domains[$i]['domain']."</option>\n";
            }
            else
            {
               print "            <option value=\"".$list_domains[$i]['id']."\">".$list_domains[$i]['domain']."</option>\n";
            }
         }
         ?>
         </select>
      </td>
   </tr>
   <tr>
      <td align="center" colspan="3">
         <input type="submit" name="submit" value="<?php print $PALANG['pCreate_domain_alias_button']; ?>" />
         </form>
      </td>
   </tr>
</table>
<p />
