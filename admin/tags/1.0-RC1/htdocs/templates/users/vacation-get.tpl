<?php print $tMessage . "\n"; ?>
<table class="auto">
   <tr>
      <td align="center">
         <br />
         <br />
      </td>
   </tr>
   <tr>
      <td align="center">
         <form name="vacation" method="post">
         <input type="submit" name="fBack" value="<?php print $PALANG['pUsersVacation_button_back']; ?>" />
    			<?php if ( isset ($_GET['username'])) print '<input type="hidden" name="fUsername" value="'.$fUsername.'">'; ?>
		    	<?php if ( isset ($_GET['domain'])) print '<input type="hidden" name="fDomain" value="'.$fDomain.'">'; ?>
         </form>
      </td>
   </tr>
</table>
