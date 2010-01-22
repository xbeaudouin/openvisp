<!-- $Id: verif-js.tpl,v 1.1 2007/07/09 12:22:55 kiwi Exp $ -->

<script language="javascript">
<!--

// don't forget to add the following into form :
//   onSubmit="please_wait();"

function please_wait(whatform)
{
	// sets value of all submit buttons to please wait and disables them
	// whatform is optional
	for ( f=0; f<document.forms.length; f++ )
	{
		var form = document.forms[f];
		for ( i=0; i<form.length; i++ )
		{
			if ( typeof form.elements[i].type != 'undefined' &&
			form.elements[i].type == 'submit' )
			{
				form.elements[i].disabled = true;
				form.elements[i].value = '<?php print $PALANG['wait']; ?>';
			}
		}
	}
	// now submit form if specified
	if ( typeof whatform != 'undefined' )
		whatform.submit();
}

//-->
</script>

