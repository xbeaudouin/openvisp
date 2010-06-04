<?php

class AJAX_YUI
{

	protected $db_link;

	function __construct ($db_link)
	{
		$this->db_link = $db_link;
	}
	
	function start($primary_key = NULL){
	  $this->buffer = "
    <script type=\"text/javascript\">    
    var myBuildUrl".$this->info['data_div']." = function(datatable,record) {
      var url = '';
      var cols = datatable.getColumnSet().keys;
      for (var i = 0; i < cols.length; i++) {
        if (cols[i].key == '".$primary_key."') {
          url += '&' + cols[i].key + '=' + escape(record.getData(cols[i].key));
        }
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

		$this->buffer .= ' DynamicData'.$this->info['data_div'].' = function() {';

	 	$this->buffer .= "
	 	  var myColumnDefs".$this->info['data_div']." = [";
	 	  $boucle=1;
	 	  $editable_item = array();
	 	  $deletable_item = array();
	 	  
	 	  foreach ($this->item_list as $item => $attributes) {

				if ( !isset($attributes['display']) ) { $attributes['display'] = TRUE;}

				if ( $attributes['display'] == TRUE ){

					if ( preg_match("/children.*/", $item) ){
						$item = "children";
					}

					switch ($item){

					case "delete":
						//$delete_items = explode("|", $attributes);
						$this->buffer .="{key:'delete', label:'', formatter:function(elCell){
  	 	        elCell.innerHTML = '<img src=\"../images/ico-exit.png\" title=\"delete item\" height=\"20\" width=\"20\"/>';
	   	        elCell.style.cursor = 'pointer';
	 	          }
	 	        }";
						//	 	      $deletable_item[] = $attributes;
						break;

					case "children":
						
						$this->buffer .= '{ ';
						if ( isset($attributes['label']) ) { $this->buffer .= 'label:"'.$attributes['label'].'",'; }
						$this->buffer .= "children: [ \n";
						
						$total_child = sizeof($attributes) - 3;
						$boucle_child = 0;
						foreach ($attributes as $child_array){
							if ( is_array($child_array) ){
								$this->buffer .= '  { key:"'.$child_array['key'].'"';

								if ( ! empty($child_array['sortable']) ){
									$this->buffer .= ', sortable:'.$child_array['sortable'];
								}

								if ( ! empty($child_array['resizeable']) ){
									$this->buffer .= ', resizeable:'.$child_array['resizeable'];
								}
								
								if ( ! empty($child_array['label']) ) {
									//$child_array['label'];
									$this->buffer .= ', label:"'.$child_array['label'].'"';
								}

								$this->buffer .= '} ';

								if ( $boucle_child < $total_child ) { $this->buffer .= ","; }
								$boucle_child++;

							}
						}

						$this->buffer .= '
	                    ] 
	 
	                } 
            ';
						break;

					default:
						if ( empty($attributes['label']) ) { $attributes['label']="";}
						$this->buffer .= '{key:"'.$item.'", label:"'.$attributes['label'].'"';
						if ( isset($attributes['formatter']) ) {$this->buffer .= ", formatter:".$attributes['formatter'];}
						if ( isset($attributes['editor']) ) {$editable_item[] = $item; $this->buffer .= ", editor:\"".$attributes['editor']."\"";}
						if ( isset($attributes['dropdownOptions']) ) {
							$this->buffer .= ', editor: new YAHOO.widget.DropdownCellEditor({ dropdownOptions: '.$attributes['dropdownOptions'].' })';
						}
						if ( isset($attributes['radioOptions']) ) {
							$this->buffer .= ", editor: new YAHOO.widget.RadioCellEditor({
                 radioOptions: ".$attributes['radioOptions']['items'].",disableBtns:true,
                 asyncSubmitter: function (callback, newValue) {
                   var record = this.getRecord(),
                   column = this.getColumn(),
                   oldValue = this.value,
                   datatable = this.getDataTable();

                   YAHOO.util.Connect.asyncRequest(
                     'POST',
                     '".$attributes['radioOptions']['url']."', 
                       {
                         success:function(o) {
                           var r = YAHOO.lang.JSON.parse(o.responseText);
                           if (r.replyCode == 201) {
                             callback(true, r.records[column.key]);
                           } else {
                             alert(r.replyText);
                             alert(r.log);
                             callback();
                           }
                         },
                        failure:function(o) {
                          alert(o.statusText);
                          callback();
                        },
                        scope:this
                      },
                   'column=' + column.key + '&newValue=' + 
                   escape(newValue) + '&oldValue=' + escape(oldValue) + '&".$attributes['radioOptions']['url_param']."' +
                   myBuildUrl".$this->info['data_div']."(datatable,record)
                   );                                              
                 }
               })";

						}
						if ( isset($attributes['sortable']) ) {$this->buffer .= ", sortable:".$attributes['sortable'];}

						$this->buffer .= '} ';

					}

					if ( $boucle < sizeof($this->item_list)){
						$this->buffer .= ",\n";
					}
				
				}


	 	    $boucle++;
	 	  }
	 	  
	 	  $this->buffer .= "];
	 	  
	 	  
	 	  myDataSource".$this->info['data_div']." = new YAHOO.util.DataSource(\"".$this->ajax_info['url']."\");";
	 	    if ( $this->ajax_info['method'] == "post" ){
	 	      $this->buffer.="myDataSource".$this->info['data_div'].".connMethodPost = true;";
	 	    }
	 	    else{
	 	      $this->buffer.="myDataSource".$this->info['data_div'].".connMethodPost = false;";
	 	    }
	 	    
	 	    $this->buffer.='
	 	    myDataSource'.$this->info['data_div'].'.responseType = YAHOO.util.DataSource.TYPE_JSON;
	 	    
	 	    myDataSource'.$this->info['data_div'].'.responseSchema = {
	 	    resultsList: "'.$this->info['root'].'",
	 	    fields: ['."\n";
	 	    
	 	    $boucle=1;
	 	    foreach ($this->item_list as $item => $attributes) {

					if ( preg_match("/children.*/", $item) ){
						$item = "children";
					}


					switch ($item){

					case "children":
						$total_child = sizeof($attributes);
						$boucle_child = 0;
						foreach ($attributes as $child_array){
							if ( is_array($child_array) ){
								$this->buffer .= '  { key:"'.$child_array['key'].'"';
								if ( ! empty($child_array['parser']) ) { $this->buffer .= ', parser:"'.$child_array['parser'].'"'; }
								$this->buffer .= '} ';
								
								if ( $boucle_child < $total_child ) { $this->buffer .= ","; }
								$boucle_child++;
							}
					}						
						break;

					default:
						$this->buffer .= '	                {key:"'.$item.'"';
						if ( isset($attributes['parser']) ) { $this->buffer .= ', parser:"'.$attributes['parser'].'"';}
						$this->buffer .= '}';
						
	 	        if ( $boucle < sizeof($this->item_list)){
	 	          $this->buffer .= ",\n";
	 	        }
					

	 	      }
	 	      $boucle++;
	 	    }
				
	 	    
	 	    $this->buffer.='],
	 	    metaFields: { 
	 	      totalRecords: "totalRecords",
          domainName: "domainName",
          paginationRecordOffset : "startIndex", 
	        paginationRowsPerPage : "recordsReturned", 
	        sortKey: "sort", 
	        sortDir: "dir"
         ';


	 	    $this->buffer .= '
          }
	 	    };';
	 	    
	 	    $this->buffer .= "
	 	    
	 	    var myConfigs".$this->info['data_div']." = {
          dynamicData: true,
          sortedBy : {key:\"".$this->info['sort']."\", dir:YAHOO.widget.DataTable.CLASS_ASC},
          paginator: new YAHOO.widget.Paginator({
            containers : [\"".$this->info['nav_div']."\"],
            template : \"{PreviousPageLink} {CurrentPageReport} {NextPageLink} {RowsPerPageDropdown}\",
            pageReportTemplate : \"Showing items {startIndex} - {endIndex} of {totalRecords}\",
            rowsPerPageOptions : [5,10,25,50,100],
            rowsPerPage:".$this->info['maxrows']."
          }),
	 	      initialRequest: ";

				$buffer_add = "\"method=json&sort=".$this->info['sort'];

				$buffer_add .= "&dir=".$this->info['sortdir'];
				$buffer_add .= "&startIndex=".$this->info['startindex']."&results=".$this->info['maxrows'];

				if ( isset($this->ajax_info['params']) ) {
						foreach ($this->ajax_info['params'] as $param => $value) {

							$buffer_add .= "&".$param."=".$value;
							$boucle++;
						} 
					}
				$buffer_add .= "\"";


				$this->buffer .= "$buffer_add
        };
        ";



				$this->buffer .= "
          
        myDataTable".$this->info['data_div']." = new YAHOO.widget.DataTable(\"".$this->info['data_div']."\", myColumnDefs".$this->info['data_div'].", myDataSource".$this->info['data_div'].", myConfigs".$this->info['data_div'].");

        myDataTable".$this->info['data_div'].".subscribe('cellClickEvent', myDataTable".$this->info['data_div'].".onEventShowCellEditor);

        myDataTable".$this->info['data_div'].".subscribe('cellClickEvent', function(oArgs){
					var target = oArgs.target;
					var column = myDataTable".$this->info['data_div'].".getColumn(target);
        ";

	 	    foreach ($this->item_list as $item => $attributes) {
	 	      if ( isset($attributes['link']) ){
						$this->buffer .= "

						if ( column.key == '$item') {
							var record = this.getRecord(target);
";

						if ( $item == "delete" ){
							$this->buffer .= "

							if (confirm('Are you sure?')) {

							  YAHOO.util.Connect.asyncRequest(
                                               \"POST\",
                                               '".$attributes['link']."',
                                               {
                                                 success:function(o) {
                                                   var r = YAHOO.lang.JSON.parse(o.responseText);
                                                   if (r.replyCode == 201) {
                                                     this.deleteRow(target);
                                                   } else {
                                                     alert(r.replyText + r.log);
                                                     callback();
                                                   }
                                                 },
                                                failure:function(o) {
                                                  alert(o.statusText);
                                                },
                                                scope:this
                                              },
                                           '".$attributes['key_item']."=' + record.getData('".$attributes['key_item']."') + '&".$attributes['url_param']."'
                                         ); 

							}
							else{
							  this.onEventShowCellEditor(oArgs);
							}
                                               ";
						}

						$this->buffer .= "
					}
            ";
					}

	 	      $boucle++;
	 	    } 



				$this->buffer.="
        });


	 	    myDataTable".$this->info['data_div'].".handleDataReturnPayload = function(oRequest, oResponse, oPayload) {
	 	      oPayload.totalRecords = oResponse.meta.totalRecords;
	 	      return oPayload;
	 	    }
	 	    
	 	    return {
          ds: myDataSource".$this->info['data_div'].",
          dt: myDataTable".$this->info['data_div']."
        };

	 	    ";
	 	    
	 	    
				$this->buffer .= "}();";

	}

	function create_search(){

	  foreach ($this->search_form as $item => $value) {

			$this->buffer .= "
       YAHOO.util.Event.addListener(window, \"load\", function() { 

       	 var Dom = YAHOO.util.Dom, Event = YAHOO.util.Event;

       	 //'datatable=yes&zip=' + query + '&query=' + Dom.get('dt_input').value + queryString,


       	 var get".ucfirst($value['name'])." = function(query) { 
       		 myDataSource.sendRequest('".$value['name']."=' + Dom.get('dt_input_".$value['name']."').value,
						 myDataTable.onDataReturnInitializeTable, myDataTable); 
       	  }; 

        ";
		}

		$this->buffer .= "
         Event.onDOMReady(function() { 
         //				zip = Dom.get('dt_input_zip').value; 
	    ";

	  foreach ($this->search_form as $item => $value) {
			$this->buffer .= "
			     var oACDS_".$value['name']." = new YAHOO.widget.DS_JSFunction(get".ucfirst($value['name']).");
			     oACDS_".$value['name'].".queryMatchContains = true;

			     var oAutoComp".ucfirst($value['name'])." = new YAHOO.widget.AutoComplete(\"dt_input_".$value['name']."\",\"dt_ac_".$value['name']."_container\", oACDS_".$value['name'].");
			     oAutoComp".ucfirst($value['name']).".minQueryLength = ".$value['minQueryLength'].";
       ";

		}

    $this->buffer .= "})
})
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

	function add_function($function_name, $function_content){

		$this->buffer .= 'this.'.$function_name.' = '.$function_content."\n\n"; 

	}
	
	function generate_search_form($size){
	  foreach ($this->search_form as $item => $value) {
	    //$value['name'];
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





