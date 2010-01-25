<?php

print load_js("../lib/ajax_server.php");

if ( $list_server_job != "" )
	{

  print load_js("../lib/yui/yahoo-dom-event/yahoo-dom-event.js");
  print load_js("../lib/yui/connection/connection-min.js");
  print load_js("../lib/yui/element/element-beta.js");
  print load_js("../lib/yui/datasource/datasource-beta-min.js");
  print load_js("../lib/yui/datatable/datatable-beta-min.js");

  print load_css("../css/datatable.css");


?>

<style type="text/css">
/* custom styles for this example */
.yui-skin-sam .yui-dt-col-address pre { font-family:arial;font-size:100%; } /* Use PRE in first col to preserve linebreaks*/
</style>

<div id="xml" class=" yui-skin-sam"></div>

<script type="text/javascript">

<?php 

?>

	var myBuildUrl = function(record) {
		var url = '';
		var cols = this.getColumnSet().keys;
		for (var i = 0; i < cols.length; i++) {
			url += '&' + cols[i].key + '=' + escape(record.getData(cols[i].key));
		}
		return url;
	};


YAHOO.widget.DataTable.prototype.saveCellEditor = function() {


				// ++++ this is the inner function to handle the several possible failure conditions
				var onFailure = function (msg) {
					alert(msg);

					// --------      on failure section
					this.resetCellEditor();
					this.fireEvent("editorRevertEvent",
					{editor:this._oCellEditor, oldData:oldData, newData:newData}
												 );
					// --------      end of on failure section

				};

				// +++ this comes from the original except for the part I cut to place in the function above.

				if(this._oCellEditor.isActive) {
					var newData = this._oCellEditor.value;
					var oldData = this._oCellEditor.record.getData(this._oCellEditor.column.key);

					if(this._oCellEditor.validator) {
            newData = this._oCellEditor.validator.call(this, newData, oldData);
            this._oCellEditor.value = newData;
            if(newData === null ) {

							// this is where the contents of the inner function onFailure used to be.
							onFailure('validation');
							return;
            }
					}

					// ++++++ from here on I added new, except for the 'success' case pasted in.
					var act = 'manage-app.php';
					var postdata = 'server_id=' + <?php print $fServer_id;?> + '&upfield=' + this._oCellEditor.column.key + '&newvalue=' + escape(newData) + myBuildUrl.call(this, this._oCellEditor.record);

					YAHOO.util.Connect.asyncRequest(
																					'POST',
																					act,
																					{
																					success: function (o) {

																							if ( o.responseText == 'OK' ) {
																								
																								// --------     on success section
																								this._oRecordSet.updateRecordValue(this._oCellEditor.record, this._oCellEditor.column.key, this._oCellEditor.value);
																								this.formatCell(this._oCellEditor.cell.firstChild);
																								this.resetCellEditor();
																								this.fireEvent("editorSaveEvent",
																															 {editor:this._oCellEditor, oldData:oldData, newData:newData}
																															 );
																								// --------     end of on success section
																								
																							} else {
																								onFailure.call(this,o.responseText);
																							}
																						},
																							failure: function(o) {
																							onFailure(this, o.statusText);
																						},
																							scope: this
																							},
																					postdata
																					);
				} else {
				}
			};

YAHOO.util.Event.addListener(window, "load", function() {


    YAHOO.example.Basic = new function() {
			var myColumnDefs = [
  			{key:"role", sortable:true},
			  {key:"role_id", hidden:true, minWidth:0},
			  {key:"app", sortable:true},
   			{key:"app_id", hidden:true, minWidth:0},
   			{key:"ip_id", hidden:true, minWidth:0},
   			{key:"ip"},
   			{key:"hostname"},
        {key:"version"},
        {key:"login", editor:"textbox"},
        {key:"password", editor:"textbox"},
        {key:"port", editor:"textbox"},
			  {key:"active", sortable:true, editor:"radio", editorOptions:{radioOptions:["Yes","No"],disableBtns:true}},
			  {key:'delete',label:' ',formatter:function(elCell) {
						elCell.innerHTML = '<img src="../images/ico-exit.png" title="delete row" height="20" width="20"/>';
						elCell.style.cursor = 'pointer';
				}},
      ];
 
			this.myDataSource = new YAHOO.util.DataSource("../ajax/server_info.php");
			this.myDataSource.connMethodPost = true;
			this.myDataSource.responseType = YAHOO.util.DataSource.TYPE_XML;

			this.myDataSource.responseSchema = {
				resultNode: "application",
				fields: ["role", "role_id","app", "app_id", "version", "login", "password", "port", "active", "ip_id", "ip","hostname"]
			};
 
			this.myDataTable = new YAHOO.widget.DataTable("xml", myColumnDefs,
        this.myDataSource, {initialRequest:"fServer_id=<?php print $fServer_id;?>&fType=server"});



			// Set up editing flow
			this.highlightEditableCell = function(oArgs) {
				var elCell = oArgs.target;
				if(YAHOO.util.Dom.hasClass(elCell, "yui-dt-editable")) {
					this.highlightCell(elCell);
				}
			};

			this.myDataTable.subscribe("cellMouseoverEvent", this.highlightEditableCell);
			this.myDataTable.subscribe("cellMouseoutEvent", this.myDataTable.onEventUnhighlightCell);

			this.myDataTable.subscribe('cellClickEvent',function(ev) {
					var target = YAHOO.util.Event.getTarget(ev);
					var column = this.getColumn(target);

					if (column.key == 'delete') {

						var record = this.getRecord(target);

						if (confirm('Are you sure to delete ?')) {

							var record = this.getRecord(target);
							var act = './manage-app.php';
							var postdata = 'server_id=' + <?php print $fServer_id;?> + '&action=delete' + myBuildUrl.call(this, record);
							YAHOO.util.Connect.asyncRequest(
																							"POST",
																							act,
																							{
																							success: function (o)
																								{
																									if (o.responseText == 'OK') { this.deleteRow(target); }
																									else { alert(o.responseText); }
																								},
																									failure: function (o) { alert(o.statusText); },
																									scope:this
																							},
																							postdata
																							);
						}
					} else {
						this.onEventShowCellEditor(ev);
					}
				});


			// Hook into custom event to customize save-flow of "radio" editor
			this.myDataTable.subscribe("editorUpdateEvent", function(oArgs) {
					if(oArgs.editor.column.key === "active") {
						this.saveCellEditor();
					}
        });

			this.myDataTable.subscribe("editorBlurEvent", function(oArgs) {
					this.cancelCellEditor();
        });


		};
});



</script>

<br/>

<?php

		}
?>

<form method="POST">

<table>
	<tr>
	  <td><?php print $PALANG['pApplication_group']; ?></td>
	  <td>
		  <select name="role_id" id="role_id" onChange="display_role_app2()">
		  <option value="">
		<?php
		for ($i = 0; $i < sizeof ($list_job_model); $i++)
			{
				print "<option value=\"".$list_job_model[$i]['id']."\">".$list_job_model[$i]['role']."</option>\n";
			}
?>
      </select>
    </td>
		<td>
			<div id='form_app_id' style='display:inline'> </div>
	  </td>

		<td>
	    Priv @IP <input type="text" name="priv_ip" size="15">
    </td>

		<td>
	    Pub @IP <input type="text" name="pub_ip" size="15">
    </td>

    <td>
      Login <input type="text" name="login">
    </td>

    <td>
      Password <input type="text" name="password">
    </td>

    <td>
      Port <input size="5" type="text" name="port">
    </td>

  </tr>


	</table>

<input type="hidden" name="server_id" value="<?php print $fServer_id;?>">
<input type="hidden" name="action" value="add">
<input type="submit">

</form>