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
	  
	      var onFailure = function (msg) {
	        alert(msg);
	        
	        this.resetCellEditor();
	        this.fireEvent(\"editorRevertEvent\",
	          {editor:this._oCellEditor, oldData:oldData, newData:newData}
	          );
	        
	      };
	      
	      if(this._oCellEditor.isActive) {
	        var newData = this._oCellEditor.value;
	        var oldData = this._oCellEditor.record.getData(this._oCellEditor.column.key);
	        
	        if(this._oCellEditor.validator) {
	          newData = this._oCellEditor.validator.call(this, newData, oldData);
	          this._oCellEditor.value = newData;
	          if(newData === null ) {
	            onFailure('validation');
	            return;
	          }
	        }
	        
	        var act = 'manage-db.php';
	        var postdata = 'upfield=' + this._oCellEditor.column.key + '&newvalue=' + escape(newData) + myBuildUrl.call(this, this._oCellEditor.record);
	        
	        YAHOO.util.Connect.asyncRequest(
	          'POST',
	          act,
	          {
	            success: function (o) {
	              
	              if ( o.responseText == 'OK' ) {
	                this._oRecordSet.updateRecordValue(this._oCellEditor.record, this._oCellEditor.column.key, this._oCellEditor.value);
	                this.formatCell(this._oCellEditor.cell.firstChild);
	                this.resetCellEditor();
	                this.fireEvent(\"editorSaveEvent\",
	                  {editor:this._oCellEditor, oldData:oldData, newData:newData}
	                  );
	                
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

	function create_listener(){
	 	$this->buffer .= "YAHOO.example.DynamicData = function() {
	 	  var myColumnDefs = [";
	 	  $boucle=1;
	 	  $editable_item = array();
	 	  $deletable_item = array();
	 	  
	 	  foreach ($this->item_list as $item => $attributes) {
	 	    
	 	    if ( strstr($attributes, 'editor:') ) {$editable_item[] = $item;}
	 	    if ( $item == "delete" ){
	 	      $delete_items = explode("|", $attributes);
	 	      $this->buffer .="{key:'delete', label:' ',formatter:function(elCell){
	 	      elCell.innerHTML = '<img src=\"".$delete_items[0]."\" title=\"".$delete_items[1]."\" height=\"20\" width=\"20\"/>';
	 	      elCell.style.cursor = 'pointer';
	 	      }
	 	      }";
	 	      $deletable_item[] = $attributes;
	 	    }
	 	    else{
	 	      $temp_attributes = preg_replace('/(.*), parser:"number"/', '$1', $attributes);
	 	      $this->buffer .= '{key:"'.$item.'", label:"'.$item.'", '.$temp_attributes.'}'."";
	 	    }
	 	    if ( $boucle < sizeof($this->item_list)){
	 	      $this->buffer .= ",\n";
	 	    }
	 	    $boucle++;
	 	  }
	 	  
	 	  $this->buffer .= "];
	 	  
	 	  
	 	  myDataSource = new YAHOO.util.DataSource(\"".$this->ajax_info['url']."\");";
	 	    if ( $this->ajax_info['method'] == "post" ){
	 	      $this->buffer.="myDataSource.connMethodPost = true;";
	 	    }
	 	    else{
	 	      $this->buffer.="myDataSource.connMethodPost = false;";
	 	    }
	 	    
	 	    $this->buffer.='
	 	    myDataSource.responseType = YAHOO.util.DataSource.TYPE_JSON;
	 	    
	 	    myDataSource.responseSchema = {
	 	    resultsList: "'.$this->info['root'].'",
	 	    fields: ['."\n";
	 	    
	 	    $boucle=1;
	 	    foreach ($this->item_list as $item => $attributes) {
	 	      if ( $item != "delete" ){
	 	        $temp_attributes = preg_replace('/.*(parser:"number")/', '$1', $attributes);
	 	        $this->buffer .= '	                {key:"'.$item.'", '.$temp_attributes.'}';
	 	        //$this->buffer .= '	                {key:"'.$item.'"}';
	 	        if ( $boucle < sizeof($this->item_list)){
	 	          $this->buffer .= ",\n";
	 	        }
	 	      }
	 	      $boucle++;
	 	    } 
	 	    
	 	    
	 	    $this->buffer.="],\n
	 	    metaFields: { 
	 	      totalRecords: \"totalRecords\"
	 	    } 
	 	    };";
	 	    
	 	    $this->buffer.="
	 	    
	 	    var myConfigs = {
	 	      initialRequest: \"method=json&sort=".$this->info['sort']."&dir=".$this->info['sortdir']."&startIndex=".$this->info['startindex']."&results=".$this->info['maxrows']."\",
	 	      dynamicData: true,
	 	      sortedBy : {key:\"".$this->info['sort']."\", dir:YAHOO.widget.DataTable.CLASS_ASC},
	 	      paginator: new YAHOO.widget.Paginator({ rowsPerPage:".$this->info['maxrows']." })
	 	    };
        ";



				$this->buffer.="
	 	    
	 	    myDataTable = new YAHOO.widget.DataTable(\"xml\", myColumnDefs, myDataSource, myConfigs);
	 	    
	 	    myDataTable.handleDataReturnPayload = function(oRequest, oResponse, oPayload) {
	 	      oPayload.totalRecords = oResponse.meta.totalRecords;
	 	      return oPayload;
	 	    }
	 	    
	 	    return {
          ds: myDataSource,
          dt: myDataTable
        };

	 	    ";
	 	    
	 	    
	 	$this->buffer .= "}();";
	}
	
	function create_listener2 (){
	  $this->buffer .="

	    YAHOO.util.Event.addListener(window, \"load\", function() { 

	        var Dom = YAHOO.util.Dom, Event = YAHOO.util.Event;
	        

	        ";	        

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
	            $boucle=1;
	            $editable_item = array();
	            $deletable_item = array();
	            
	            foreach ($this->item_list as $item => $attributes) {
	              
	              if ( strstr($attributes, 'editor:') ) {$editable_item[] = $item;}
	              if ( $item == "delete" ){
	                $delete_items = explode("|", $attributes);
	                $this->buffer .="{key:'delete', label:' ',formatter:function(elCell){
	                  elCell.innerHTML = '<img src=\"".$delete_items[0]."\" title=\"".$delete_items[1]."\" height=\"20\" width=\"20\"/>';
	                  elCell.style.cursor = 'pointer';
	                  }
	                }";
	                $deletable_item[] = $attributes;
	              }
	              else{
	                $temp_attributes = preg_replace('/(.*), parser:"number"/', '$1', $attributes);
	                $this->buffer .= '{key:"'.$item.'",'.$temp_attributes.'}'."";
	              }
	              if ( $boucle < sizeof($this->item_list)){
	                  $this->buffer .= ",\n";
	              }
	              $boucle++;
	            } 

	            $this->buffer.="\n];\n
	            
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
	              resultsData: "'.$this->info['root'].'",
	              fields: ['."\n";
	            
	            $boucle=1;
	            foreach ($this->item_list as $item => $attributes) {
	              if ( $item != "delete" ){
	                $temp_attributes = preg_replace('/.*(parser:"number")/', '$1', $attributes);
	                $this->buffer .= '	                {key:"'.$item.'", '.$temp_attributes.'}';
	                //$this->buffer .= '	                {key:"'.$item.'"}';
	                if ( $boucle < sizeof($this->item_list)){
	                  $this->buffer .= ",\n";
	                }
	              }
	              $boucle++;
	            } 

	            
	            $this->buffer.="],\n
	            metaFields: { 
	              totalRecords: \"totalRecords\", 
	              paginationRecordOffset : \"startIndex\", 
	              paginationRowsPerPage : \"pageSize\", 
	              sortKey: \"sort\", 
	              sortDir: \"dir\" 
	              } 
	            };";
	            
	            
	            /*
	            $this->buffer.="]
	            };\n";
	            */
	            
	            $this->buffer.="
	            
	            var myConfigs = {
	            initialRequest: \"sort=id&dir=asc&startIndex=0&results=25\", // Initial request for first page of data
	            dynamicData: true, // Enables dynamic server-driven data
	            sortedBy : {key:\"name\", dir:YAHOO.widget.DataTable.CLASS_ASC}, // Sets UI initial sort arrow
	            paginator: new YAHOO.widget.Paginator({ rowsPerPage:10 }) // Enables pagination 
	            };

	            
	            myDataTable = new YAHOO.widget.DataTable(\"xml\", myColumnDefs, myDataSource, myConfigs);
	            
	            myDataTable.handleDataReturnPayload = function(oRequest, oResponse, oPayload) {
	              oPayload.totalRecords = oResponse.meta.totalRecords;
	              return oPayload;
	            }
	            

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
	                ";
	            for ($i=0; $i<sizeof($editable_item); $i++){
	              $this->buffer .= "if (column.key == '".$editable_item[$i]."') {
	                  this.onEventShowCellEditor(ev);
	                }
	              ";
	            }
	            
	            for ($i=0; $i<sizeof($deletable_item); $i++){
	              
	            }
	            $this->buffer.="
	                if (column.key == 'delete') {
	                  
	                  var record = this.getRecord(target);
	                  
	                  if (confirm()) {
	                    
	                    var record = this.getRecord(target);
	                    var act = '';
	                    var postdata = 'action=delete' + myBuildUrl.call(this, record);
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
	
	function attr_add($item, $value){
	  $this->info[$item]=$value;
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





