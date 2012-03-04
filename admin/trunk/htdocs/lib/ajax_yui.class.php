<?php

class AJAX_YUI
{

  protected $db_link;

  function __construct ($db_link){
    $this->db_link = $db_link;
  }


  function add_search_form($item_list){
    $this->search_form=$item_list;
  }

  function add_function($function_name, $function_content){
    $this->js_function .= 'var '.$function_name.' = '.$function_content."\n\n"; 
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
};


';


		$this->load_lib .= "




YUI().use(";

  }

  function load_yui_lib ($lib){
    $this->cnt_lib++;
    if ( !in_array($lib,$this->loaded_lib)){
      $this->loaded_lib[]=$lib;
      if ($this->cnt_lib > 1 ){$this->load_lib.=",";}
      $this->load_lib.='"'.$lib.'"';
    }
    
  }


  function end(){
    $this->load_yui_lib("datasource-io");
    $this->load_yui_lib("autocomplete");
    $this->load_yui_lib("event");
    $this->load_yui_lib("node");

    print $this->load_lib;
    print ", function (Y) {\n";

    print "";

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


  /*
    Function create_search_field_autocomplete
    function to attache a yui object to a search field
  */

  function create_search_field_autocomplete($options){
    $this->load_yui_lib("datasource-io");
    $this->load_yui_lib("autocomplete");
    $this->load_yui_lib("event");
    $this->load_yui_lib("node");

    $this->buffer .= "\n\n";


  }

  /*
    Function create_datasource
    function to create a yui datasource with the item_list array
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


    $this->buffer .= "

  function sendRequest_".$ds_param['name']."(){
    table_".$ds_param['name'].".datasource.load({
      request: \"".$add_request."&startIndex=\"+".$ds_param['name']."_pg.getStartIndex() + \"&results=\" + ".$ds_param['name']."_pg.getRowsPerPage()
    })

  }
";

    $this->buffer .= "\n\n";

    $this->buffer .= "  var DS_".$ds_param['name']." = new Y.DataSource.IO({source:\"".$ds_param['url']."?\", ioConfig: { method: '".$ds_param['method']."'}});

  DS_".$ds_param['name'].".plug(Y.Plugin.DataSourceJSONSchema, {
    schema: {
      resultListLocator: \"records\",
      metaFields: {
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

    $this->buffer .= "\n      ]
    }
  });";
  
		
    $this->buffer .= "\n\n";

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

  function create_datatable($dt_param){
    $this->load_yui_lib("datatable-datasource");


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
              $this->buffer .= "key:\"$item\",\n";
              //, label:"'.$attributes['label'].'"';
              foreach ($attributes as $att_key => $att_value) {
                $this->buffer .= "$att_key:$att_value,\n";
              }
              $this->buffer .= "      }\n";
              if ( $boucle < sizeof($dt_param['item_list']) ){
                $this->buffer .= ",\n";
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
    $this->buffer .="
    ],\n
    summary: \"".$dt_param['table_summary']."\",
    caption: \"".$dt_param['table_caption']."\"
    });
    ";

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

  }

}

