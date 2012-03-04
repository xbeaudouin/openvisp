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
  

  function create_datasource(){
    $this->load_yui_lib("datasource-io");
    $this->load_yui_lib("datasource-jsonschema");
    $this->load_yui_lib("gallery-paginator");

    $this->buffer .= "\n\n";

    $add_request = "";
    if ( isset($this->ajax_info['params']) && gettype($this->ajax_info['params']) == "array" ){
      foreach ( $this->ajax_info['params'] as $key => $value ){
        if ( $add_request != "" ){ $add_request .= "&";}
        $add_request .= "$key=$value";
      }
    }


    $this->buffer .= "

  function sendRequest_".$this->name."(){
    table_".$this->name.".datasource.load({
      request: \"".$add_request."&startIndex=\"+".$this->name."_pg.getStartIndex() + \"&results=\" + ".$this->name."_pg.getRowsPerPage()
    })

  }
";

    $this->buffer .= "\n\n";

    $this->buffer .= "  var DS_".$this->name." = new Y.DataSource.IO({source:\"".$this->ajax_info['url']."?\", ioConfig: { method: '".$this->ajax_info['method']."'}});

  DS_".$this->name.".plug(Y.Plugin.DataSourceJSONSchema, {
    schema: {
      resultListLocator: \"records\",
      metaFields: {
        totalRecords: 'totalRecords'
      },
      resultFields: [\n";

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

            if ( $boucle < sizeof($this->item_list)){
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

	var ".$this->name."_pg = new Y.Paginator(
	{
		rowsPerPage: ".$this->ajax_info['params']['results'].",
		rowsPerPageOptions: [1,2,5,10,25,50,100],
		template: '{FirstPageLink} {PreviousPageLink} {PageLinks} {NextPageLink} {LastPageLink} <span class=\"pg-rpp-label\">Rows per page:</span> {RowsPerPageDropdown}'
	});
	".$this->name."_pg.render('#".$this->name."-nav');

	function updatePaginator(state)
	{
		this.setPage(state.page, true);
		this.setRowsPerPage(state.rowsPerPage, true);
		this.setTotalRecords(state.totalRecords, true);
		sendRequest_".$this->name."();
	}
	".$this->name."_pg.on('changeRequest', updatePaginator, ".$this->name."_pg);

	DS_".$this->name.".on('response', function(e)
	{
		".$this->name."_pg.setTotalRecords(e.response.meta.totalRecords, true);
		".$this->name."_pg.render();
	});

//        startIndex: ".$this->name."_pg.getStartIndex(),
//       resultCount: ".$this->name."_pg.getRowsPerPage(),

";

		$this->buffer .= "\n\n";

  }

/*
  Function create_table
  To create a table with yui object.
  you must link the table with a datasource.
*/

  function create_datatable(){
    $this->load_yui_lib("datatable-datasource");


		//    $this->buffer .= "Colset_".$this->name." = new Y.Columnset({defintions:Nestedcolset_".$this->name."});";
    $this->buffer .="  var table_".$this->name." = new Y.DataTable({\n
    columns: [\n";

    $boucle=1;
    foreach ($this->item_list as $item => $attributes){

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
            if ( $boucle < sizeof($this->item_list) -1 ){
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
              if ( $boucle < sizeof($this->item_list) ){
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
    summary: \"".$this->ajax_info['table_summary']."\",
    caption: \"".$this->ajax_info['table_caption']."\"
    });
    ";

    $add_request = "";
    if ( isset($this->ajax_info['params']) && gettype($this->ajax_info['params']) == "array" ){
      foreach ( $this->ajax_info['params'] as $key => $value ){
        if ( $add_request != "" ){ $add_request .= "&";}
        $add_request .= "$key=$value";
      }
    }

    $this->buffer .= "\n";
    $this->buffer .= "table_".$this->name.".plug(Y.Plugin.DataTableDataSource, { datasource: DS_".$this->name." });\n";
    $this->buffer .= "table_".$this->name.".datasource.load({ request: \"startIndex=0&results=10&$add_request\" })\n";
    $this->buffer .= "DS_".$this->name.".after(\"response\", function(){ table_".$this->name.".render(\"#".$this->name."\") });";
    $this->buffer .= "table_".$this->name.".render(\"#".$this->name."\");\n";
    $this->buffer .= "\n";

  }

}

