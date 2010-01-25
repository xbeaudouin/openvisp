<center>
<?php print $tMessage . "\n"; ?>
<table class="auto">
   <tr>
      <td align="center" colspan="5">
         <?php print "Make operation on the domain $tDomain". "\n";
// 				 Reinitialiser les quarantaines (RAZ des Operation sur mail en quaranatine) OK<br /> 
// 				 Reinit de tous les comptes poure renvoyer le mail de quarantaine<br />
// 				 Remettre tous le monde en greylisting<br />
// 				 Mettre tout le monde sous le mailing<br />
// 				 Augmenter le quota des comptes<br />
 ?>
         <br />
         <br />
				 <br /><br />
      </td>
   </tr>

   <tr>
      <td>
         <form name="reset_quar_status" method="post">
	       <input type="hidden" name="fDomain" value="<?php print $tDomain; ?>">
	       <input type="hidden" name="fForm_name" value="reset_quar_status">
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
         <form name="clean_quarantine" method="post">
	       <input type="hidden" name="fDomain" value="<?php print $tDomain; ?>">
	       <input type="hidden" name="fForm_name" value="clean_quarantine">

	 <?php print $PALANG['pTools_clean_quarantine'] . ":\n"; ?>
      </td>

      <td>

			<select name="fUser" size="1">
	 
         <?php
	 for ($i = 0; $i < sizeof ($mailbox_list); $i++){
		 print "<option value=\"".$mailbox_list[$i]."\">".$mailbox_list[$i]."</option>\n";
	 }
         ?>
				 </select>

      </td>

	    <td>

			</td>

			<td align="right">

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
	       <input type="hidden" name="fDomain" value="<?php print $tDomain; ?>">
	       <input type="hidden" name="fForm_name" value="search_mail">
	 <?php print $PALANG['pTools_find_mail'] . ":\n"; ?>
      </td>

	    <td>
				 <?php print $PALANG['pTools_find_mail_from'];?> : <br/><input name="fEmailFrom" size="30">
			</td>

      <td>
			<?php print $PALANG['pTools_find_mail_to'] . ":\n"; ?><br />
			<select name="fEmailTo" size="1">
	 
         <?php
	 for ($i = 0; $i < sizeof ($mailbox_list); $i++){
		 print "<option value=\"".$mailbox_list[$i]."\">".$mailbox_list[$i]."</option>\n";
	 }
         ?>
				 </select>

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
	       <input type="hidden" name="fDomain" value="<?php print $tDomain; ?>">
	       <input type="hidden" name="fForm_name" value="search_mail">
	 <?php print $PALANG['pTools_find_mail'] . ":\n"; ?>
      </td>

	    <td>
				 <?php print $PALANG['pTools_find_mail_to'];?> : <br/><input name="fEmailTo" size="30">
			</td>

      <td>
			<?php print $PALANG['pTools_find_mail_from'] . ":\n"; ?><br />
			<select name="fEmailFrom" size="1">
	 
         <?php
	 for ($i = 0; $i < sizeof ($mailbox_list); $i++){
		 print "<option value=\"".$mailbox_list[$i]."\">".$mailbox_list[$i]."</option>\n";
	 }
         ?>
				 </select>

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
         <form name="greylisting_status" method="post">
	       <input type="hidden" name="fDomain" value="<?php print $tDomain; ?>">
	       <input type="hidden" name="fForm_name" value="greylisting_status">
	 <?php print $PALANG['pTools_Greylisting'] . ":\n"; ?>
      </td>

	    <td>
			
			<input name="fButton" type="button" value="Activate it" OnClick="submit();">
			</td>

      <td>
				 <input name="fButton" type="button" value="Desactivate it" OnClick="submit();">
      </td>


			<td>

			</td>

      <td>
				 </form>
      </td>
   </tr>


</table>
