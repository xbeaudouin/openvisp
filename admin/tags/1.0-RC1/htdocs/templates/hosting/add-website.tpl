<form name="add-website" method="post">
<?php
print $tMessage . "<br />\n";
print $PALANG['pWhostCreate_website_welcome'] . "\n";
?>
<br />
<br />

<table class="auto">
<tr>
  <td>
    <?php print $PALANG['pWhostCreate_website_domain']; ?> :
  </td>
  <td align="right">
    <select name="fDomain">
      <?php
      for ($i = 0; $i < sizeof ($list_domains); $i++)
      {
        if ($fDomain == $list_domains[$i])
        {
          print "<option value=\"$list_domains[$i]\" selected>$list_domains[$i]</option>\n";
        }
        else
        {
          print "<option value=\"$list_domains[$i]\">$list_domains[$i]</option>\n";
        }
      }
      ?>
    </select>
  </td>
  </tr>
  <tr>
    <td>
      <?php print $PALANG['pWhostCreate_website_vhost']; ?>
    </td>
    <td align="right">
      <input type="text" name="fVhost" value="">
    </td>
  </tr>
  <tr>
    <td colspan="2" align="right">
      <br /><br />
      <input class="button" type="submit" name="submit" value="<?php print $PALANG['pWhostCreate_website_button']; ?>" /> 
    </td>
  </tr>
</table>
</form>
<br />
