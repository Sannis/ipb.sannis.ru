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
+	All credits to Sannis or ibpower.ru
+ 	must remain in tact or use of this
+	modification is not permitted!
+
+------------------------------------------*/

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Некорректный адрес</h1>Вы не имеете доступа к этому файлу напрямую. Если вы недавно обновляли форум, вы должны обновить все соответствующие файлы.";
	exit();
}

class component_public
{
	
	# Classes
	var $ipsclass;
	
	# Others
    var $output     = "";
    var $page_title = "";
    var $nav        = array();

	function init()
	{
		//-----------------------------------------
    	// Require the HTML and language modules
    	//-----------------------------------------
    	
		$this->ipsclass->load_language('lang_trivia');
    	$this->ipsclass->load_template('skin_trivia');

		$copyright = "<!-- Copyright Information -->
                          <div align='center' class='copyright'>
                              Trivia System 2.0.3 by <a href='http://ipbmods.net' target='_blank'>bfarber</a>, Викторина 2.2.0 от <a href='http://ibpower.ru/index.php?showuser=33' target='_blank'>San</a><a href='http://www.ibresource.ru/forums/index.php?showuser=36662' target='_blank'>nis</a>";

        if ( $this->ipsclass->vars['ipb_reg_show'] and $this->ipsclass->vars['ipb_reg_name'] )
		{
			$copyright .= "<br />Лицензия зарегистрирована на: ". $this->ipsclass->vars['ipb_reg_name']."</div>";
		}
		else
		{
			$copyright .= "</div>";
		}
		$copyright .= "<!-- / Copyright -->";
		
		$this->ipsclass->skin[ '_wrapper' ] = str_replace( '<% COPYRIGHT %>', "<% COPYRIGHT %>$copyright", $this->ipsclass->skin[ '_wrapper' ] );
		
		$this->nav[] = "<a href='{$this->ipsclass->base_url}autocom=trivia'>{$this->ipsclass->lang['nav_lang']}</a>";
		$this->page_title = $this->ipsclass->vars['board_name'] ." -> ". $this->ipsclass->lang['page_title'];
	}

    function run_component()
    {
		if((!($this->ipsclass->member['id'] > 0 )) and ($this->ipsclass->vars['t_guests_view'] == 'no'))
		{
			$this->ipsclass->Error( array( 'MSG' => 'no_guests') );
		}
		
		// Initialization method
	    $this->init();

		// Save a query (or more) if not auto-cutting off sessions
		if($this->ipsclass->vars['t_resume'] == "0")
		{
			$this->do_cleanup();
		}

		//Main switch
		switch($this->ipsclass->input['CODE'])
		{
			case '01':
				$this->main_page();
				break;
			case '02':
				$this->start_session();
				break;
			case '03':
				$this->cont_session();
				break;
			case '04':
				$this->end_session();
				break;

			case 'hide':
				$this->hide_question();
				break;

			case 'unhide':
				$this->unhide_question();
				break;
			case 'delete':
				$this->delete_question();
				break;

			case 'addquestion':
				$this->add_question();
				break;

			case 'doaddquestion':
				$this->do_add_question();
				break;


			default:
				$this->main_page();
				break;
		}

		//------------------------------------------------------
		// IPB loves to mess with your line breaks. ;)
		//------------------------------------------------------

		$this->output = str_replace( "&lt;br&gt;"  , "<br />", $this->output );
		$this->output = str_replace( "&lt;br /&gt;"  , "<br />", $this->output );

		//--------------------------------------------------
    	// If we have any HTML to print, do so...
    	//--------------------------------------------------
    	
    	$this->ipsclass->print->add_output("$this->output");
        $this->ipsclass->print->do_output( array( 'TITLE' => $this->page_title, 'JS' => 0, NAV => $this->nav ) );    		
 	}


	function main_page() 
	{
		//------------------------------------------
        // Load and config the post parser
        //------------------------------------------
        require_once( ROOT_PATH."sources/handlers/han_parse_bbcode.php" );
        $parser                      =  new parse_bbcode();
        $parser->ipsclass            =& $this->ipsclass;

		$parser->parse_html    = 0;
		$parser->parse_smilies = 1;
		$parser->parse_bbcode  = 1;
		$parser->nl2br  = 1;
		
		$fix_welcome_message = $parser->pre_display_parse($parser->pre_db_parse($this->ipsclass->vars['t_welcome_message']));
		
		$this->output .= $this->ipsclass->compiled_templates['skin_trivia']->main_page_block($fix_welcome_message);

		//---------------------------
        // Show action buttons
        //---------------------------
		
		$buttons = array();
		$buttons[0] = $this->ipsclass->compiled_templates['skin_trivia']->button_new();
		
		//--------------------------
        // Show other buttons
        //--------------------------	
		
		$q = $this->ipsclass->DB->query("SELECT * FROM ".SQL_PREFIX."trivia_sessions WHERE mid={$this->ipsclass->member['id']} AND current=1 LIMIT 1;");
		if( $this->ipsclass->DB->get_num_rows($q) > 0 )
		{
			$buttons[1] = $this->ipsclass->compiled_templates['skin_trivia']->button_continue();			
			$buttons[2] = $this->ipsclass->compiled_templates['skin_trivia']->button_finalize();
		}
		
		if($this->allow_add())
		{
			$buttons[3] = $this->ipsclass->compiled_templates['skin_trivia']->button_add_question();
		}

		$this->output .= $this->ipsclass->compiled_templates['skin_trivia']->main_page_end($buttons);

		//------------------------
        // Show Trivia stats
        //------------------------

		$q1 = $this->ipsclass->DB->query("SELECT * FROM ".SQL_PREFIX."trivia_sessions ORDER BY mostcorrect DESC LIMIT 0,5;");
		$block1 = $this->ipsclass->compiled_templates['skin_trivia']->stat_block_start($this->ipsclass->lang['most_correct'], $this->ipsclass->lang['stat_name'], $this->ipsclass->lang['stat_questions']);
		while($r1 = $this->ipsclass->DB->fetch_row($q1))
		{
			$r1['data'] = $r1['mostcorrect'];
			$r1['mname'] = $this->ipsclass->make_profile_link($r1['mname'], $r1['mid']);
			$block1 .= $this->ipsclass->compiled_templates['skin_trivia']->stat_block_row($r1);
		}
		$block1 .= $this->ipsclass->compiled_templates['skin_trivia']->stat_block_end();

		$q2 = $this->ipsclass->DB->query("SELECT SUM(trivia_correct) as thesum,mid,mname FROM ".SQL_PREFIX."trivia_sessions GROUP BY mid ORDER BY thesum DESC LIMIT 0,5;");
		$block2 = $this->ipsclass->compiled_templates['skin_trivia']->stat_block_start($this->ipsclass->lang['most_served'], $this->ipsclass->lang['stat_name'], $this->ipsclass->lang['stat_answers']);
		while($r2 = $this->ipsclass->DB->fetch_row($q2))
		{
			$r2['data'] = $r2['thesum'];
			$r2['mname'] = $this->ipsclass->make_profile_link($r2['mname'], $r2['mid']);
			$block2 .= $this->ipsclass->compiled_templates['skin_trivia']->stat_block_row($r2);
		}
		$block2 .= $this->ipsclass->compiled_templates['skin_trivia']->stat_block_end();

		$q3 = $this->ipsclass->DB->query("SELECT * FROM ".SQL_PREFIX."trivia ORDER BY served DESC LIMIT 0,5;");
		$block3 = $this->ipsclass->compiled_templates['skin_trivia']->stat_block_start($this->ipsclass->lang['top_question'], $this->ipsclass->lang['stat_question'], $this->ipsclass->lang['stat_served']);
		while($r3 = $this->ipsclass->DB->fetch_row($q3))
		{
			$r3['data'] = $r3['served'];
			$r3['mname'] = $this->onlyfive($r3['question']);
			$block3 .= $this->ipsclass->compiled_templates['skin_trivia']->stat_block_row($r3);
		}
		$block3 .= $this->ipsclass->compiled_templates['skin_trivia']->stat_block_end();

		$q4 = $this->ipsclass->DB->query("SELECT * FROM ".SQL_PREFIX."trivia ORDER BY correct DESC LIMIT 0,5;");
		$block4 = $this->ipsclass->compiled_templates['skin_trivia']->stat_block_start($this->ipsclass->lang['most_answered'], $this->ipsclass->lang['stat_question'], $this->ipsclass->lang['stat_answers']);
		while($r4 = $this->ipsclass->DB->fetch_row($q4))
		{
			$r4['data'] = $r4['correct'];
			$r4['mname'] = $this->onlyfive($r4['question']);
			$block4 .= $this->ipsclass->compiled_templates['skin_trivia']->stat_block_row($r4);
		}
		$block4 .= $this->ipsclass->compiled_templates['skin_trivia']->stat_block_end();

		$this->output .= $this->ipsclass->compiled_templates['skin_trivia']->stat_block_layout($block1,$block2,$block3,$block4);
		
		if(!$this->allow_mod())
		{
			return;
		}
		
		//--------------------------------------------------
        // Show hidden questions for moderators
        //--------------------------------------------------
		
		$q_new = $this->ipsclass->DB->query("SELECT * FROM ".SQL_PREFIX."trivia WHERE open=0 and hidden_by=0 ORDER BY hidden_on DESC;");
		$block_new = $this->ipsclass->compiled_templates['skin_trivia']->stat_block_start($this->ipsclass->lang['mod_new_questions'], $this->ipsclass->lang['mod_options']);
		if($this->ipsclass->DB->get_num_rows($q_new) == 0)
		{
			$r5['mname'] = $this->ipsclass->lang['no_mod_questions'];
			$r5['data'] = "&nbsp;";
			$block_new .= $this->ipsclass->compiled_templates['skin_trivia']->stat_block_row($r5);
		}
		else
		{
			while($r5 = $this->ipsclass->DB->fetch_row($q_new))
			{
				$r5['mname'] = $r5['question']."&nbsp;—:—&nbsp;".$r5['answer'];
				$r5['data'] = "<a href='{$this->ipsclass->base_url}autocom=trivia&CODE=unhide&id={$r5['id']}'>{$this->ipsclass->lang['mod_unhide']}</a> | <a href='javascript:delete_question({$r5['id']})'>{$this->ipsclass->lang['mod_delete']}</a>";
				$block_new .= $this->ipsclass->compiled_templates['skin_trivia']->stat_block_row($r5);
			}
		}
		$block_new .= $this->ipsclass->compiled_templates['skin_trivia']->stat_block_end();
		
		$q_hidden = $this->ipsclass->DB->query("SELECT * FROM ".SQL_PREFIX."trivia WHERE open=0 and hidden_by!=0 ORDER BY hidden_on DESC;");
		$block_hidden = $this->ipsclass->compiled_templates['skin_trivia']->stat_block_start($this->ipsclass->lang['mod_hidden_questions'], $this->ipsclass->lang['mod_options']);
		if($this->ipsclass->DB->get_num_rows($q_hidden) == 0)
		{
			$r6['mname'] = $this->ipsclass->lang['no_mod_questions'];
			$r6['data'] = "&nbsp;";
			$block_hidden .= $this->ipsclass->compiled_templates['skin_trivia']->stat_block_row($r6);
		}
		else
		{
			while($r6 = $this->ipsclass->DB->fetch_row($q_hidden))
			{
				$r6['mname'] = $r6['question']."&nbsp;—:—&nbsp;".$r6['answer'];
				$r6['data'] = "<a href='{$this->ipsclass->base_url}autocom=trivia&CODE=unhide&id={$r6['id']}'>{$this->ipsclass->lang['mod_unhide']}</a>";
				$block_hidden .= $this->ipsclass->compiled_templates['skin_trivia']->stat_block_row($r6);
			}
		}
		$block_hidden .= $this->ipsclass->compiled_templates['skin_trivia']->stat_block_end();
		
		$this->output .= $this->ipsclass->compiled_templates['skin_trivia']->mod_block_layout($block_new, $block_hidden);
	}


	function get_next_question()
	{
		$query = "";
		
		$concatids = "";
		if($this->ipsclass->member['id'])
		{
			$query = $this->ipsclass->DB->query("SELECT qid FROM ".SQL_PREFIX."trivia_answers WHERE mid={$this->ipsclass->member['id']};");
			while($res = $this->ipsclass->DB->fetch_row($query))
			{
				$concatids .= $res['qid'].",";
			}
			$concatids = substr($concatids, 0, strlen($concatids)-1);
		}
		
		$extra = "";
		if($concatids and $this->ipsclass->vars['t_answer_once'] and !$this->ipsclass->member['g_access_cp'])
		{
			$extra = "AND id NOT IN ($concatids)";
		}
		

		$query = $this->ipsclass->DB->query("SELECT * FROM ".SQL_PREFIX."trivia WHERE open=1 $extra ORDER BY RAND() LIMIT 1;");
		if($this->ipsclass->DB->get_num_rows($query) == 0)
		{
			$qrow = array('id' => 0);
		}
		else
		{
			$qrow = $this->ipsclass->DB->fetch_row($query);
		}
		
		return $qrow;
	}

	
	function start_session()
	{
		if(!($this->ipsclass->member['id'] > 0 ))
		{
			if($this->ipsclass->vars['t_guests_view'] != 'all')
			{
				$this->ipsclass->Error( array( 'MSG' => 'no_guests') );
			}
		}
		
		// We don't want more than one session going at a time, that's just confusing lol
		$this->ipsclass->DB->query("UPDATE ".SQL_PREFIX."trivia_sessions SET session_end='".time()."', current=0 WHERE mid={$this->ipsclass->member['id']} AND current=1;");

		// Get first question
		$qrow = $this->get_next_question();
		if( !$qrow['id'] )
		{
			$this->output .= $this->ipsclass->compiled_templates['skin_trivia']->no_question_block();
			return;
		}

		$sessid =  md5( uniqid( microtime() ) );
		$to_insert = array( 'tsid' 			=> $sessid,
						'mid' 				=> $this->ipsclass->member['id'],
						'mname' 			=> $this->ipsclass->member['members_display_name'],
						'trivia_served' 	=> 1,
						'trivia_correct' 	=> 0,
						'trivia_incorrect' 	=> 0,
						'session_start' 	=> time(),
						'session_activity' 	=> time(),
						'session_end' 		=> 0,
						'current' 			=> 1,
						'mostcorrect' 		=> 0,
						'currentcorrect' 	=> 0,
				);
		$this->ipsclass->DBstring = $this->ipsclass->DB->compile_db_insert_string($to_insert);
		$this->ipsclass->DB->query("INSERT INTO ".SQL_PREFIX."trivia_sessions (".$this->ipsclass->DBstring['FIELD_NAMES'].") VALUES (".$this->ipsclass->DBstring['FIELD_VALUES'].")");

		$this->output .= $this->ipsclass->compiled_templates['skin_trivia']->question_block($qrow);
		$this->ipsclass->DB->query("UPDATE ".SQL_PREFIX."trivia SET served=served+1 WHERE id='{$qrow['id']}'");

		$buttons = array();
		$buttons[0] = $this->ipsclass->compiled_templates['skin_trivia']->button_cont_c();
		$buttons[1] = $this->ipsclass->compiled_templates['skin_trivia']->button_finalize();

		
		$this->output .= $this->ipsclass->compiled_templates['skin_trivia']->main_page_end($buttons);
	}
	
	
	function save_answer_to_log($correct)
	{
		$to_insert = array( 'ip_address' 	=> $this->ipsclass->ip_address,
						'mid' 				=> $this->ipsclass->member['id'],
						'mname' 			=> "{$this->ipsclass->member['members_display_name']} (Логин: {$this->ipsclass->member['name']})",
						'date' 				=> time(),
						'qid' 				=> intval($this->ipsclass->input['id']),
						'answer' 			=> $this->ipsclass->input['the_answer'],
						'correct' 			=> $correct,
				);
		$this->ipsclass->DBstring = $this->ipsclass->DB->compile_db_insert_string($to_insert);
		$this->ipsclass->DB->query("INSERT INTO ".SQL_PREFIX."trivia_answers (".$this->ipsclass->DBstring['FIELD_NAMES'].") VALUES (".$this->ipsclass->DBstring['FIELD_VALUES'].");");
	}
	

	function cont_session()
	{	
		if(!($this->ipsclass->member['id'] > 0 ))
		{
			if($this->ipsclass->vars['t_guests_view'] != 'all')
			{
				$this->ipsclass->Error( array( 'MSG' => 'no_guests') );
			}
		}
		
		$member = $this->ipsclass->DB->query("SELECT * FROM ".SQL_PREFIX."trivia_sessions WHERE mid='{$this->ipsclass->member['id']}' AND current=1 LIMIT 1;");
		$meminfo = $this->ipsclass->DB->fetch_row($member);

		// Did we have input?  If so, a question was just answered.  If not, we are continuing from a past session.
		if($this->ipsclass->input['id'] != "0" && $this->ipsclass->input['id'] != "")
		{
			$correct = 0;
			if( ( $this->ipsclass->vars['t_time_restrict'] > 0 ) and ( time() > ($meminfo['session_activity'] + $this->ipsclass->vars['t_time_restrict']) ) )
			{
				$pqrow['flag'] = $this->ipsclass->lang['outoftime'];
				$this->ipsclass->DB->query("UPDATE ".SQL_PREFIX."trivia_sessions SET trivia_incorrect=trivia_incorrect+1, session_activity='".time()."', currentcorrect='0' WHERE tsid='{$meminfo['tsid']}';");
				$correct = 2;
			}
			else
			{
				$pquestion = $this->ipsclass->DB->query("SELECT * FROM ".SQL_PREFIX."trivia WHERE open=1 AND id='".intval($this->ipsclass->input['id'])."' LIMIT 1");
				$pqrow = $this->ipsclass->DB->fetch_row($pquestion);
				$correct = (strtolower(trim($this->ipsclass->input['the_answer'])) == strtolower($pqrow['answer']))?1:0;
				if($correct)
				{
					$pqrow['flag'] = $this->ipsclass->lang['correct'];
					$this->ipsclass->DB->query("UPDATE ".SQL_PREFIX."trivia SET correct=correct+1 WHERE id='{$pqrow['id']}'");
					if($meminfo['currentcorrect']+1 > $meminfo['mostcorrect'])
					{
						$num = $meminfo['currentcorrect']+1;
						$toqry = ",mostcorrect={$num}";
					}
					$this->ipsclass->DB->query("UPDATE ".SQL_PREFIX."trivia_sessions SET trivia_correct=trivia_correct+1, session_activity='".time()."', currentcorrect=currentcorrect+1 {$toqry} WHERE tsid='{$meminfo['tsid']}'");
				}
				else
				{
					$pqrow['flag'] = $this->ipsclass->lang['incorrect'];
					$this->ipsclass->DB->query("UPDATE ".SQL_PREFIX."trivia_sessions SET trivia_incorrect=trivia_incorrect+1, session_activity='".time()."', currentcorrect='0' WHERE tsid='{$meminfo['tsid']}'");
				}
			}
			$pqrow['prev_ans'] = $this->ipsclass->input['the_answer'];
			$this->save_answer_to_log($correct);
			
			$mod = "   <div><span class='fauxbutton'><a href=\"javascript:PopUp('{$this->ipsclass->base_url}autocom=trivia&CODE=hide&id={$pqrow['id']}','Hide','400','300','1','1','1','0','0');\">{$this->ipsclass->lang['button_hide']}</a></span><br />\n</div>";

			$this->output .= $this->ipsclass->compiled_templates['skin_trivia']->prev_question_block($pqrow, $this->allow_mod()?$mod:"");
		}

		$this->ipsclass->DB->query("UPDATE ".SQL_PREFIX."trivia_sessions SET trivia_served=trivia_served+1,session_activity='".time()."' WHERE tsid='{$meminfo['tsid']}'");	
		$this->ipsclass->DB->query("UPDATE ".SQL_PREFIX."trivia SET served=served+1 WHERE id='{$qrow['id']}'");

		// Get next question
		$qrow = $this->get_next_question();
		if( !$qrow['id'] )
		{
			$this->output .= $this->ipsclass->compiled_templates['skin_trivia']->no_question_block();
			return;
		}
		
		$this->output .= $this->ipsclass->compiled_templates['skin_trivia']->question_block($qrow);

		$buttons = array();
		$buttons[0] = $this->ipsclass->compiled_templates['skin_trivia']->button_cont_c();
		$buttons[1] = $this->ipsclass->compiled_templates['skin_trivia']->button_finalize();

		$this->output .= $this->ipsclass->compiled_templates['skin_trivia']->main_page_end($buttons);
	}


	function do_cleanup()
	{
		if(!($this->ipsclass->member['id'] > 0 ))
		{
			if($this->ipsclass->vars['t_guests_view'] != 'all')
			{
				$this->ipsclass->Error( array( 'MSG' => 'no_guests') );
			}
		}

		if($this->ipsclass->vars['t_sess_cutoff'] == "")
		{
			$this->ipsclass->vars['t_sess_cutoff'] = "0";
		}
		if($this->ipsclass->vars['t_sess_cutoff'] == "0")
		{
			return;
		}
		if($this->ipsclass->vars['t_resume'] == "0")
		{
			return;
		}

		$cutoff = time() - $this->ipsclass->vars['t_sess_cutoff'];
		$qry = $this->ipsclass->DB->query("SELECT * FROM ".SQL_PREFIX."trivia_sessions WHERE session_activity < {$cutoff};");
		while($r = $this->ipsclass->DB->fetch_row($qry))
		{
			$this->ipsclass->DB->query("UPDATE ".SQL_PREFIX."trivia_sessions SET session_end='".time()."', current=0 WHERE tsid='{$r['tsid']}';");
		}
		return;
	}

	function end_session()
	{
		if(!($this->ipsclass->member['id'] > 0 ))
		{
			if($this->ipsclass->vars['t_guests_view'] != 'all')
			{
				$this->ipsclass->Error( array( 'MSG' => 'no_guests') );
			}
		}
		
		$member = $this->ipsclass->DB->query("SELECT * FROM ".SQL_PREFIX."trivia_sessions WHERE mid='{$this->ipsclass->member['id']}' AND current='1' LIMIT 1;");
		if($this->ipsclass->DB->get_num_rows($member) == 0)
		{
			$this->ipsclass->print->redirect_screen("{$this->ipsclass->lang['redirect_notended']}", "autocom=trivia" );
			exit();
		}
		$meminfo = $this->ipsclass->DB->fetch_row($member);

		// Did we have input?  If so, a question was just answered.  If not, we are continuing from a past session.
		if($this->ipsclass->input['id'] != "0" && $this->ipsclass->input['id'] != "")
		{
			$pquestion = $this->ipsclass->DB->query("SELECT * FROM ".SQL_PREFIX."trivia WHERE open=1 AND id=".intval($this->ipsclass->input['id'])." LIMIT 1;");
			$pqrow = $this->ipsclass->DB->fetch_row($pquestion);
			if(strtolower($this->ipsclass->input['the_answer']) == strtolower($pqrow['answer']))
			{
				$this->ipsclass->DB->query("UPDATE ".SQL_PREFIX."trivia SET correct=correct+1 WHERE id={$pqrow['id']};");
				if($meminfo['currentcorrect']+1 > $meminfo['mostcorrect'])
				{
					$toqry = ",mostcorrect='".($meminfo['currentcorrect']+1);
				}
				$this->ipsclass->DB->query("UPDATE ".SQL_PREFIX."trivia_sessions SET trivia_correct=trivia_correct+1, session_activity='".time()."', currentcorrect=currentcorrect+1 {$toqry} WHERE tsid='{$meminfo['tsid']}';");
			}
			else
			{
				$this->ipsclass->DB->query("UPDATE ".SQL_PREFIX."trivia_sessions SET trivia_incorrect=trivia_incorrect+1, session_activity='".time()."', currentcorrect='0' WHERE tsid='{$meminfo['tsid']}';");
			}

			$this->ipsclass->DB->query("UPDATE ".SQL_PREFIX."trivia_sessions SET session_end='".time()."', current='0' WHERE tsid='{$meminfo['tsid']}';");
		}
		else
		{
			$this->ipsclass->DB->query("UPDATE ".SQL_PREFIX."trivia_sessions SET session_end='".time()."', current='0' WHERE tsid='{$meminfo['tsid']}';");
		}	

		// If there are any strays out there, let's just kill them now....
		$this->ipsclass->DB->query("UPDATE ".SQL_PREFIX."trivia_sessions SET session_end='".time()."', current='0' WHERE mid='{$this->ipsclass->member['id']}' AND current='1';");

		$this->ipsclass->print->redirect_screen("{$this->ipsclass->lang['redirect_ended']}", "autocom=trivia" );
		exit();
	}
	
	function allow_mod()
	{
		if(!$this->ipsclass->member['id'])
		{
			return false;
		}
	
		if($this->ipsclass->member['g_access_cp'])
		{
			return true;
		}
		
		$mod_list = explode(',', $this->ipsclass->vars['t_mod_list']);

		foreach($mod_list as $mod_id)
		{
			if($this->ipsclass->member['id'] == intval(trim($mod_id)))
			{
				return true;
			}
		}
			
		return false;
	}

	function hide_question()
	{
		$output = "";
		if(! $this->allow_mod())
		{
			$output = $this->ipsclass->compiled_templates['skin_trivia']->for_popup($this->ipsclass->lang['question_perm']);
			$this->ipsclass->print->pop_up_window($title = "{$this->ipsclass->lang['page_title']}", $text = "$output");
		}
		$this->ipsclass->input['id'] = intval($this->ipsclass->input['id']);
		if(!$this->ipsclass->input['id'])
		{
			$output = $this->ipsclass->compiled_templates['skin_trivia']->for_popup($this->ipsclass->lang['question_nothidden']);
			$this->ipsclass->print->pop_up_window($title = "{$this->ipsclass->lang['page_title']}", $text = "{$output}");
		}
		$this->ipsclass->DB->query("UPDATE ".SQL_PREFIX."trivia SET open=0, hidden_by={$this->ipsclass->member['id']}, hidden_on='".time()."' WHERE id={$this->ipsclass->input['id']};");
		$output = $this->ipsclass->compiled_templates['skin_trivia']->for_popup($this->ipsclass->lang['question_hidden']);
		$this->ipsclass->print->pop_up_window("{$this->ipsclass->lang['page_title']}", "$output");
	}
	
	function unhide_question()
	{
		$output = "";
		if(! $this->allow_mod())
		{
			$output = $this->ipsclass->compiled_templates['skin_trivia']->for_popup($this->ipsclass->lang['question_perm']);
			$this->ipsclass->print->pop_up_window($title = "{$this->ipsclass->lang['page_title']}", $text = "$output");
		}
		$this->ipsclass->input['id'] = intval($this->ipsclass->input['id']);
		if(!$this->ipsclass->input['id'])
		{
			$output = $this->ipsclass->compiled_templates['skin_trivia']->for_popup($this->ipsclass->lang['question_nothidden']);
			$this->ipsclass->print->pop_up_window($title = "{$this->ipsclass->lang['page_title']}", $text = "{$output}");
		}
		$this->ipsclass->DB->query("UPDATE ".SQL_PREFIX."trivia SET open=1, hidden_by={$this->ipsclass->member['id']} WHERE id={$this->ipsclass->input['id']};");
		$this->ipsclass->boink_it( $this->ipsclass->base_url.'autocom=trivia' );
	}
	
	function delete_question()
	{
		$output = "";
		if(! $this->allow_mod())
		{
			$output = $this->ipsclass->compiled_templates['skin_trivia']->for_popup($this->ipsclass->lang['question_perm']);
			$this->ipsclass->print->pop_up_window($title = "{$this->ipsclass->lang['page_title']}", $text = "$output");
		}
		$this->ipsclass->input['id'] = intval($this->ipsclass->input['id']);
		if(!$this->ipsclass->input['id'])
		{
			$output = $this->ipsclass->compiled_templates['skin_trivia']->for_popup($this->ipsclass->lang['question_nothidden']);
			$this->ipsclass->print->pop_up_window($title = "{$this->ipsclass->lang['page_title']}", $text = "{$output}");
		}
		$this->ipsclass->DB->query("DELETE FROM ".SQL_PREFIX."trivia WHERE id={$this->ipsclass->input['id']} LIMIT 1;");
		$this->ipsclass->boink_it( $this->ipsclass->base_url.'autocom=trivia' );
	}
	
	function allow_add()
	{
		if($this->ipsclass->member['g_access_cp'])
		{
			return true;
		}	
		if( ($this->ipsclass->member['id'] > 0) and $this->ipsclass->vars['t_allow_add_questions'] )
		{
			return true;
		}
			
		return false;
	}
	
	function add_question()
	{
		$output = "";
		if(!$this->allow_add())
		{
			$this->ipsclass->Error( array( 'MSG' => 'question_perm') );
		}
		$this->output .= $this->ipsclass->compiled_templates['skin_trivia']->main_page_block($this->ipsclass->lang['add_question_msg']);		
		$this->output .= $this->ipsclass->compiled_templates['skin_trivia']->add_question_form();
		$this->nav[] = "<a href='{$this->ipsclass->base_url}autocom=trivia&CODE=addquestion'>{$this->ipsclass->lang['nav_add_question']}</a>";
	}
	
	function do_add_question()
	{
		$output = "";
		if(!$this->allow_add())
		{
			$this->ipsclass->Error( array( 'MSG' => 'question_perm') );
		}
		
		$the_question = $this->make_safe_text($this->ipsclass->input['the_question']?trim($this->ipsclass->input['the_question']):"");
		$the_answer = $this->make_safe_text($this->ipsclass->input['the_answer']?trim($this->ipsclass->input['the_answer']):"");
		
		if($the_question == "")
		{
			$this->ipsclass->Error( array( 'MSG' => 'no_question') );
			exit();
		}
		if($the_answer == "")
		{
			$this->ipsclass->Error( array( 'MSG' => 'no_answer') );
			exit();
		}

		$this->ipsclass->DB->query("INSERT INTO ".SQL_PREFIX."trivia (`open`,`question`,`answer`,`date`,`hidden_by`,`hidden_on`) VALUES (0,'".addslashes($the_question)."','".addslashes($the_answer)."','".time()."',0,'".time()."')");
		$id = $this->ipsclass->DB->get_insert_id();
		
		require_once ROOT_PATH."sources/lib/admin_functions.php";
		$admin = new admin_functions();
		$admin->ipsclass = $this->ipsclass;
		$admin->save_log("Вопрос №{$id} добавлен в Викторину пользователем ".$this->ipsclass->make_profile_link($this->ipsclass->member['members_display_name'], $this->ipsclass->member['id']));
		
		$this->ipsclass->boink_it( $this->ipsclass->base_url.'autocom=trivia' );	
	}
	
	function make_safe_text($txt)
	{
		$txt = str_replace("<br />", "", $txt);
		$txt = str_replace( "\n", "", $txt );
		$txt = $this->ipsclass->remove_tags($txt);
		//Class_parce_bbcode
		$txt = preg_replace( "#javascript\:#is"    , "java script:", $txt );
		$txt = preg_replace( "#vb(.+?)?script\:#is", "vb script:"  , $txt );
		$txt = str_replace(  "`"                   , "&#96;"       , $txt );
		$txt = preg_replace( "#moz\-binding:#is"   , "moz binding:", $txt );
		$txt = str_replace(  "<script"			   , "&lt;script"  , $txt );
		
		$txt = trim($txt);
		
		return $txt;
	}
	
	function onlyfive($str)
	{
		$l = strlen($str);
		$string = array();
		$p = 0;
		$c = 1;
		$z = 0;
		
		do
		{
			$p = @strpos($str, chr(32),40);
			if ($p === false) 
			{
				$string[$c] = $str;
				break;
			}
			$string[$c] = substr($str, 0, $p+1);
			$str = substr($str,$p+1,$l-1);
			$c++;
			$z += $p;
		}
		while ($z<$l);
	
		$str = implode('<br/>',$string);
		
		return $str;
	}

}
?>