/*
+--------------------------------------------------------------------------
|   (SnS) AJAX Quick MultiQuote
|   for IP.Board 2.3.x
|   based on standart multiquote functions
|   ========================================
|   (c) 2007‒2010 Sannis
|   http://sannis.ru
|   http://forums.ibresource.ru/index.php?showuser=36662
+---------------------------------------------------------------------------
*/

function ajax_quick_multi_quote()
{
	//----------------------------------
	// Using fancy js?
	//----------------------------------

	if ( ! use_enhanced_js )
	{
		return;
	}

	/*--------------------------------------------*/
	// Main function to do on request
	// Must be defined first!!
	/*--------------------------------------------*/
	
	do_request_function = function()
	{
		//----------------------------------
		// Ignore unless we're ready to go
		//----------------------------------

		if ( ! xmlobj.readystate_ready_and_ok() )
		{
			xmlobj.show_loading();
			return;
		}

		xmlobj.hide_loading();

		//----------------------------------
		// INIT
		//----------------------------------

		var ajax_return = xmlobj.xmlhandler.responseText;

		if ( ajax_return != 'error' )
		{
			if (ajax_return.replace(" ", "") != "")
			{
				document.REPLIER.Post.innerHTML += ajax_return;
				my_show_div(document.getElementById('qr_open'));
				my_hide_div(document.getElementById('qr_closed'));
				document.getElementById('qr_open').scrollIntoView();
				ipsclass.my_setcookie('mqtids', '');
			}
		}
		
		return;		
	}

	//----------------------------------
	// LOAD XML
	//----------------------------------
	xmlobj = new ajax_request();
	xmlobj.onreadystatechange( do_request_function );
	var url = ipb_var_base_url+'act=xmlout&do=get-quickmultiquote&t='+ipb_input_t+'&f='+ipb_input_f;
	var xmlreturn = xmlobj.process( url );

	return ;
}
