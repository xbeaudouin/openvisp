<?php

	//print load_js("../lib/ajax_server.php");

  print load_js("../lib/yui/yahoo-dom-event/yahoo-dom-event.js");
  print load_js("../lib/yui/connection/connection-min.js");
  print load_js("../lib/yui/autocomplete/autocomplete-min.js");
  print load_js("../lib/yui/element/element-beta.js");
  print load_js("../lib/yui/datasource/datasource-beta-min.js");
  print load_js("../lib/yui/datatable/datatable-beta-min.js");

  print load_css("../css/datatable.css");


?>




<style type="text/css">

</style>

<script type="text/javascript">

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
	    var act = 'manage-db.php';
	    var postdata = 'upfield=' + this._oCellEditor.column.key + '&newvalue=' + escape(newData) + myBuildUrl.call(this, this._oCellEditor.record);
	    
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

	var Dom = YAHOO.util.Dom, Event = YAHOO.util.Event;

	//'datatable=yes&zip=' + query + '&query=' + Dom.get('dt_input').value + queryString,


	var getDomainname = function(query) { 
		myDataSource.sendRequest('domain_name=' + Dom.get('dt_input_domainname').value + '&db_name=' + Dom.get('dt_input_dbname').value,
														 myDataTable.onDataReturnInitializeTable, myDataTable); 
	}; 

	var getDbname = function(query) { 
		myDataSource.sendRequest('domain_name=' + Dom.get('dt_input_domainname').value + '&db_name=' + Dom.get('dt_input_dbname').value,
														 myDataTable.onDataReturnInitializeTable, myDataTable); 
	}; 

	var getDbname = function(query) { 
		myDataSource.sendRequest('domain_name=' + Dom.get('dt_input_domainname').value + '&db_name=' + Dom.get('dt_input_dbname').value,
														 myDataTable.onDataReturnInitializeTable, myDataTable); 
	}; 


	Event.onDOMReady(function() { 
			//				zip = Dom.get('dt_input_zip').value; 
	         
			var oACDS_domainname = new YAHOO.widget.DS_JSFunction(getDomainname); 
			oACDS_domainname.queryMatchContains = true; 

			var oAutoCompDomain = new YAHOO.widget.AutoComplete("dt_input_domainname","dt_ac_domainname_container", oACDS_domainname); 
			oAutoCompDomain.minQueryLength = 3;

			var oACDS_dbname = new YAHOO.widget.DS_JSFunction(getDbname); 
			oACDS_dbname.queryMatchContains = true; 

			var oAutoCompDbname = new YAHOO.widget.AutoComplete("dt_input_dbname","dt_ac_dbname_container", oACDS_dbname); 
			oAutoCompDbname.minQueryLength = 3; 
	 
/* 				var oACDSZip = new YAHOO.widget.DS_JSFunction(getZip);  */
/* 				oACDSZip.queryMatchContains = true;  */
/* 				var oAutoCompZip = new YAHOO.widget.AutoComplete("dt_input_zip","dt_ac_zip_container", oACDSZip);  */
/* 				//Don't query until we have 5 numbers for the zip code  */
/* 				oAutoCompZip.minQueryLength = 5;  */
	 
				var formatUrl = function(elCell, oRecord, oColumn, sData) { 
					elCell.innerHTML = "<a href='" + oRecord.getData("ClickUrl") + "' target='_blank'>" + sData + "</a>"; 
				}; 
	 
				var myColumnDefs = [
														{key:"domain", sortable:true},
														{key:"db_name", sortable:true},
														{key:"description", sortable:true, editor:"textarea"},
														{key:"db_type", sortable:true},
														{key:"server_name", sortable:true},
														{key:"server_port", sortable:true},
														{key:"server_id", hidden:true},
														{key:"server_ip_id", hidden:true},
														{key:"db_id", hidden:true},
														//														{key:"active", sortable:true, editor:"radio", editorOptions:{radioOptions:["Yes","No"],disableBtns:true}},
														{key:'pdf',label:' ',formatter:function(elCell) {
																elCell.innerHTML = '<img src="../images/pdf.png" title="Display PDF" width="20"/>';
																elCell.style.cursor = 'pointer';
															}
														},
														{key:'delete',label:' ',formatter:function(elCell) {
																elCell.innerHTML = '<img src="../images/ico-exit.png" title="delete row" height="20" width="20"/>';
																elCell.style.cursor = 'pointer';
															}
														}
														];

				myDataSource = new YAHOO.util.DataSource("../ajax/database_info.php");
				myDataSource.connMethodPost = true;
				myDataSource.responseType = YAHOO.util.DataSource.TYPE_XML;
				
				myDataSource.responseSchema = {
        resultNode: "database",
        fields: ["domain", "db_name","db_id","db_type", "description", "server_name","server_port","server_id","server_ip_id"]
				};
 
				myDataTable = new YAHOO.widget.DataTable("xml", myColumnDefs,
																								 myDataSource,{initialRequest: 'domain_name=' + Dom.get('dt_input_domainname').value});



			// Set up editing flow
			this.highlightEditableCell = function(oArgs) {
				var elCell = oArgs.target;
				if(YAHOO.util.Dom.hasClass(elCell, "yui-dt-editable")) {
					this.highlightCell(elCell);
				}
			};

			this.myDataTable.subscribe("cellMouseoverEvent", this.highlightEditableCell);
			this.myDataTable.subscribe("cellMouseoutEvent", this.myDataTable.onEventUnhighlightCell);

        
/* 				myDataTable.subscribe("cellDblclickEvent",myDataTable.onEventShowCellEditor); */
/*         myDataTable.subscribe("editorBlurEvent", myDataTable.onEventSaveCellEditor); */



				myDataTable.subscribe('cellClickEvent',function(ev) {
						var target = YAHOO.util.Event.getTarget(ev);
						var column = this.getColumn(target);

						if (column.key == 'description') {
							this.onEventShowCellEditor(ev);
						}

						if (column.key == 'delete') {

							var record = this.getRecord(target);

							if (confirm('Are you sure to delete ?')) {

								var record = this.getRecord(target);
								var act = './manage-app.php';
								var postdata = 'server_id=1' + '&action=delete' + myBuildUrl.call(this, record);
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
						}

						if ( column.key == 'pdf'){
							var record = this.getRecord(target);
							var act = '../gen-pdf.php?type=mysql' + myBuildUrl.call(this, record);
							window.open(act);
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


	    }); 
	});



</script>

<table>
  <tr>

    <td width="18%">
      <div id="autocomplete_domain">
        <label for="dt_input_domainname">Domain: </label><input id="dt_input_domainname" type="text" value="">
        <div id="dt_ac_domainname_container"></div>
      </div>
    </td>

    <td width="18%">
      <div id="autocomplete_dbname"> 
        <label for="dt_input_dbname">Dbname: </label><input id="dt_input_dbname" type="text" value="">
        <div id="dt_ac_dbname_container"></div>
      </div>

    </td>

    <td width="18%px">
    </td>

    <td width="18%px">
    </td>

    <td width="18%px">
    </td>

  </tr>
<table>

<div id="xml"></div>