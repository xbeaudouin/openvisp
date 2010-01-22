<script type="text/javascript" src="../lib/ajax_server.php"></script> 
<center>

<form method="post">

  <table class="auto">

    <tr>
      <td>Nom du role</td>
      <td><input type="text" size="30" name="fRole_name" id="fRole_name" onkeyup="check_modelserver()">
	      <div id='model_status' style='display:inline'></div>
	    </td>
    </tr>

    <tr>
      <td>desc</td>
      <td><textarea rows="10" size="50" name="fRole_desc"></textarea></td>
    </tr>

    <tr>
      <td>Active</td>
      <td><input type="checkbox" name="fRole_active"></td>
    </tr>

    <tr align="center">
      <td colspan="2">
         <input class="button_inactive" type="submit" id="submit" name="submit" value="<?php print $PALANG['pAdd_server_model']; ?>" disabled/>
         <input type="hidden" name="fType" value="model">
      </td>


    </tr>

  </table>

</form>
</div>
</center>