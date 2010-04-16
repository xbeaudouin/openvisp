<?php

class AJAX_YUI
{

	protected $db_link;

	function __construct ($db_link)
	{
		$this->db_link = $db_link;
	}
	
	function start(){
	  $this->buffer="
	  <script type=\"text/javascript\">
	  
	  var myBuildUrl = function(record) {
	    var url = '';
	    var cols = this.getColumnSet().keys;
	    for (var i = 0; i < cols.length; i++) {
	      url += '&' + cols[i].key + '=' + escape(record.getData(cols[i].key));
	    }
	    return url;
	  };
	  ";
	}
	
	function create_celleditor(){
	  
	  $this->buffer .="
	  
	    YAHOO.widget.DataTable.prototype.saveCellEditor = function() {
	  
	  
	      // ++++ this is the inner function to handle the several possible failure conditions
	      var onFailure = function (msg) {
	        alert(msg);
	        
	        // --------      on failure section
	        this.resetCellEditor();
	        this.fireEvent(\"editorRevertEvent\",
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
	                this.fireEvent(\"editorSaveEvent\",
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
	    ";
	}
	
	function create_listener (){
	  $this->buffer .="
	    YAHOO.util.Event.addListener(window, \"load\", function() { 

	        var Dom = YAHOO.util.Dom, Event = YAHOO.util.Event;
	        

	        ";	        
	        /*
	        var getDomainname = function(query) { 
	          myDataSource.sendRequest('domain_name=' + Dom.get('dt_input_domainname').value + '&db_name=' + Dom.get('dt_input_dbname').value,
	            myDataTable.onDataReturnInitializeTable, myDataTable); 
	        }; 
	        
	        var getDbname = function(query) { 
	          myDataSource.sendRequest('domain_name=' + Dom.get('dt_input_domainname').value + '&db_name=' + Dom.get('dt_input_dbname').value,
	            myDataTable.onDataReturnInitializeTable, myDataTable); 
	        }; ";
	        */

	        $boucle=1;
	        $request_url="";
	        foreach ($this->search_form as $item => $value) {
	          $request_url .= $value['name']."=' + Dom.get('dt_input_".$value['name']."').value";
	          if ( $boucle < sizeof($this->search_form ) ){
	            $request_url .= " + '&";
	          }
	        }
	        
	        foreach ($this->search_form as $item => $value) {
	          $this->buffer .=" var get".ucfirst($value['name'])." = function(query) { 
	            myDataSource.sendRequest('".$request_url.",
	            myDataTable.onDataReturnInitializeTable, myDataTable); 
	            }; ";
	        }
	        
	        $this->buffer .="
	        Event.onDOMReady(function() {"; 
	         
	            /*
	            var oACDS_domainname = new YAHOO.widget.DS_JSFunction(getDomainname); 
	            oACDS_domainname.queryMatchContains = true; 
	            
	            var oAutoCompDomain = new YAHOO.widget.AutoComplete(\"dt_input_domainname\",\"dt_ac_domainname_container\", oACDS_domainname); 
	            oAutoCompDomain.minQueryLength = 3;
	            
	            var oACDS_dbname = new YAHOO.widget.DS_JSFunction(getDbname); 
	            oACDS_dbname.queryMatchContains = true; 
	            
	            var oAutoCompDbname = new YAHOO.widget.AutoComplete(\"dt_input_dbname\",\"dt_ac_dbname_container\", oACDS_dbname); 
	            oAutoCompDbname.minQueryLength = 3; 
	            */

	            foreach ($this->search_form as $item => $value) {
	              $this->buffer .="var oACDS_".$value['name']." = new YAHOO.widget.DS_JSFunction(get".ucfirst($value['name']).");
	              oACDS_".$value['name'].".queryMatchContains = true;	    
	              
	              var oAutoComp".$value['name']." = new YAHOO.widget.AutoComplete(\"dt_input_".$value['name']."\",\"dt_ac_".$value['name']."_container\", oACDS_".$value['name'].");
	              oAutoComp".$value['name'].".minQueryLength = ".$value['minQueryLength'].";
	              ";
	            }
	            
	            $this->buffer .="
	            var formatUrl = function(elCell, oRecord, oColumn, sData) { 
	              elCell.innerHTML = \"<a href='\" + oRecord.getData(\"ClickUrl\") + \"' target='_blank'>\" + sData + \"</a>\"; 
	            }; 
	            
	            var myColumnDefs = [";
	            $boucle=0;
	            foreach ($this->item_list as $item => $attributes) {
	              $this->buffer .= '{key:"'.$item.'",'.$attributes.'}';
	              if ( $boucle < sizeof($this->item_list)){
	                  $this->buffer .= ",";
	              }
	                $boucle++;
	            } 
	            
	            /*
	            {key:\"name\", sortable:true},
	            {key:\"aliases\", sortable:true},
	            {key:\"quota_aliases\", sortable:true},
	            {key:\"mailboxes\", sortable:true},
	            {key:\"quota_mailboxes\", sortable:true},
	            {key:\"diskspace_mailboxes\", sortable:true},
	            {key:'pdf',label:' ',formatter:function(elCell) {
	              elCell.innerHTML = '<img src=\"../images/pdf.png\" title=\"Display PDF\" width=\"20\"/>';
	              elCell.style.cursor = 'pointer';
	              }
	            },
	            {key:'delete',label:' ',formatter:function(elCell) {
	              elCell.innerHTML = '<img src=\"../images/ico-exit.png\" title=\"delete row\" height=\"20\" width=\"20\"/>';
	              elCell.style.cursor = 'pointer';
	              }
	            }
	            */
	            
	            $this->buffer.="];
	            
	            myDataSource = new YAHOO.util.DataSource(\"".$this->ajax_info['url']."\");";
	            if ( $this->ajax_info['method'] == "post" ){
	              $this->buffer.="myDataSource.connMethodPost = true;";
	            }
	            else{
	              $this->buffer.="myDataSource.connMethodPost = false;";
	            }

	            $this->buffer.='
	            myDataSource.responseType = YAHOO.util.DataSource.TYPE_XML;
	            
	            myDataSource.responseSchema = {
	            resultNode: "'.$this->xmlinfo['root'].'",
	            fields: [';
	            
	            $boucle=0;
	            foreach ($this->item_list as $item => $attributes) {
	              $this->buffer .= '"'.$item.'"';
	              if ( $boucle < sizeof($this->item_list)){
	                $this->buffer .= ",";
	              }
	              $boucle++;
	            } 

	            $this->buffer.="]
	            };";


	            $this->buffer.="
	            myDataTable = new YAHOO.widget.DataTable(\"xml\", myColumnDefs, myDataSource);
	            
	            this.highlightEditableCell = function(oArgs) {
	              var elCell = oArgs.target;
	              if(YAHOO.util.Dom.hasClass(elCell, \"yui-dt-editable\")) {
	                this.highlightCell(elCell);
	              }
	            };
	            
	            this.myDataTable.subscribe(\"cellMouseoverEvent\", this.highlightEditableCell);
	            this.myDataTable.subscribe(\"cellMouseoutEvent\", this.myDataTable.onEventUnhighlightCell);
	            
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
	                      \"POST\",
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
	            
	            
	            // Hook into custom event to customize save-flow of \"radio\" editor
	            this.myDataTable.subscribe(\"editorUpdateEvent\", function(oArgs) {
	                if(oArgs.editor.column.key === \"active\") {
	                  this.saveCellEditor();
	                }
	            });
	            
	            this.myDataTable.subscribe(\"editorBlurEvent\", function(oArgs) {
	                this.cancelCellEditor();
	            });
	            
	            
	        }); 
	    });
	 
	    ";
	}
	
	function end(){
	  print $this->buffer;
	  print "</script>";
	}
	
	function item_add($item_list){
	  $this->item_list=$item_list;
	}
	
	function ajax_info($item_list){
	  $this->ajax_info=$item_list;
	}
	
	function xml_attr_add($item, $value){
	  $this->xmlinfo[$item]=$value;
	}
	
	function add_search_form($item_list){
	  $this->search_form=$item_list;
	}
	
	function generate_search_form($size){
	  foreach ($this->search_form as $item => $value) {
	    $value['name'];
	    print '
	      <td width="'.$size.'">
	        <div id="autocomplete_'.$value['name'].'">
	        <label for="dt_input_'.$value['name'].'">'.$value['form_inputname'].'</label><input id="dt_input_'.$value['name'].'" type="text" value="">
	        <div id="dt_ac_'.$value['name'].'_container"></div>
	        </div>
	      </td>';
	  }

	}
	
}





