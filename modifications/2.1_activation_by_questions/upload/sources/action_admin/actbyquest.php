<?php

/*
+---------------------------------------------------------------------------
|   ActByQuest Mod v1.2
|   =============================================
|   > $Date: 2007-03-21 $
|   > $Author: Oleg "Sannis" Efimov <efimovov@yandex.ru> $
+---------------------------------------------------------------------------
*/

if ( ! defined( 'IN_ACP' ) )
{
	print "<h1>Неверный вход</h1> У Вас нет доступа к директиве этого файла. Если Вы проводили обновления, убедитесь, что не забыли обновить 'admin.php'.";
	exit();
}


class ad_actbyquest
{
	function auto_run()
	{
		//-----------------------------------------
		// Kill globals - globals bad, Homer good.
		//-----------------------------------------
		
		$tmp_in = array_merge( $_GET, $_POST, $_COOKIE );
		
		foreach ( $tmp_in as $k => $v )
		{
			unset($$k);
		}
		
		//-----------------------------------------

		switch($this->ipsclass->input['code'])
		{
			case 'edit':
				$this->show_form('edit');
				break;
			case 'new':
				$this->show_form('new');
				break;
			
			case 'doedit':
				$this->doedit();
				break;
				
			case 'donew':
				$this->doadd();
				break;
				
			case 'remove':
				$this->remove();
				break;
			
			//-----------------------------------------
			default:
				$this->list_files();
				break;
		}
		
	}
	
	//-----------------------------------------
	// ActByQuest FUNCTIONS
	//-----------------------------------------
	
	function doedit()
	{
		if ($this->ipsclass->input['id'] == "")
		{
			$this->ipsclass->admin->error("Вы должны ввести правильный ID вопроса!");
		}
		
		$question  = preg_replace( "/\n/", "<br>", stripslashes($_POST['question'] ) );
		$answer  = preg_replace( "/\n/", "<br>", stripslashes($_POST['answer'] ) );
		
		$text  = preg_replace( "/\\\/", "&#092;", $text );
		
		$this->ipsclass->DB->do_update( 'actbyquest', array( 'question'       => $question,
													  'answer'        => $answer,
											  ), "id=".intval($this->ipsclass->input['id'])     );
		
		$this->ipsclass->admin->save_log("Изменен вопрос для модуля ActByQuest");
		
		$this->ipsclass->boink_it($this->ipsclass->base_url."&{$this->ipsclass->form_code}");
		exit();
			
		
	}
	
	//=====================================================
	
	
	function show_form($type='new')
	{
		$this->ipsclass->admin->page_detail = "В этой секции вы можете добавлять, изменять и удалять вопросы для модуля ActByQuest.";
		$this->ipsclass->admin->page_title  = "Управление вопросами для активации";
		
		//-----------------------------------------
		
		if ($type != 'new')
		{
		
			if ($this->ipsclass->input['id'] == "")
			{
				$this->ipsclass->admin->error("Вы должны ввести правильный ID вопроса!");
			}
		
			//-----------------------------------------
			
			$this->ipsclass->DB->simple_construct( array( 'select' => '*', 'from' => 'actbyquest', 'where' => "id=".intval($this->ipsclass->input['id']) ) );
			$this->ipsclass->DB->simple_exec();
		
			if ( ! $r = $this->ipsclass->DB->fetch_row() )
			{
				$this->ipsclass->admin->error("Невозможно найти в базе данных вопрос с таким ID");
			}
		
			//-----------------------------------------
			
			$button = 'Изменить этот вопрос';
			$code   = 'doedit';
		}
		else
		{
			$r = array();
			$button = 'Добавить этот вопрос';
			$code   = 'donew';
		}
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_form( array( 1 => array( 'code'  , $code ),
																			 2 => array( 'act'   , 'actbyquest'     ),
																			 3 => array( 'id'    , $this->ipsclass->input['id'] ),
																			 4 => array( 'section', $this->ipsclass->section_code ),
																	)      );
		
		
		
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;"  , "20%" );
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;"  , "80%" );
		
		$r['question'] = preg_replace( "/<br>/i", "\n", stripslashes($r['question']) );
 		
 		//-----------------------------------------
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( $button );
										 
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "Вопрос",
												  $this->ipsclass->adskin->form_textarea('question', stripslashes($r['question']) ),
										 )      );
										 
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "Ответ",
												  $this->ipsclass->adskin->form_input('answer'  , stripslashes($r['answer']) ),
										 )      );
										 
		$this->ipsclass->html .= $this->ipsclass->adskin->end_form($button);
										 
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		
		$this->ipsclass->admin->output();
	
	}
	
	//=====================================================
	
	function remove()
	{
		if ($this->ipsclass->input['id'] == "")
		{
			$this->ipsclass->admin->error("Вы должны ввести правильный ID вопроса!");
		}
		
		$this->ipsclass->DB->simple_exec_query( array( 'delete' => 'actbyquest', 'where' => "id=".$this->ipsclass->input['id'] ) );
	
		$this->ipsclass->admin->save_log("Удалён вопрос для модуля ActByQuest");
		
		$this->ipsclass->boink_it($this->ipsclass->base_url."&{$this->ipsclass->form_code}");
		exit();
			
		
	}
	
	//=====================================================
	
	function doadd()
	{
		if (($this->ipsclass->input['question'] == "") || ($this->ipsclass->input['answer'] == ""))
		{
			$this->ipsclass->admin->error("Вы должны ввести вопрос и ответ!");
		}
		
		$question  = preg_replace( "/\n/", "<br>", stripslashes($_POST['question'] ) );
		$answer = preg_replace( "/\n/", "<br>", stripslashes($_POST['answer'] ) );
		
		$question  = preg_replace( "/\\\/", "&#092;", $question );
		$answer  = preg_replace( "/\\\/", "&#092;", $answer );
		
		$this->ipsclass->DB->do_insert( 'actbyquest', array( 'question'       => $question,
									  'answer'        => $answer,
							 )      );
												  
		$this->ipsclass->admin->save_log("Добавлен вопрос для модуля ActByQuest");
		
		$this->ipsclass->boink_it($this->ipsclass->base_url."&{$this->ipsclass->form_code}");
		exit();	
	}
	
	//=====================================================
	
	function list_files()
	{
		$this->ipsclass->admin->page_detail = "В этой секции вы можете добавлять, изменять и удалять вопросы для модуля ActByQuest";
		$this->ipsclass->admin->page_title  = "Управление вопросами для модуля ActByQuest";
		
		//-----------------------------------------
		
		$this->ipsclass->adskin->td_header[] = array( "Вопрос"  , "50%" );
		$this->ipsclass->adskin->td_header[] = array( "Ответ"  , "20%" );
		$this->ipsclass->adskin->td_header[] = array( "Изменить"   , "15%" );
		$this->ipsclass->adskin->td_header[] = array( "Удалить" , "15%" );
		
		//-----------------------------------------
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "Текущие вопросы" );
		
		$this->ipsclass->DB->simple_construct( array( 'select' => '*', 'from' => 'actbyquest', 'order' => "id" ) );
		$this->ipsclass->DB->simple_exec();
		
		if ( $this->ipsclass->DB->get_num_rows() )
		{
			while ( $r = $this->ipsclass->DB->fetch_row() )
			{
				
				$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( stripslashes($r['question']),//"<b>".stripslashes($r['question'])."</b>"),
				                                          stripslashes($r['answer']),
														  "<center><a href='".$this->ipsclass->base_url."&{$this->ipsclass->form_code}&code=edit&id={$r['id']}'>Изменить</a></center>",
														  "<center><a href='".$this->ipsclass->base_url."&{$this->ipsclass->form_code}&code=remove&id={$r['id']}'>Удалить</a></center>",
												 )      );
												   
			
				
			}
		}
		
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_basic("<a href='".$this->ipsclass->base_url."&{$this->ipsclass->form_code}&code=new'>Добавить новый вопрос</a>", "center", "pformstrip" );
		
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		
		//-----------------------------------------
		
		$this->ipsclass->admin->output();
	}
	
	
}


?>
