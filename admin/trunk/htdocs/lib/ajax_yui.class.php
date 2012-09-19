<?php
/**
 * AJAX_YUI
 *
 * Copyright (c) 2012,
 * Nicolas GORALSKI for the association Kazar
 * All right reserved
 *
 * @copyright 2012 Kazar, the authors
 *
 */

/**
 * This class create and manage all yui code block
 * @package ova-yui
 */

class AJAX_YUI
{

  /**
   * 
   */

  protected $db_link;

  function __construct ($db_link){
    $this->db_link = $db_link;
  }


  function add_search_form($item_list){
    $this->search_form=$item_list;
  }

  /**
   * This method add to the current yui js function block a new function definition
   *
   * @param text $function_name the name of the new function
   * @param text $function_content the code of the new function
   *
   */

  function add_function($function_name, $function_content){
    $this->js_function .= 'var '.$function_name.' = '.$function_content."\n\n"; 
  }

  /**
   * This metod generate a textarea form in the top menu
   *
   * @param int $size the size of the search form box
   *
   */
  public function generate_search_form($size){
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

/*
  Global function not specific to yui but to ova project.
*/

  function item_add($item_list){
    $this->item_list=$item_list;
  }

  function ajax_info($item_list){
    $this->ajax_info=$item_list;
  }

  function attr_add($item, $value){
    $this->info[$item]=$value;
  }


  /**
   * This method initialize the minimum requirement for creating yui objects
   * 
   * @param text $primary_key the name that will identify the yui object in case of multiple yui object in the page
   *
   */

  function start ($primary_key = NULL){
    $this->js_function ="";
    $this->name=$primary_key;
    $this->buffer = " ";
    $this->cnt_lib = 0;
    $this->loaded_lib = array();
    $this->load_lib = "<script>";


    // Make YUI load locally the lib and not from yahoo web site

    $this->load_lib .= '

    YUI_config = {
    groups: {
        gallery: {
            base    : \'/lib/yui/gallery/\', // Adjust as necessary.
            patterns: {
                \'gallery-\'   : {},
                \'gallerycss-\': { type: \'css\' }
            },
            modules: {
                \'gallery-paginator\': {
                    path     : \'gallery-paginator/gallery-paginator.js\',
                    skinnable: true
                }
            }
        }
    }
    };';


		$this->load_lib .= "\n\n\n\n
    YUI().use(";

  }

  /**
   * This method allow to add a new library to load
   * 
   * @param text $lib library to load inside the yui js code
   *
   */

  function load_yui_lib ($lib){
    $this->cnt_lib++;
    if ( !in_array($lib,$this->loaded_lib)){
      $this->loaded_lib[]=$lib;
      if ($this->cnt_lib > 1 ){$this->load_lib.=",";}
      $this->load_lib.='"'.$lib.'"';
    }
    
  }

  /**
   * This method end the structure of the yui js code and insert it inside the result page
   */

  function end(){
    $this->load_yui_lib("datasource-io");
    $this->load_yui_lib("autocomplete");
    $this->load_yui_lib("event");
    $this->load_yui_lib("node");

    print $this->load_lib;
    print ", function (Y) {\n";


    print $this->buffer."\n";

    print "

    var ACDomain = new Y.AutoComplete({
      inputNode: '#DomainSearch',
      resultListLocator: 'records',
      resultTextLocator: 'domain',
      source : '".$_SESSION['absoluteuri']."ajax/domain/domain_search.php?domain={query}',
      render   : true
    });

    var clean = function (e){
      if ( e.target.get(\"value\") == \"Search\" ){
        e.target.set(\"value\", \"\");
      }
    };

    var fill = function (e){
      if ( e.target.get(\"value\") == \"\" ){
        e.target.set(\"value\", \"Search\");
      }
    };

    var select = function (e){
      CurDomain.setContent(\"Current Domain \" + e.target.get(\"value\"));
      DS_searchDomain.sendRequest({
        request:\"wdomain=\" + e.target.get(\"value\")
      });
    }

    var CurDomain = Y.one('#CurDomain'),
        IDomainSearch = Y.one('#DomainSearch');

    IDomainSearch.on('focus', clean);
    IDomainSearch.on('blur', fill);
    ACDomain.after('select', select);

    var DS_searchDomain = new Y.DataSource.IO({source:'".$_SESSION['absoluteuri']."/ajax/manage_session.php?', ioConfig: { method: 'POST'}});

    ";

    print $this->js_function;

    print "})\n";

    //		print "alert('NGO' + totalRecords);";

    print "</script>\n";
  }

/*
  YUI 3 lib
*/


  /**
   * This method create a autocomplete search field
   * 
   * @param text $options ???
   *
   */

  function create_search_field_autocomplete($options){
    $this->load_yui_lib("datasource-io");
    $this->load_yui_lib("autocomplete");
    $this->load_yui_lib("event");
    $this->load_yui_lib("node");

    $this->buffer .= "\n\n";


  }

  /**
   * This method create a datasource to extract data from
   * 
   * @param array $ds_param the array contains all parameters required to configure the datasource
   *
   */

  function create_datasource($ds_param){
    $this->load_yui_lib("datasource-io");
    $this->load_yui_lib("datasource-jsonschema");
    $this->load_yui_lib("gallery-paginator");

    $this->buffer .= "\n\n";

    $add_request = "";
    if ( isset($ds_param['params']) && gettype($ds_param['params']) == "array" ){
      foreach ( $ds_param['params'] as $key => $value ){
        if ( $add_request != "" ){ $add_request .= "&";}
        $add_request .= "$key=$value";
      }
    }

    $this->buffer .="\n   /**";
    $this->buffer .="\n   Method returns the column key (or name) from the provided TD node";
    $this->buffer .="\n   @method getColumn";
    $this->buffer .="\n   @param target {Node} the TD node for the requested column";
    $this->buffer .="\n   @returns {String} Column key or name";
    $this->buffer .="\n   **/";
    $this->buffer .="\n   Y.DataTable.prototype.getCellColumnKey = function (node) {";
    //$this->buffer .="\n        alert('NGO2 ' + node.get('className'));var classRE = new RegExp( this.getClassName('col') + '-(\w+) .*'),";
    $this->buffer .="\n          var classRE = new RegExp( this.getClassName('col') + \"-(\\\w*)\"),";
    $this->buffer .="\n          cname = (node.get('className').match(classRE) || [])[1];";
    //$this->buffer .="\n        alert('NGI3 ' + classRE + ' ## ' + node.get('className') + ' || ' + cname + ' // ' + this.getClassName('col') + '-(\\w+)');return cname;";
    $this->buffer .="\n          return cname;";
    $this->buffer .="\n    };";
    $this->buffer .="\n";

  // Y.DataTable.prototype.getCellColumnKey = function (node) {
  //       var classRE = new RegExp( this.getClassName('col') + '-(\\w+)'),
  //         cname = (node.get('className').match(classRE) || [])[1];
  //       return cname;


    $this->buffer .= "

    function sendRequest_".$ds_param['name']."(){
      table_".$ds_param['name'].".datasource.load({
        request: \"".$add_request."&startIndex=\"+".$ds_param['name']."_pg.getStartIndex() + \"&results=\" + ".$ds_param['name']."_pg.getRowsPerPage()
      })

    }
    ";

    $this->buffer .= "\n\n";

    $this->buffer .= "  var DS_".$ds_param['name']." = new Y.DataSource.IO({source:\"".$ds_param['url'];
    if ( $ds_param['method'] == "get" ) {$this->buffer .= "?";}
    $this->buffer .= "\", ioConfig: { method: '".$ds_param['method']."'}});

    DS_".$ds_param['name'].".plug(Y.Plugin.DataSourceJSONSchema, {
    schema: {
      resultListLocator: \"records\",
      metaFields: {
        rcode: 'replyCode',
        rtext: 'replyText',
        totalRecords: 'totalRecords'
      },
      resultFields: [\n";


    $boucle=1;
    foreach ($ds_param['item_list'] as $item => $attributes) {

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
              if ( ! empty($child_array['parser']) ) { $this->buffer .= ', parser:'.$child_array['parser']; }
              $this->buffer .= '} ';

              if ( $boucle_child < $total_child ) { $this->buffer .= ","; }
              $boucle_child++;
            }
          }
          break;

         default:
            $this->buffer .= '                  {key:"'.$item.'"';
            if ( isset($attributes['parser']) ) { $this->buffer .= ', parser:'.$attributes['parser'];}
            $this->buffer .= '}';

            if ( $boucle < sizeof($ds_param['item_list'])){
              $this->buffer .= ",\n";
            }
      }
      $boucle++;
 	  }


    /*                 $boucle=1; */
    /*                 foreach ($this->item_list as $item => $attributes) { */
    /* 									if ( $item != "delete" ){ */
                        
                        
    /*                     $temp_attributes = preg_replace('/.*(parser:"number")/', '$1', $attributes); */
    /*                     $this->buffer .= '        {key:"'.$item.'", '.$temp_attributes.'}'; */
    /*                     $this->buffer .= '        {key:"'.$item.'"}'; */
    /*                     if ( $boucle < sizeof($this->item_list)){ */
    /*                       $this->buffer .= ",\n"; */
    /*                     } */
    /* 									} */
                      
    /* 									$boucle++; */
    /*                 } */

    $this->buffer .= "\n      ]\n    }\n    });";
    $this->buffer .= "\n\n";

    $this->buffer .="\n     DS_".$ds_param['name'].".RemoteRequest = function( qs_append, rqst_options, success_cback ) {";
    $this->buffer .="\n         var rec      = rqst_options.rec,";
    $this->buffer .="\n             basic_qs = Y.QueryString.stringify({";
    $this->buffer .="\n                 'action_target' : '".$ds_param['action_target']."',";
    $this->buffer .="\n                 ".$ds_param['action_key']."  :  rec.get('".$ds_param['table_pkey']."') || null   // this is the DB primary key";
    $this->buffer .="\n             });";
    $this->buffer .="\n  ";
    $this->buffer .="\n         this.sendRequest({";
    $this->buffer .="\n             request : basic_qs + qs_append,";
    $this->buffer .="\n             on : {";
    $this->buffer .="\n                 success: function(o) {";
    $this->buffer .="\n                     var oargs = o.on.argument,";
    $this->buffer .="\n                         oresp = o.response;";
    $this->buffer .="\n  ";
    $this->buffer .="\n                     if ( oresp.meta.rcode && oresp.meta.rcode<300 )";
    $this->buffer .="\n                         oargs.cback(o);";
    $this->buffer .="\n                     else";
    $this->buffer .="\n                         alert(\"DS sendrequest returned replyCode=\" + oresp.meta.rcode + \" from server, failed!\");";
    $this->buffer .="\n  ";
    $this->buffer .="\n                 },";
    $this->buffer .="\n                 failure : function(o) {";
    $this->buffer .="\n                     alert(\"failed in sync\");";
    $this->buffer .="\n                 },";
    $this->buffer .="\n                 argument : { record:rec, cback:success_cback, rqst_options:rqst_options }";
    $this->buffer .="\n             }";
    $this->buffer .="\n         });";
    $this->buffer .="\n     }";

    $this->buffer .= "\n";




		$this->buffer .= "

  	var ".$ds_param['name']."_pg = new Y.Paginator(
    {
      rowsPerPage: ".$ds_param['params']['results'].",
      rowsPerPageOptions: [1,2,5,10,25,50,100],
      template: '{FirstPageLink} {PreviousPageLink} {PageLinks} {NextPageLink} {LastPageLink} <span class=\"pg-rpp-label\">Rows per page:</span> {RowsPerPageDropdown}'
    });
  	".$ds_param['name']."_pg.render('#".$ds_param['name']."-nav');

    function updatePaginator_".$ds_param['name']."(state)
    {
      this.setPage(state.page, true);
      this.setRowsPerPage(state.rowsPerPage, true);
      this.setTotalRecords(state.totalRecords, true);
      sendRequest_".$ds_param['name']."();
    }
    ".$ds_param['name']."_pg.on('changeRequest', updatePaginator_".$ds_param['name'].", ".$ds_param['name']."_pg);

    DS_".$ds_param['name'].".on('response', function(e)
    {
      ".$ds_param['name']."_pg.setTotalRecords(e.response.meta.totalRecords, true);
      ".$ds_param['name']."_pg.render();
    });

    //        startIndex: ".$ds_param['name']."_pg.getStartIndex(),
    //       resultCount: ".$ds_param['name']."_pg.getRowsPerPage(),

    ";

		$this->buffer .= "\n\n";

  }

/*
  Function create_table
  To create a table with yui object.
  you must link the table with a datasource.
*/


  /**
   * This method create the datatable with yui js
   * 
   * @param array $dt_param the array contains all parameters required to configure the datatable
   *
   */


  function create_datatable($dt_param){
    //$this->load_yui_lib("datatable-datasource");
    $this->load_yui_lib("datatable");
    $this->load_yui_lib("datatype");
    $this->load_yui_lib("dd-plugin");


    $this->buffer .= "       var table_".$dt_param['name']."_RecordModel = Y.Base.create('recmodel', Y.Model, [], {";
    $this->buffer .="\n  ";
    $this->buffer .="\n         sync : function( action, options, callback) {";
    $this->buffer .="\n  ";
    $this->buffer .="\n             var dt = options.dt,";
    $this->buffer .="\n                 dtDS = dt.datasource.get('datasource');";
    $this->buffer .="\n  ";
    $this->buffer .="\n             switch(action) {";
    $this->buffer .="\n  ";
    $this->buffer .="\n             /*---------------------------------------------------------------------------------------------*/";
    $this->buffer .="\n             /*   UPDATE the given record on the server, based on \"Edit\" column click and Panel \"Update\"    */";
    $this->buffer .="\n             /*---------------------------------------------------------------------------------------------*/";
    $this->buffer .="\n  ";
    $this->buffer .="\n                 case 'update':";
    $this->buffer .="\n  ";
    $this->buffer .="\n                     // define the qstring to \"update\" a record, 'ifld' is field name, 'iparam' is new value";
    $this->buffer .="\n                     var qstr =  '&'+Y.QueryString.stringify({";
    $this->buffer .="\n                             iact :      'u',";
    $this->buffer .="\n                             imodid :    options.mod_id,     // this is the DB \"Primary Key\"";
    $this->buffer .="\n                             imodname :  options.mod_name,";
    $this->buffer .="\n                             imoddata :  options.mod_data";
    $this->buffer .="\n                         }),";
    $this->buffer .="\n                         rtn_opts = { rec:this, sync_options:options, sync_callback:callback };";
    $this->buffer .="\n  ";
    $this->buffer .="\n                     //  fire off the remote request to DS, and on \"success\",";
    $this->buffer .="\n                     //    do the function below ... use record.set() to set the data ...";
    $this->buffer .="\n                     dtDS.RemoteRequest( qstr, rtn_opts, function(o){";
    $this->buffer .="\n                         var rdata = o.response.results[0],";
    $this->buffer .="\n                             oargs = o.on.argument,";
    $this->buffer .="\n                             orec  = oargs.record;";
    $this->buffer .="\n                     //";
    $this->buffer .="\n                     //  Put the UPDATED data item into the record, automatically updates the DT";
    $this->buffer .="\n                     //";
    $this->buffer .="\n                         updateStatus('Updated row with mod_id=' + orec.get('mod_id') + ' from server and locally');";
    $this->buffer .="\n  ";
    $this->buffer .="\n                         if ( oargs.rqst_options.sync_callback )";
    $this->buffer .="\n                             oargs.rqst_options.sync_callback( null, rdata );";
    $this->buffer .="\n                         else";
    $this->buffer .="\n                             orec.setAttrs( rdata ); // this works, not ideal (i.e. using callbacks), but it works !!";
    $this->buffer .="\n  ";
    $this->buffer .="\n                     });";
    $this->buffer .="\n  ";
    $this->buffer .="\n                     return;";
    $this->buffer .="\n                     break;";
    $this->buffer .="\n  ";
    $this->buffer .="\n             /*---------------------------------------------------------------------------*/";
    $this->buffer .="\n             /*   DELETE the given record on the server, based on \"Delete\" column click   */";
    $this->buffer .="\n             /*---------------------------------------------------------------------------*/";
    $this->buffer .="\n  ";
    $this->buffer .="\n                 case 'delete':";
    $this->buffer .="\n  ";
    $this->buffer .="\n                     var qstr = '&action=delete',";
    $this->buffer .="\n                         rtn_opts = { rec:this, sync_options:options, sync_callback:callback };";
    $this->buffer .="\n  ";
    $this->buffer .="\n                     dtDS.RemoteRequest( qstr, rtn_opts, function(o) {";
    $this->buffer .="\n                         if ( o ) {";
    $this->buffer .="\n                             var rdata = o.response.results,";
    $this->buffer .="\n                                 oargs = o.on.argument,";
    $this->buffer .="\n                                 rcode = o.response.meta.rcode,";
    $this->buffer .="\n                                 orec  = oargs.record;";
    $this->buffer .="\n  ";
    $this->buffer .="\n                             if ( rcode<300 ) {";
    $this->buffer .="\n                                 updateStatus('".$dt_param['delete_msg']."' + orec.get('".$dt_param['table_pkey']."') + ' from server and locally');";
    $this->buffer .="\n                                 orec.destroy(null,null);";
    $this->buffer .="\n                                 if ( Y.Lang.isFunction(oargs.rqst_options.sync_callback) ) oargs.rqst_options.sync_callback();";
    $this->buffer .="\n                             }";
    $this->buffer .="\n  ";
    $this->buffer .="\n                         } // end if 'o'";
    $this->buffer .="\n                     });";
    $this->buffer .="\n  ";
    $this->buffer .="\n                     return;";
    $this->buffer .="\n                     break;";
    $this->buffer .="\n  ";
    $this->buffer .="\n             }";
    $this->buffer .="\n  ";
    $this->buffer .="\n         }";
    $this->buffer .="\n  ";
    $this->buffer .="\n     });";
    // $this->buffer .="\n, {";
    // $this->buffer .="\n       ATTRS: {";
    // $this->buffer .="\n  ";
    // $this->buffer .="\n     // Define the record fields for this record ...";
    // $this->buffer .="\n  ";
    // $this->buffer .="\n         mod_id: {";
    // $this->buffer .="\n             value:      0,";
    // $this->buffer .="\n             validator:  Y.Lang.isNumber";
    // $this->buffer .="\n         },";
    // $this->buffer .="\n  ";
    // $this->buffer .="\n         mod_name: {";
    // $this->buffer .="\n             value:      '',";
    // $this->buffer .="\n             validator:  Y.Lang.isString";
    // $this->buffer .="\n         },";
    // $this->buffer .="\n  ";
    // $this->buffer .="\n         mod_data: {";
    // $this->buffer .="\n             value:      null,";
    // $this->buffer .="\n             validator:  Y.Lang.isNumber";
    // $this->buffer .="\n         },";
    // $this->buffer .="\n  ";
    // $this->buffer .="\n         mod_ts: {";
    // $this->buffer .="\n             value:      null";
    // $this->buffer .="\n         }";
    // $this->buffer .="\n  ";
    // $this->buffer .="\n       }";
    // $this->buffer .="\n     });";

		//    $this->buffer .= "Colset_".$this->name." = new Y.Columnset({defintions:Nestedcolset_".$this->name."});";
    $this->buffer .="  var table_".$dt_param['name']." = new Y.DataTable({\n
    columns: [\n";

    $boucle=1;
    foreach ($dt_param['item_list'] as $item => $attributes){

      if ( !isset($attributes['display']) ) { $attributes['display'] = TRUE;}
      
      if ( $attributes['display'] == TRUE ){

        if ( preg_match("/children.*/", $item) ){
          $item = "children";
        }

        switch ($item){
          case "children":
            $this->buffer .= '      { ';
            if ( isset($attributes['label']) )
            { $this->buffer .= 'label:'.$attributes['label'].','; }
            $this->buffer .= " children:\n";
            $this->buffer .= "        [\n";

            $total_child = sizeof($attributes) - 3;
            $boucle_child = 0;
            foreach ($attributes as $child_array){
              if ( is_array($child_array) ){
                $this->buffer .= '          { key:"'.$child_array['key'].'"';


                if ( ! empty($child_array['sortable']) ){
                  $this->buffer .= ', sortable:'.$child_array['sortable'];
                }

                if ( ! empty($child_array['resizeable']) ){
                  $this->buffer .= ', resizeable:'.$child_array['resizeable'];
                }

                if ( ! empty($child_array['label']) ) {
                  //$child_array['label'];
                  $this->buffer .= ', label:'.$child_array['label'];
                }

                if ( ! empty($child_array['formater']) ) {
                  //$child_array['label'];
                  $this->buffer .= ', formatter:'.$child_array['label'];
                }

                $this->buffer .= "}";

                if ( $boucle_child < $total_child ) { $this->buffer .= ",\n"; }
                $boucle_child++;

              }
            }

            $this->buffer .= "\n        ]\n";
            $this->buffer .= "      }";
            if ( $boucle < sizeof($dt_param['item_list']) -1 ){
              $this->buffer .= ",\n";
            }
            else {$this->buffer .= ",";}
            
            
            break;

            default:
              if ( empty($attributes['label']) ) { $attributes['label']="";}
              
              $this->buffer .= "      {\n";
              $this->buffer .= "            key:\"$item\",\n";
              //, label:"'.$attributes['label'].'"';
              foreach ($attributes as $att_key => $att_value) {
                $this->buffer .= "            $att_key:$att_value,\n";
              }
              $this->buffer .= "      }\n";
              if ( $boucle < sizeof($dt_param['item_list']) ){
                $this->buffer .= "      ,\n";
              }

        }
      }
      $boucle++;
    }
/*
            \"Title\",
            \"Phone\",
            { key:\"Rating.AverageRating\", label:\"Rating\" }
            
*/
    $this->buffer .="\n";
    $this->buffer .="    ],\n";
    $this->buffer .="    recordType: table_".$dt_param['name']."_RecordModel,\n";
    $this->buffer .="    summary: \"".$dt_param['table_summary']."\",\n";
    $this->buffer .="    caption: \"".$dt_param['table_caption']."\"\n";
    $this->buffer .="    });\n";


    $add_request = "";
    if ( isset($dt_param['params']) && gettype($dt_param['params']) == "array" ){
      foreach ( $dt_param['params'] as $key => $value ){
        if ( $add_request != "" ){ $add_request .= "&";}
        $add_request .= "$key=$value";
      }
    }

    $this->buffer .= "\n";
    $this->buffer .= "table_".$dt_param['name'].".plug(Y.Plugin.DataTableDataSource, { datasource: DS_".$dt_param['name']." });\n";
    $this->buffer .= "table_".$dt_param['name'].".datasource.load({ request: \"startIndex=0&results=10&$add_request\" })\n";
    $this->buffer .= "DS_".$dt_param['name'].".after(\"response\", function(){ table_".$dt_param['name'].".render(\"#".$dt_param['name']."\") });";
    $this->buffer .= "table_".$dt_param['name'].".render(\"#".$dt_param['name']."\");\n";
    $this->buffer .= "\n";

    $this->buffer .= "\n// Create stuff about clicking";
    $this->buffer .= "\n// -------------------------";
    $this->buffer .= "\n//  Define a click handler on table cells ...";
    $this->buffer .= "\n//   Note: use Event Delegation here (instead of just .on() ) because we may be";
    $this->buffer .= "\n//         deleting rows which may cause problems with just .on";
    $this->buffer .= "\n// -------------------------";
    //$this->buffer .= "\n  var cols, lastTD;";
    $this->buffer .= "\n  table_".$dt_param['name'].".addAttr(\"selectedCell\",{value:null});";
    $this->buffer .= "\n  table_".$dt_param['name'].".delegate(\"click\", function(e) {";
    $this->buffer .= "\n      this.set(\"selectedCell\",e.currentTarget);";
    $this->buffer .= "\n  }, \"td\", table_".$dt_param['name'].");";

    $this->buffer .= "  table_".$dt_param['name'].".after(\"selectedCellChange\",function(e){\n";
    $this->buffer .= "      var td = e.newVal,\n";
    $this->buffer .= "          last_td = e.prevVal,\n";
    $this->buffer .= "          rec = this.getRecord(td),\n";
    $this->buffer .= "          col = this.getColumnTd(td);\n";

    $this->buffer .= "      if ( last_td ) last_td.removeClass(\"hilite\");\n";
    $this->buffer .= "      td.addClass(\"hilite\");\n";
    $this->buffer .= "      updateStatus('Clicked TD for rec domain='+rec.get('".$dt_param['table_pkey']."'));\n";

    $this->buffer .= "        switch( col.label.toLowerCase() ) {\n";
    $this->buffer .= "  \n";
    $this->buffer .= "             case \"edit\":\n";
    $this->buffer .= "  \n";
    $this->buffer .= "             //\n";
    $this->buffer .= "             //  Load the current \"record\" items into the Dialog FORM inputs ...\n";
    $this->buffer .= "             //\n";
    $this->buffer .= "                 var frm = document.forms['dialogForm'];\n";
    $this->buffer .= "                 frm.frmID.value   = rec.get('mod_id');\n";
    $this->buffer .= "                 frm.frmName.value = rec.get('mod_name');\n";
    $this->buffer .= "                 frm.frmData.value = rec.get('mod_data');\n";
    $this->buffer .= "  \n";
    $this->buffer .= "             //\n";
    $this->buffer .= "             //  Set the Panel custom attribute for the record\n";
    $this->buffer .= "             //\n";
    $this->buffer .= "                 editorPanel.set('dt_record',rec);\n";
    $this->buffer .= "  \n";
    $this->buffer .= "             //\n";
    $this->buffer .= "             //  Set the \"save\" button label to \"Update\" on the Dialog and show it ...\n";
    $this->buffer .= "             //\n";
    $this->buffer .= "                 var btns = editorPanel.get('buttons.footer');\n";
    $this->buffer .= "                 btns[0].value = \"Update\";\n";
    $this->buffer .= "                 editorPanel.set('buttons.footer',btns);\n";
    $this->buffer .= "                 editorPanel.render();\n";
    $this->buffer .= "                 editorPanel.show();\n";
    $this->buffer .= "  \n";
    $this->buffer .= "                 break;\n";
    $this->buffer .= "  \n";
    $this->buffer .= "  \n";
    $this->buffer .= "             case \"delete\":\n";
    $this->buffer .= "  \n";
    $this->buffer .= "                 if ( confirm(\"Are you SURE you want to delete this record ?\") === true ) {\n";
    $this->buffer .= "                     rec.sync('delete', { dt: this });\n";
    $this->buffer .= "                 }\n";
    $this->buffer .= "                 break;\n";
    $this->buffer .= "         }\n";

    $this->buffer .= "   });\n";


    $this->buffer .= "     var updateStatus = function(o) {\n";
    $this->buffer .= "         Y.one(\"#status\").append(o+'<br/>');\n";
    $this->buffer .= "     }\n";

    $this->buffer .= "     /** \n";
    $this->buffer .= "      Method to scan the \"columns\" Array for the target and return the requested column. \n";
    $this->buffer .= "      The requested \"target\" can be either of ; \n";
    $this->buffer .= "         a column index, \n";
    $this->buffer .= "         or a TD Node, \n";
    $this->buffer .= "         or a column \"key\", column \"name\" or \"_yuid\" (in that order). \n";
    $this->buffer .= "   \n";
    $this->buffer .= "      @method getColumn \n";
    $this->buffer .= "      @param target {Number | Node | String} Either the column index, the TD node or a column ID \n";
    $this->buffer .= "      @returns {Object} Column \n";
    $this->buffer .= "      **/ \n";
    $this->buffer .= "     Y.DataTable.prototype.getColumnTd = function( target ) { \n";
    $this->buffer .= "         var cs = this.get('columns'), \n";
    $this->buffer .= "             ckey = null; \n";
    $this->buffer .= "   \n";
    $this->buffer .= "         if (Y.Lang.isNumber(target) ) \n";
    $this->buffer .= "             return cs[target];  //return cs.keys[col]; \n";
    $this->buffer .= "   \n";
    $this->buffer .= "         else if ( Y.Lang.isString(target) || target instanceof Y.Node ) {   // check for 'key' or then 'name', finally '_yuid' \n";
    $this->buffer .= "   \n";
    $this->buffer .= "             ckey = ( target instanceof Y.Node ) ? ckey = this.getCellColumnKey( target ) : ckey; \n";
    $this->buffer .= "   \n";
    $this->buffer .= "             col = ( ckey ) ? ckey : target; \n";
    $this->buffer .= "   \n";
    $this->buffer .= "         // Check if a column \"key\" \n";
    $this->buffer .= "             var cm = -1; \n";
    $this->buffer .= "             Y.Array.some( cs, function(citem) { \n";
    $this->buffer .= "                 if ( citem['key'] === col ) { \n";
    $this->buffer .= "                     cm = citem; \n";
    $this->buffer .= "                     return true; \n";
    $this->buffer .= "                 } \n";
    $this->buffer .= "             }); \n";
    $this->buffer .= "             if ( cm !== -1) return cm;  // found one, bail !! \n";
    $this->buffer .= "   \n";
    $this->buffer .= "         // If not found, Check if a column \"name\" \n";
    $this->buffer .= "             Y.Array.some( cs, function(citem) { \n";
    $this->buffer .= "                 if ( citem.name === col ) { \n";
    $this->buffer .= "                     cm = citem; \n";
    $this->buffer .= "                     return true; \n";
    $this->buffer .= "                 } \n";
    $this->buffer .= "             }); \n";
    $this->buffer .= "             if ( cm!==-1 ) return cm; \n";
    $this->buffer .= "   \n";
    $this->buffer .= "         // If not found, Check if a column \"_yui\" something \n";
    $this->buffer .= "             Y.Array.some( cs, function(citem) { \n";
    $this->buffer .= "                 if ( citem._yuid === col ) { \n";
    $this->buffer .= "                     cm = citem; \n";
    $this->buffer .= "                     return true; \n";
    $this->buffer .= "                 } \n";
    $this->buffer .= "             }); \n";
    $this->buffer .= "             return cm; \n";
    $this->buffer .= "   \n";
    $this->buffer .= "         } else \n";
    $this->buffer .= "             return false; \n";
    $this->buffer .= "     } \n";

    // $this->buffer .= "\n";
    // $this->buffer .= "\n      var cell = e.currentTarget;         // the clicked TD";
    // $this->buffer .= "\n      row  = cell.ancestor(),         // the parent of TD, which is TR";
    // $this->buffer .= "\n";
    // $this->buffer .= "\n      rec  = this.getRecord( cell ),      //  Call the helper method above to return the \"data\" record (a Model)";
    // $this->buffer .= "\n      //ckey = this.getCellColumnKey( cell ), //";
    // $this->buffer .= "\n      ckey  = this.getCellColumnKey( cell ),";
    // $this->buffer .= "\n      col   = this.getColumn(ckey);alert('test ngo ' + ckey + ' ## ' + col.name );     //";
    // $this->buffer .= "\n  //";
    // $this->buffer .= "\n  //  check for TD cell highlighting";
    // $this->buffer .= "\n  //";
    // $this->buffer .= "\n    if ( !col.nohighlight ) {   // if col has nohighlight=true, then don't highlight the cell ....";
    // $this->buffer .= "\n      if ( lastTD ) lastTD.removeClass(\"myhighlight\");";
    // $this->buffer .= "\n      cell.addClass(\"myhighlight\");";
    // $this->buffer .= "\n      lastTD = cell;";
    // $this->buffer .= "\n    }";
    // $this->buffer .= "\n";
    // $this->buffer .= "\n    var d_ckey = col.key || col.name || 'not set';  // if column key returned is a yui_id, don't display it";
    // $this->buffer .= "\n                                    //   ... that means we are in the \"Select\", \"Edit\" or \"Delete\" columns";
    // $this->buffer .= "\n  //";
    // $this->buffer .= "\n  //  Update status box";
    // $this->buffer .= "\n  //";
    // $this->buffer .= "\n      var StatusTMPL = Y.one(\"#status-template\").getContent();  // this retrieves HTML containing {xxx} tags for substitution";
    // $this->buffer .= "\n";
    // $this->buffer .= "\n      Y.one(\"#idStatus\").setContent( Y\";.Lang.sub( StatusTMPL, {  // ... do the substitution into the template using Y.Lang.sub";
    // $this->buffer .= "\n       rec_id :   rec.get('clientId'),";
    // $this->buffer .= "\n       rec_index: this.get('data').indexOf(rec),";
    // $this->buffer .= "\n       col_key :  d_ckey,";
    // $this->buffer .= "\n       col_index: Y.Array.indexOf( this.get('columns'), col ), //this.get('columns').indexOf(col),";
    // $this->buffer .= "\n       raw_data : rec.get(ckey) || 'No Data'";
    // $this->buffer .= "\n    } ) );";
    // $this->buffer .= "\n";
    // $this->buffer .= "\n  //";
    // $this->buffer .= "\n  //  If a column 'action' is available, process it";
    // $this->buffer .= "\n  //";
    // $this->buffer .= "\n    switch( col.name || null ) {";
    // $this->buffer .= "\n      case 'edit':";
    // $this->buffer .= "\n        showDT_Panel( rec, cell.getXY() );";
    // $this->buffer .= "\n        break;";
    // $this->buffer .= "\n";
    // $this->buffer .= "\n      case 'delete':";
    // $this->buffer .= "\n        var qstr = '&action=delete',";
    // $this->buffer .= "\n            rtn_opts = { rec:this, sync_options:options, sync_callback:callback };";
    // $this->buffer .= "\n        if ( confirm(\"Are you sure you want to delete this record (\" + rec.get('domain_alias') + \")?\") === true ) {";
    // $this->buffer .= "\n          table_".$dt_param['name'].".removeRow( rec.get('domain_alias') );";
    // //$this->buffer .= "\n          Y.one(\"#idStatus\").setContent(\"<br/><b>Row was Deleted!</b>\");";
    // $this->buffer .= "\n        }";
    // $this->buffer .= "\n";
    // $this->buffer .= "\n        break;";
    // $this->buffer .= "\n    }";
    // $this->buffer .= "\n  }, \"tbody tr td\", table_".$dt_param['name'].");";
    // $this->buffer .= "\n  //  the selector,  internal scope";



  }

}

