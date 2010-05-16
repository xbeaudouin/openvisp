<form name="overview" method="post">
<?php
  print $PALANG['pOverview_welcome_text'];

print load_js("../lib/yui/yahoo-dom-event/yahoo-dom-event.js");
print load_js("../lib/yui/connection/connection-min.js");
print load_js("../lib/yui/json/json-min.js");
print load_js("../lib/yui/element/element-min.js");
print load_js("../lib/yui/paginator/paginator-min.js");
print load_js("../lib/yui/datasource/datasource-min.js");
print load_js("../lib/yui/datatable/datatable-min.js");

print load_css("../css/datatable.css");


?>


<style type="text/css">

.yui-dt-editor {
position:absolute;
z-index:9000;
}

</style>

<div id="domain-nav"></div>
<div id="domain"></div>


<?php

	$ajax_domain->end();

?>


<br/>
<br/>
