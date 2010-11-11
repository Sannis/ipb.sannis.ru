<?php

/*-------------------------------------------
+
+	Trivia System 2.0.3
+	by bfarber
+	http://ipbmods.net
+	brandon.farber@gmail.com
+--------------------------------------------
+
+	Copyright 2005 by bfarber
+	All credits to bfarber or ipbmods.net
+ 	must remain in tact or use of this
+	modification is not permitted!
+
+------------------------------------------
+
+	2.2.x version
+
+	Copyright 2007 by Sannis
+	All credits toSannis or ibpower.ru
+ 	must remain in tact or use of this
+	modification is not permitted!
+
+------------------------------------------*/

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>������������ �����</h1>�� �� ������ ������� � ����� ����� ��������. ���� �� ������� ��������� �����, �� ������ �������� ��� ��������������� �����.";
	exit();
}

/*
+--------------------------------------------------------------------------
|   This module has two functions:
|   get_session_variables: Return the session variables for the class_session functions
|   parse_online_entries: Parses an array from online.php
|   See each function for more information
+--------------------------------------------------------------------------
*/

//-----------------------------------------
// This must always be 'components_location'
//-----------------------------------------

class components_location_trivia
{
	var $ipsclass;

	/*-------------------------------------------------------------------------*/
	// get_session_variables
	// Returns:
	// array( '1_type' => {location type #1} [ char(10) ]
	//        '1_id'   => {location ID #1}   [ int(10)  ]
	//        '2_type' => {location type #2} [ char(10) ]
	//        '2_id'   => {location ID #2}   [ int(10)  ]
	//		  '3_type' => {location type #3} [ char(10) ]
	//        '3_id'   => {location ID #3}   [ int(10)  ]
	//      );
	// All are optional.
	// Use this to populate the 'module_id_*' fields in the session table
	// so you can check in your own scripts it the member is active in your module
	// {variable} can be 30 chrs long and alpha numerical
	// "location" in the sessions table will be the name of the module called
	/*-------------------------------------------------------------------------*/
	
	function get_session_variables()
	{
		$return_array = array();

		if ( ($this->ipsclass->input['CODE'] == '02') or ($this->ipsclass->input['CODE'] == '03') or ($this->ipsclass->input['CODE'] == '04')) 
		{
			$return_array['1_type'] = 'do_answer';
		}
		else
		{
			$return_array['1_type'] = 'main_page';
		}

		return $return_array;
	}

	/*-------------------------------------------------------------------------*/
	// parse_online_entries
	// INPUT: $array IS:
	// $array[ $session_id ] = $session_array;
	// Session array is DB row from ibf_sessions
	// EXPECTED RETURN ------------------------------------
	// $array[ $session_id ]['_parsed'] = 1;
	// $array[ $session_id ]['_url']    = {Location url}
	// $array[ $session_id ]['_text']   = {Location text}
	// $array[ $session_id ] = $session_array...
	//
	// YOU ARE RESPONSIBLE FOR PERMISSION CHECKS. IF THE MEMBER DOESN'T
	// HAVE PERMISSION RETURN '_url'    => $this->ipsclass->base_url,
	// 						  '_text'   => $this->ipsclass->lang['board_index'],
	//						  '_parsed' => 1 { as well as the rest of $session_array }
	/*-------------------------------------------------------------------------*/

	function parse_online_entries( $array=array() )
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------

		$return = array();
		$gallery_cache = array();
		$image_cache = array();

		//-----------------------------------------
		// Load language file
		//-----------------------------------------
		if ( ! isset( $this->ipsclass->lang['trivia_loc_main_page'] ) )
		{
			$this->ipsclass->load_language( 'lang_trivia_location' );
		}

		//-----------------------------------------
		// LOOP
		//-----------------------------------------
		foreach( $array as $session_id => $session_array )
		{
			if ( ($session_array['location'] == 'mod:trivia') and ($session_array['location_1_type'] == 'do_answer'))
			{
				$location = "{$this->ipsclass->base_url}autocom=trivia&CODE=03";
				$text = $this->ipsclass->lang['trivia_loc_do_answer'];
			}
			else
			{
				$location = "{$this->ipsclass->base_url}autocom=trivia";
				$text = $this->ipsclass->lang['trivia_loc_main_page'];
			}
			   
			$return[ $session_id ] = array_merge( $session_array, array( '_url' => $location, '_text' => $text, '_parsed' => 1 ) );
		}

		return $return;
	}

}
?>