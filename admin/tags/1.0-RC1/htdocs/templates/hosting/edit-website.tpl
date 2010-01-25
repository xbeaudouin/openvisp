<form name="add-website" method="post">
<?php
print $tMessage . "<br />\n";
print $PALANG['pWhostEdit_website_welcome'] . "\n";
?>
<br />
<br />

<table class="auto">
<tr>
  <td>
    <?php print $PALANG['pWhostCreate_website_domain']; ?> :
  </td>
  <td align="right">
	<input type="hidden" name="fDomain" value="<?php print $infovirtual['domain']; ?>">
	<?php print $infovirtual['domain']; ?>
  </td>
  </tr>
  <tr>
    <td>
      <?php print $PALANG['pWhostEdit_website_vhost']; ?>
    </td>
    <td align="right">
      <input type="hidden" name="fVhost" value="<?php print $infovirtual['vhost']; ?>">
	    <?php print $infovirtual['vhost']; ?>
    </td>
  </tr>
  <tr>
    <td colspan="2">
    <br />
      *** Options system need to be defined ***      
  </tr>
  <tr>
    <td>
      Options :
    </td>
    <td align="right">
      <select name="fOptions[]" size="5" multiple>
        <option value="memory_limit=32M">memory_limit=32M</option>
        <option value="upload_max_filesize=8M">upload_max_filesize=8M</option>
        <option value="expose_php=0">expose_php=0</option>
        <option value="blabla1">blabla</option>
        <option value="blabla2">blabla</option>
      </select>
  </tr>
  <tr>
    <td colspan="2" align="right">
      <br /><br />
      <input class="button" type="submit" name="submit" value="<?php print $PALANG['pWhostEdit_website_button']; ?>" /> 
    </td>
  </tr>
</table>
</form>
<br />
