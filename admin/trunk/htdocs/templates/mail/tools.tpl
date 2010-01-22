<center>
<?php print $tMessage . "\n"; ?>
<table class="auto">
   <tr>
      <td align="center" colspan="5">
         <?php print "Make operation on the domain $tDomain". "\n"; ?>
         <br />
         <br />
				 Reinitialiser les quarantaines (RAZ des Operation sur mail en quaranatine) OK<br />
				 Reinit de tous les comptes poure renvoyer le mail de quarantaine <br />
				 Remettre tous le monde en greylisting <br />
				 Mettre tout le monde sous le mailing Quarantaine <br />
				 Augmenter le quota des comptes<br />
				 <br /><br />
      </td>
   </tr>

   <tr>
      <td>
         <form name="reset_quar_status" method="post">
         <?php print $PALANG['pTools_reset_quarantine'] . ":\n"; ?>
      </td>

      <td>
			<select name="fUser" size="3" multiple>
	 
         <?php
	 for ($i = 0; $i < sizeof ($mailbox_list); $i++){
		 print "<option value=\"".$mailbox_list[$i]."\">".$mailbox_list[$i]."</option>\n";
	 }
         ?>

				 </select>
      </td>

			<td align="right">
				 <?php print $PALANG['pTools_reset_begindate']?> <input name="fBeginDate" size="10" value="YYYYMMDD"><br />
				 <?php print $PALANG['pTools_reset_enddate']?> <input name="fEndDate" size="10" value="YYYYMMDD">
			</td>

      <td align="right">
				 <input type="submit" value="Go">
				 </form>
      </td>

	    <td></td>
   </tr>

	 <tr colspan="5">
	 <td><br/></td>
   </tr>

   <tr>
      <td>
         <form name="search_mail" method="post">
	 <?php print $PALANG['pTools_find_mail'] . ":\n"; ?>
      </td>

      <td>
	 <?php print $PALANG['pTools_find_mail_from'] . ":\n"; ?>
			<select name="fUser" size="1">
	 
         <?php
	 for ($i = 0; $i < sizeof ($mailbox_list); $i++){
		 print "<option value=\"".$mailbox_list[$i]."\">".$mailbox_list[$i]."</option>\n";
	 }
         ?>
				 </select>

      </td>

	    <td>

				 <?php print $PALANG['pTools_find_mail_to']?> <input name="fEmailTo" size="30"><br />

			</td>

			<td align="right">
	 <?php print $PALANG['pTools_find_mail_begindate']?> <input name="fBeginDate" size="10" value="YYYYMMDD"><br />
	 <?php print $PALANG['pTools_find_mail_enddate']?> <input name="fEndDate" size="10" value="YYYYMMDD">
			</td>

      <td>
				 <input type="submit" value="Go">
				 </form>
      </td>
   </tr>



	 <tr colspan="5">
	 <td><br/></td>
   </tr>

   <tr>
      <td>
         <form name="search_mail" method="post">
	 <?php print $PALANG['pTools_find_mail'] . ": \n"; ?>
      </td>

      <td>
	 <?php print $PALANG['pTools_find_mail_from'] . ":\n"; ?>
			<select name="fUser" size="1">
	 
         <?php
	 for ($i = 0; $i < sizeof ($mailbox_list); $i++){
		 print "<option value=\"".$mailbox_list[$i]."\">".$mailbox_list[$i]."</option>\n";
	 }
         ?>
				 </select>

      </td>

	    <td>

				 <?php print $PALANG['pTools_find_mail_to']?> <input name="fEmailto" size="30"><br />

			</td>

			<td align="right">
	 <?php print $PALANG['pTools_find_mail_begindate']?> <input name="fBeginDate" size="10" value="YYYYMMDD"><br />
	 <?php print $PALANG['pTools_find_mail_enddate']?> <input name="fEndDate" size="10" value="YYYYMMDD">
			</td>

      <td>
				 <input type="submit" value="Go">
				 </form>
      </td>
   </tr>


</table>
