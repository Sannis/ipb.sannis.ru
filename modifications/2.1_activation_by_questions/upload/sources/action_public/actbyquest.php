<?php

/*
+---------------------------------------------------------------------------
|   ActByQuest Mod v1.2
|   =============================================
|   > $Date: 2007-03-21 $
|   > $Author: Oleg "Sannis" Efimov <efimovov@yandex.ru> $
+---------------------------------------------------------------------------
*/


if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Некорректный адрес</h1>Вы не имеете доступа к этому файлу напрямую. Если вы недавно обновляли форум, вы должны обновить все соответствующие файлы.";
	exit();
}

class actbyquest
{
	# Classes
	var $ipsclass;
	
	# Others
    var $output     = "";
    var $page_title = "";
    var $nav        = array();
    
    #Prefix
    var $prefix       = "answer_";

	/*-------------------------------------------------------------------------*/
	// Auto run
	/*-------------------------------------------------------------------------*/
	
    function auto_run()
    {
    	//-----------------------------------------
    	// Require the HTML and language modules
    	//-----------------------------------------
    	
		$this->ipsclass->load_language('lang_actbyquest');
    	$this->ipsclass->load_template('skin_actbyquest');
    	
    	$this->base_url  = $this->ipsclass->base_url;
    	
    	//-----------------------------------------
    	// What to do?
    	//-----------------------------------------
    	if($this->ipsclass->member['mgroup'] == $this->ipsclass->vars['guest_group'])
    	{
    	    $this->ipsclass->Error( array( 'LEVEL' => 1, 'MSG' => 'actbyquest_guest') );
    	}
    	
    	if($this->ipsclass->member['mgroup'] != $this->ipsclass->vars['auth_group'])
    	{
    	    $this->ipsclass->Error( array( 'LEVEL' => 1, 'MSG' => 'actbyquest_noperm') );
    	}    	
    	
    	switch($this->ipsclass->input['CODE'])
    	{
    		case '01':
    			$this->show_questions();
    			break;
    		case '02':
    		    if(isset($this->ipsclass->input['flag']))
    		    {
    		        $n = $this->num_correct();
    		    }
    		    else
    		    {
    		        $this->ipsclass->boink_it( "{$this->ipsclass->base_url}act=Actbyquest");
    		    }    
    			if($this->correct_answer($n))
    			{
    			    $this->do_action();
    			    $this->show_success($n);
    			}
    			else
    			{			    
    			    $this->show_fail($n);
    			}
    			break;
    		default:
    			$this->show_questions();
    			break;
    	}
    	
    	//-----------------------------------------
    	// If we have any HTML to print, do so...
    	//-----------------------------------------
    	
    	$this->ipsclass->print->add_output("$this->output");
        $this->ipsclass->print->do_output( array( 'TITLE' => $this->page_title, 'JS' => 0, NAV => $this->nav ) );
 	}
 	
 	/*-------------------------------------------------------------------------*/
 	// Calculate correct answers
 	/*-------------------------------------------------------------------------*/
 	
 	function num_correct()
 	{ 		
 		$this->ipsclass->DB->simple_construct( array( 'select' => 'id, answer',
 									  'from'   => 'actbyquest',
 									  'order'  => 'id'
 							 )      );
 		$this->ipsclass->DB->simple_exec();
 								
 		$n = 0;
 		
 		while ($row = $this->ipsclass->DB->fetch_row() )
 		{
 			$s = $this->prefix.$row['id'];
 			if(isset($this->ipsclass->input[$s]) && strtolower($this->ipsclass->input[$s]) == strtolower($row['answer']))
 			{
 			    $n++; 			
 			}
 		}
 		
        return $n;
 	}
 	
 	/*-------------------------------------------------------------------------*/
 	// Correct?
 	/*-------------------------------------------------------------------------*/
 	
 	function correct_answer($n)
 	{
 	    if($n >= $this->ipsclass->vars['actbyquest_num'])
 	        return true;
 	    else
 	        return false;
 	}
 	
 	/*-------------------------------------------------------------------------*/
 	// Show Questions
 	/*-------------------------------------------------------------------------*/
 	
 	function show_questions()
 	{
 		$row['table_title'] = $this->ipsclass->lang['list_title'];
 		$row['question_title'] = $this->ipsclass->lang['question_title'];
 		$row['answer_title'] = $this->ipsclass->lang['answer_title'];
 		
 		$this->output = $this->ipsclass->compiled_templates['skin_actbyquest']->table_start($row);
 		
 		$this->ipsclass->DB->simple_construct( array( 'select' => 'id, question',
 									  'from'   => 'actbyquest',
 									  'order'  => 'id'
 							 )      );
 		$this->ipsclass->DB->simple_exec();
 								
 		$cnt = 0;
 		
 		while ($row = $this->ipsclass->DB->fetch_row() )
 		{
 			$row['row_color'] = $cnt % 2 ? 'row1' : 'row2';
 			
 			$cnt++;
 			
 			$row['answer_input'] = "<input type=\"text\" maxlength=\"60\" size=\"30\" name=\"".$this->prefix.$row['id']."\" />";
 			$this->output .= $this->ipsclass->compiled_templates['skin_actbyquest']->row($row);
 			
 		}
 		
 		$row['button'] = "<input class=\"button\" type=\"submit\" name=\"submit\" value=\"".$this->ipsclass->lang['quest_submit']."\" />";
 		$row['row_color'] = $cnt % 2 ? 'row1' : 'row2';
 		
 		$this->output .= $this->ipsclass->compiled_templates['skin_actbyquest']->table_end($row);
 		
 		$this->page_title = $this->ipsclass->lang['page_title'];
 		$this->nav        = array( $this->ipsclass->lang['nav_text'] );
 	}
 	
 	/*-------------------------------------------------------------------------*/
 	// Show Success Message
 	/*-------------------------------------------------------------------------*/
 	
 	function show_success($n)
 	{
 		$data['title'] = $this->ipsclass->lang['success_title'];
 		
 		$data['subtitle'] = str_replace("{N1}", $n, $this->ipsclass->lang['subtitle']);
 		$data['subtitle'] = str_replace("{N2}", $this->ipsclass->vars['actbyquest_num'], $data['subtitle']);
 		
 		$data['msg'] = $this->ipsclass->vars['actbyquest_success'];
 		
 		$this->output = $this->ipsclass->compiled_templates['skin_actbyquest']->success($data);
 		
 		$this->page_title = $this->ipsclass->lang['page_title'];
 		$this->nav        = array( $this->ipsclass->lang['nav_text'] );
 	}
 	
 	/*-------------------------------------------------------------------------*/
 	// Show Fail Message
 	/*-------------------------------------------------------------------------*/
 	
 	function show_fail($n)
 	{
 		$data['title'] = $this->ipsclass->lang['fail_title'];
 		
 		$data['subtitle'] = str_replace("{N1}", $n, $this->ipsclass->lang['subtitle']);
 		$data['subtitle'] = str_replace("{N2}", $this->ipsclass->vars['actbyquest_num'], $data['subtitle']);
 		
 		$data['msg'] = $this->ipsclass->vars['actbyquest_fail'];
 		
 		$this->output = $this->ipsclass->compiled_templates['skin_actbyquest']->fail($data);
 		
 		$this->page_title = $this->ipsclass->lang['page_title'];
 		$this->nav        = array( $this->ipsclass->lang['nav_text'] );
 	}
 	
 	/*-------------------------------------------------------------------------*/
 	// Success Action
 	/*-------------------------------------------------------------------------*/
 	
 	function do_action()
 	{
 	    //$this->ipsclass->DB->do_update('members', array( 'mgroup'=>$this->ipsclass->vars['member_group']), "id=".$this->ipsclass->member['id']);
		
		//-----------------------------------------
		// Check for input and it's in a valid format.
		//-----------------------------------------
		
		$user_id      = $this->ipsclass->member['id'];
		
		if (!$user_id)
		{
			$this->ipsclass->Error( array( 'LEVEL' => 1, 'MSG' => 'actbyquest_guest') );
		}
		
		//-----------------------------------------
		// Get validating info..
		//-----------------------------------------
		
		$validate = $this->ipsclass->DB->simple_exec_query( array( 'select' => '*', 'from' => 'validating', 'where' => 'member_id='.$user_id ) );
				
		if ( ! $validate['member_id'] )
		{
			$this->ipsclass->Error( array( 'LEVEL' => 1, 'MSG' => 'auth_no_key' ) );
		}
		
		//-----------------------------------------
		// REGISTER VALIDATE
		//-----------------------------------------
	
		$this->ipsclass->DB->do_update( 'members', array( 'mgroup' => intval($validate['real_group']) ), 'id='.intval($this->ipsclass->member['id']) );
		
		/*if ( USE_MODULES == 1 )
		{
			$this->modules->register_class($this);
			$this->modules->on_group_change( $member['id'], $validate['real_group'] );
		}*/
		
		//-----------------------------------------
		// Update the stats...
		//-----------------------------------------
	
		$this->ipsclass->cache['stats']['last_mem_name'] = $this->ipsclass->member['members_display_name'];
		$this->ipsclass->cache['stats']['last_mem_id']   = $this->ipsclass->member['id'];
		$this->ipsclass->cache['stats']['mem_count']    += 1;
		
		$this->ipsclass->update_cache(  array( 'name' => 'stats', 'array' => 1, 'deletefirst' => 0 ) );
					 
		$this->ipsclass->my_setcookie("member_id"   , $this->ipsclass->member['id']              , 1);
		$this->ipsclass->my_setcookie("pass_hash"   , $this->ipsclass->member['member_login_key'], 1);
		
		//-----------------------------------------
		// Remove "dead" validation
		//-----------------------------------------
		
		$this->ipsclass->DB->simple_exec_query( array( 'delete' => 'validating', 'where' => "member_id=".$this->ipsclass->member['id'] ) );
		
		//$this->bash_dead_validations();
			
		//$this->ipsclass->boink_it($this->ipsclass->base_url.'&act=Login&CODE=autologin&fromreg=1');
		
		require_once ROOT_PATH."sources/lib/admin_functions.php";
		$admin = new admin_functions();
		$admin->ipsclass = $this->ipsclass;
		$admin->save_log("Пользователь ".$this->ipsclass->member['name']."(".$this->ipsclass->member['members_display_name'].") успешно прошёл тест и активирован");
 	}
}

?>
