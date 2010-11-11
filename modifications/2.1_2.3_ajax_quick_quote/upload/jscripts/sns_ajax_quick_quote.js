/**
* (SnS) AJAX Quick Quote
* for IP.Board 2.1.x‒2.3.x
*
* @package		(SnS) AJAX Quick Quote
* @author		Oleg "Sannis" Efimov
* @copyright		2007 Sannis
* @link			http://sannis.ru
*/


// Some code from 2.3 ips_ipsclass.js with modification

function sns_un_htmlspecialchars( text )
{
	text = text.replace( /&lt;/g  , '<' );
	text = text.replace( /&gt;/g  , '>' );
	text = text.replace( /&amp;/g, '"' );

    return text;
};



function ajax_quick_quote( pid )
{
	//----------------------------------
	// Using fancy js?
	//----------------------------------
	
	if ( !use_enhanced_js )
	{
		return false;
	}
	
	/*--------------------------------------------*/
	// Main function to do on request
	/*--------------------------------------------*/
	
	do_request_function = function()
	{
		if ( ! xmlobj.readystate_ready_and_ok() )
		{
			xmlobj.show_loading();
			return;
		}
		
		xmlobj.hide_loading();
		
		//----------------------------------
		// Process
		//----------------------------------
		
		var raw_text = xmlobj.xmlhandler.responseText;
		
		if ( raw_text == 'nopermission' )
		{
			alert( js_error_no_permission );
		}
		else if ( raw_text == 'error' )
		{
			alert( 'error' );
		}
		else 
		{
			ajax_quick_quote_add(raw_text);	
		}
	}
	
	//----------------------------------
	// LOAD XML
	//----------------------------------
	
	xmlobj = new ajax_request();
	xmlobj.onreadystatechange( do_request_function );
	var url = ipb_var_base_url+'act=xmlout&do=get-post-quickquote&p='+pid+'&t='+ipb_input_t+'&f='+ipb_input_f;
	xmlobj.process(url);
	
	return false;
}

function ajax_quick_quote_add( raw_text )
{
	if ( raw_text.replace(' ', '') != '')
	{
		raw_text = sns_un_htmlspecialchars(raw_text);

		my_show_div(document.getElementById('qr_open'));
		my_hide_div(document.getElementById('qr_closed'));

		if( typeof IPS_Lite_Editor != "undefined" && typeof IPS_Lite_Editor['fast-reply'] != "undefined" ) // 2.3.x with standart fast reply
		{
			IPS_Lite_Editor['fast-reply'].editor_check_focus();
			IPS_Lite_Editor['fast-reply'].insert_text(IPS_Lite_Editor['fast-reply'].get_selection() + raw_text);
		}
		else if( typeof IPS_editor != "undefined" && typeof IPS_editor['ed-0'] != "undefined" ) // 2.3.x with (SnS) Extended Fast Reply for 2.2 & 2.3 mod
		{
		        IPS_editor['ed-0'].editor_check_focus();
		        IPS_editor['ed-0'].insert_text(IPS_editor['ed-0'].get_selection() + raw_text);
		}
		else // 2.1.x with standart fast reply
		{
			wrap_tags(raw_text, '', 0);
		}
		document.getElementById('qr_open').scrollIntoView();
	}
}
