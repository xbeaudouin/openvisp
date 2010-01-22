<form name="add-website-alias" method="post">
<?php
print $tMessage . "<br />\n";
print $PALANG['pWhostCreate_website_alias_welcome'] . "\n";
?>
<br />
<br />

<table class="auto">
<tr>
  <td>
    <?php print $PALANG['pWhostCreate_website_alias_vhost']; ?>
  </td>
  <td align="right">
    <input type="text" name="fAlias" value="">
  </td>
</tr>
<tr>
  <td>
    <?php print $PALANG['pWhostCreate_website_alias_target']; ?>
  </td>
  <td align="right">
    <select name="fWebsite">
      <?php
      for ($i = 0; $i < sizeof($list_domains); $i++)
      {
	if ((is_array ($list_domains) and sizeof ($list_domains) > 0))
	{
	  if ( is_array($domain_websites[$i]) and sizeof($domain_websites[$i]) > 0)
	  {
	    for($j = 0; $j < sizeof($domain_websites[$i]); $j++)
            {
	      if ($domain_websites[$i][$j]['vhost'] != '')
	      {
                print "<option value=\"".$domain_websites[$i][$j]['vhost'].".".$domain_properties[$i]['domain']."\">".$domain_websites[$i][$j]['vhost'].".".$domain_properties[$i]['domain']."</option>\n";
	      }
	      else {
	        print "<option value=\"".$domain_properties[$i]['domain']."\">".$domain_properties[$i]['domain']."</option>\n";
	      }
	    }
	  }
	}
      }
      ?>
    </select>
  </td>
  </tr>
  <tr>
    <td colspan="2" align="right">
      <br /><br />
      <input class="button" type="submit" name="submit" value="<?php print $PALANG['pWhostCreate_website_alias_button']; ?>" /> 
    </td>
  </tr>
</table>
</form>
<br />
