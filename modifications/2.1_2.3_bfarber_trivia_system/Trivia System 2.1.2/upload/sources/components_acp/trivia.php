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
+	2.1.x version
+
+	Copyright 2007 by Sannis
+	All credits toSannis or ibpower.ru
+ 	must remain in tact or use of this
+	modification is not permitted!
+
+------------------------------------------*/

if ( ! defined( 'IN_ACP' ) )
{
	print "<h1>Некорректный адрес</h1>Вы не имеете доступа к этому файлу напрямую. Если вы недавно обновляли форум, вы должны обновить все соответствующие файлы.";
	exit();
}

class ad_trivia {

	#IPB Class
	
	var $ipsclass;

	//var $base_url;
	/**
	* Section title name
	*
	* @var	string
	*/
	var $perm_main = "admin";
	
	/**
	* Section title name
	*
	* @var	string
	*/
	var $perm_child = "trivia";

	var $lineitems = array();

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
		
		$this->ipsclass->admin->nav[] = array( 'act=trivia', 'Викторина - Администрирование' );
		
		//-----------------------------------------
        // Main switch
        //-----------------------------------------
		switch($this->ipsclass->input['code'])
		{
			case 'show':
				$this->show_splash();
				break;
			case 'settings':
				$this->settings();
				break;
			case 'import_export':
				$this->manage_questions();
				break;
			case 'doimport':
				$this->do_import();
				break;
			case 'doexport':
				$this->do_export();
				break;
			case 'unhide':
				$this->unhide();
				break;
			case 'delete':
				$this->delete();
				break;
			case 'deleteallhidden':
				$this->delete_all_hidden();
				break;
			case 'addpage':
				$this->add_page();
				break;
			case 'doadd':
				$this->do_add();
				break;


			//-------------------------
			default:
				$this->show_splash();
				break;
		}
		
	}
	
	
	function show_splash()
	{
		$this->ipsclass->admin->page_title = "Обзор Викторины";
		$this->ipsclass->admin->page_detail = "На этой странице вы можете ознакомиться со статистикой Викторины и управлять вопросами.";

		$qst = $this->ipsclass->DB->query("SELECT COUNT(id) as questions FROM ".SQL_PREFIX."trivia WHERE open=1");
		$questions = $this->ipsclass->DB->fetch_row($qst);
		$srv = $this->ipsclass->DB->query("SELECT SUM(trivia_served) as served FROM ".SQL_PREFIX."trivia_sessions");
		$served = $this->ipsclass->DB->fetch_row($srv);
		$served['served'] = $served['served'] != "" ? $served['served'] : 0;
		$crt = $this->ipsclass->DB->query("SELECT SUM(trivia_correct) as correct FROM ".SQL_PREFIX."trivia_sessions");
		$correct = $this->ipsclass->DB->fetch_row($crt);
		$correct['correct'] = $correct['correct'] != "" ? $correct['correct'] : 0;

		$percent_c = make_percent($correct['correct'], $served['served']);

		$query1 = $this->ipsclass->DB->query("SELECT t.*,m.name FROM ".SQL_PREFIX."trivia t LEFT JOIN ".SQL_PREFIX."members m ON (m.id=t.hidden_by) WHERE t.open=0 ORDER BY t.id ASC");
		while ($row = $this->ipsclass->DB->fetch_row($query1))
		{
			$hidden[] = $row;
		}

		$query2 = $this->ipsclass->DB->query("SELECT * FROM ".SQL_PREFIX."trivia WHERE served>0 ORDER BY served DESC LIMIT 0,5");
		while ($row1 = $this->ipsclass->DB->fetch_row($query2))
		{
			$topserved[] = $row1;
		}

		$query3 = $this->ipsclass->DB->query("SELECT * FROM ".SQL_PREFIX."trivia WHERE correct>0 ORDER BY correct DESC LIMIT 0,5");
		while ($row2 = $this->ipsclass->DB->fetch_row($query3))
		{
			$topcorrect[] = $row2;
		}

		//------------------------------------------------

		$this->ipsclass->adskin->td_header[] = array( "&nbsp;"   , "40%" );
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;"   , "60%" );
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "Статистика" );
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Всего вопросов</b>" , $questions['questions'] ) );
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Вопросов задано</b>" , $served['served'] ) );
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Правильных ответов</b>" , $correct['correct'] ) );
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Процент правильных ответов</b>" , $percent_c ) );

		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();

		//------------------------------------------------


		$this->ipsclass->adskin->td_header[] = array( "ID"   , "10%" );
		$this->ipsclass->adskin->td_header[] = array( "Вопрос"   , "50%" );
		$this->ipsclass->adskin->td_header[] = array( "Задано"   , "10%" );
		$this->ipsclass->adskin->td_header[] = array( "Ответов"   , "10%" );
		$this->ipsclass->adskin->td_header[] = array( "Процент верных"   , "20%" );
		

		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "Топ заданных вопросов" );

		if(count($topserved) > 0)
		{
			foreach($topserved as $to_out)
			{
				$percent_c = make_percent($to_out['correct'], $to_out['served']);
				$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( $to_out['id'] ,
												                 $to_out['question'],
												                 $to_out['served'],
												                 $to_out['correct'],
															$percent_c
									                    )      );
			}
		}
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();

		//------------------------------------------------

		//------------------------------------------------

		//------------------------------------------------

		$this->ipsclass->adskin->td_header[] = array( "ID"   , "10%" );
		$this->ipsclass->adskin->td_header[] = array( "Вопрос"   , "50%" );
		$this->ipsclass->adskin->td_header[] = array( "Задано"   , "10%" );
		$this->ipsclass->adskin->td_header[] = array( "Ответов"   , "10%" );
		$this->ipsclass->adskin->td_header[] = array( "Процент верных"   , "20%" );
		

		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "Топ по правильным ответам" );

		if(count($topcorrect) > 0)
		{
			foreach($topcorrect as $to_out)
			{
				$percent_c = make_percent($to_out['correct'], $to_out['served']);
				$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( $to_out['id'] ,
												                 $to_out['question'],
												                 $to_out['served'],
												                 $to_out['correct'],
															$percent_c
									                    )      );
			}
		}
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();

		//------------------------------------------------

		$this->ipsclass->adskin->td_header[] = array( "ID"   , "10%" );
		$this->ipsclass->adskin->td_header[] = array( "Вопрос"   , "25%" );
		$this->ipsclass->adskin->td_header[] = array( "Ответ"   , "15%" );
		$this->ipsclass->adskin->td_header[] = array( "Задано"   , "10%" );
		$this->ipsclass->adskin->td_header[] = array( "Верных ответов"   , "10%" );
		$this->ipsclass->adskin->td_header[] = array( "Кем скрыт (добавлен)"   , "10%" );
		$this->ipsclass->adskin->td_header[] = array( "Действие"   , "20%" );

		$title_hide = "Скрытые вопросы [<a href='{$this->ipsclass->base_url}&section=components&act=trivia&code=deleteallhidden'>Удалить все скрытые</a>]";
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( $title_hide );

		if(count($hidden) > 0)
		{
			foreach($hidden as $to_out)
			{
				$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( $to_out['id'] ,
												                 $to_out['question'],
												                 $to_out['answer'],
												                 $to_out['served'],
												                 $to_out['correct'],
												                 $to_out['name']?"<a href='{$this->ipsclass->board_url}/index.php?showuser={$to_out['hidden_by']}' target='_blank'>{$to_out['name']}</a>":"<i>добавлен</i>",
												                 "<a href='{$this->ipsclass->base_url}&section=components&act=trivia&code=unhide&id={$to_out['id']}'>Опубликовать</a> / <a href='{$this->ipsclass->base_url}&section=components&act=trivia&code=delete&id={$to_out['id']}'>Удалить</a>"
									                    )      );
			}
		}
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();

		//------------------------------------------------



		$this->ipsclass->admin->output();
	}

	function settings()
	{
		require_once( ROOT_PATH.'sources/action_admin/settings.php' );
		$settings             =  new ad_settings();
		$settings->ipsclass   =& $this->ipsclass;
		 
		$settings->get_by_key        = 'triviasettings';
		$settings->return_after_save = $this->ipsclass->form_code.'&code=settings';
		
		$settings->setting_view();		
	}

	function manage_questions()
	{
		$this->ipsclass->admin->page_title = "Импорт / Экспорт Викторины";
		$this->ipsclass->admin->page_detail = "На этой странице вы можете импортировать и экспортировать вопросы Викторины.";
		
		//Форма импорта
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "Импорт TRV-файла в Викторину" );
		$page_array = array( 1 => array( 'code'  , 'doimport'  ),
					 2 => array( 'act'   , 'trivia'       ),
					 3 => array( 'section', $this->ipsclass->section_code ),
				   );
		$this->ipsclass->html .= $this->ipsclass->adskin->start_form( $page_array,  'UploadForm' , "enctype='multipart/form-data'"          );
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;"   , "40%" );
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;"   , "60%" );
		$this->ipsclass->html .= "<tr><td colspan='2' class='tdrow1' width='100%'><b>Выберите файл для загрузки.</b></td></tr>";
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Обзор вашего компьютера</b><br /><i>Максимальный размер файла: ".ini_get('upload_max_filesize')."</i>" ,
												  "<input type='file' name='trv_file' class='textinput' size='50' />"
									     )      );
		$this->ipsclass->html .= "<tr><td colspan='2' class='tdrow1' width='100%' align='center'><b>ИЛИ</b></td></tr>";
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Введите имя файла</b><br /><i>Файл должен находиться в папке ./uploads/ форума</i>" ,
												  "<input type='text' name='trv_root' class='textinput' size='50' />"
									     )      );

		$this->ipsclass->html .= $this->ipsclass->adskin->end_form("Импортировать");
										 
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table()."<br />\n";
		
		//Форма экспорта
		
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "Экспорт Викторины в TRV-файл" );
		$page_array = array( 1 => array( 'code'  , 'doexport'  ),
					 2 => array( 'act'   , 'trivia'       ),
					 3 => array( 'section', $this->ipsclass->section_code ),
				   );
		$this->ipsclass->html .= $this->ipsclass->adskin->start_form( $page_array,  'ExportForm');
		$this->ipsclass->html .= "<tr><td class='tdrow1' width='100%'><b>Нажмите Экспортировать для сохранение вопросов Викторины в файл.</b><br /><i>Вы сможете скачать его из папки ./uploads/ на вашем сервере.</i></td></tr>";
		$this->ipsclass->html .= $this->ipsclass->adskin->end_form("Экспортировать");
										 
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		
		$this->ipsclass->admin->output();

	}

	function do_import()
	{
		if($_FILES['trv_file']['name'] AND !($_FILES['trv_file']['name']=='0' or $_FILES['trv_file']['name']=="none"))
		{
			$filename = $_FILES['trv_file']['name'];
			$path = $this->ipsclass->vars['upload_dir']."/";
			$filepath = $path.$filename;
			if (!@move_uploaded_file($_FILES['trv_file']['tmp_name'], $filepath))
			{
				$this->ipsclass->admin->error("Ошибка при загрузке файла. У вас нет прав доступа к корневой папке форума.");
			}

			$fh = @fopen($filepath, "r");
			if($fh)
			{
				$contents = @fread($fh,filesize($filepath));
			}
			else
			{
				$this->ipsclass->admin->error("Ошибка при откритии файла. Проверьте права доступа к файлу $filepath.");
			}

			$this->lineitems = explode("\n",$contents);
			$number = count($this->lineitems);
			
			$del = 1;
		}
		elseif($this->ipsclass->input['trv_root'] != "")
		{
			$filename = urldecode($this->ipsclass->input['trv_root']);
			$filepath = $this->ipsclass->vars['upload_dir']."/".$filename;
			if(!file_exists($filepath))
			{
				$this->ipsclass->admin->error("Ошибка: файл $filename отсутствует в папке форума ./uploads/");
			}
			$fh = @fopen($filepath, "r");
			if($fh)
			{
				$contents = @fread($fh,filesize($filepath));
			}
			else
			{
				$this->ipsclass->admin->error("Ошибка при откритии файла. Проверьте права доступа к файлу $filename.");
			}

			$this->lineitems = explode("\n",$contents);
			$number = count($this->lineitems);

			$del = 0;
		}
		else
		{
			$this->ipsclass->admin->error("Вы не выбрали файл для импорта");
		}

		$st = $this->ipsclass->input['st'] ? $this->ipsclass->input['st'] : 0;
		$num = 1000;

		$end = 0;
		if(($st+$num)>=$number)
		{
			$num = $number-$st;
			$end = 1;
		}
		$howmany = $st+$num;
		$this->import_shutdown($st,$num);
		$filename = urlencode($filename);

		if($end == 1)
		{
			if($del == 1)
				unlink($filepath);
			
			$this->ipsclass->admin->save_log("Импортировано {$howmany} из {$number} вопросов");
			$this->ipsclass->admin->done_screen("Импортировано {$howmany} из {$number} вопросов", "Викторина - Администрирование", "{$this->ipsclass->form_code}" , 'redirect' );		
		}
		else
		{
			$this->ipsclass->admin->save_log("Импортировано {$howmany} из {$number} вопросов");
			$this->ipsclass->admin->done_screen("Импортировано {$howmany} из {$number} вопросов", "Перейти к следующим {$num} вопросам", "{$this->ipsclass->form_code}&code=doimport&trv_root={$filename}&st={$howmany}&del={$del}" );		
		}

	}
	
	function import_shutdown($start,$offset)
	{
		set_time_limit(1200);

		$toprocess = array_slice($this->lineitems,$start,$offset);
		foreach($toprocess as $k => $v)
		{
			$answer = trim(addslashes(substr(strrchr($v,'*'),1)));
			$question = addslashes(substr(reverse_strrchr($v,"*"),0,-1));
			// Fix them dumb uword ones :P
			if(strtolower($question) == "uword")
			{
				$scramble = wordScramble($answer);
				$question = $question.": ".$scramble;
			}
			$this->ipsclass->DB->query("INSERT INTO ".SQL_PREFIX."trivia (`question`,`date`,`answer`,`open`) VALUES ('{$question}','".time()."','{$answer}',1)");
		}
		return true;
	}
	
	function do_export()
	{
		$path = ROOT_PATH."uploads/";
		if($this->ipsclass->input['filename'] == "")
		{
			$filename = "trivia_export_".time().".trv";
		}
		else
		{
			$filename = urldecode($this->ipsclass->input['filename']);
		}
		$file = $path.$filename;
		
		$fh = @fopen($file, "a");
		if(!$fh)
		{
			$this->ipsclass->admin->error("Ошибка при создании(открытии) файла. Проверьте права доступа к папке $path и файл $filename.");
		}
		
		$st = $this->ipsclass->input['st'] ? $this->ipsclass->input['st'] : 0;
		$q = $this->ipsclass->DB->query("SELECT COUNT(*) AS number FROM ".SQL_PREFIX."trivia;");
		$res = $this->ipsclass->DB->fetch_row($q);
		$number = $res['number'];
		$num = 1000;

		$end = 0;
		if(($st+$num)>=$number)
		{
			$num = $number-$st;
			$end = 1;
		}
		$howmany = $st+$num;
		$this->export_shutdown($fh, $st,$num);
		@fclose($fh);
		$filename = urlencode($filename);

		if($end == 1)
		{
			$this->ipsclass->admin->save_log("Экспортировано {$howmany} из {$number} вопросов");
			$this->ipsclass->admin->done_screen("Экспортировано {$howmany} из {$number} вопросов", "Викторина - Администрирование", "{$this->ipsclass->form_code}" , 'redirect' );		
		}
		else
		{
			$this->ipsclass->admin->save_log("Экспортировано {$howmany} из {$number} вопросов");
			$this->ipsclass->admin->done_screen("Экспортировано {$howmany} из {$number} вопросов", "Перейти к следующим {$num} вопросам", "{$this->ipsclass->form_code}&code=doexport&filename={$filename}&st={$howmany}" );		
		}

	}

	function export_shutdown($fh, $start,$offset)
	{
		set_time_limit(1200);
		
		$i = $start;

		$query = $this->ipsclass->DB->query("SELECT question, answer FROM ".SQL_PREFIX."trivia ORDER BY id ASC LIMIT $start,$offset;");
		while ($row = $this->ipsclass->DB->fetch_row($query))
		{
			$str = ($i==0?"":"\n").$row['question']."*".$row['answer'];
			if(!@fwrite($fh, $str))
			{
				$this->ipsclass->admin->error("Ошибка при записи в файл. Проверьте права доступа к файлу $filename.");
			}
			$i++;
		}
		
		return true;
	}

	function unhide()
	{
		if(!$this->ipsclass->input['id'] or $this->ipsclass->input['id'] == 0)
		{
			$this->ipsclass->admin->error("Неверный номер вопроса");
			exit();
		}
		$this->ipsclass->DB->query("UPDATE ".SQL_PREFIX."trivia SET open=1 WHERE id={$this->ipsclass->input['id']}");
		$this->ipsclass->admin->save_log("Вопрос №{$this->ipsclass->input['id']} Викторины опубликован");
		$this->ipsclass->admin->done_screen("Вопрос №{$this->ipsclass->input['id']} Викторины опубликован", "Викторина - Администрирование", "{$this->ipsclass->form_code}" , 'redirect' );		
	}

	function delete()
	{
		if(!$this->ipsclass->input['id'] or $this->ipsclass->input['id'] == 0)
		{
			$this->ipsclass->admin->error("Неверный номер вопроса");
			exit();
		}
		$this->ipsclass->DB->query("DELETE FROM ".SQL_PREFIX."trivia WHERE id={$this->ipsclass->input['id']}");
		$this->ipsclass->admin->save_log("Вопрос №{$this->ipsclass->input['id']} удалён");
		$this->ipsclass->admin->done_screen("Вопрос №{$this->ipsclass->input['id']} удалён", "Викторина - Администрирование", "{$this->ipsclass->form_code}" , 'redirect' );		
	}

	function delete_all_hidden()
	{
		$this->ipsclass->DB->query("DELETE FROM ".SQL_PREFIX."trivia WHERE open=0");
		$this->ipsclass->admin->save_log("Викторина: Удалены все скрытые вопросы");
		$this->ipsclass->admin->done_screen("Удалены все скрытые вопросы", "Викторина - Администрирование", "{$this->ipsclass->form_code}" , 'redirect' );		
	}

	function add_page()
	{
		$this->ipsclass->admin->page_title = "Добавление вопроса";
		$this->ipsclass->admin->page_detail = "На этой странице вы можете добавить вопрос в вашу Викторину.";

		$page_array = array( 1 => array( 'code'  , 'doadd'  ),
					 2 => array( 'act'   , 'trivia'       ),
					 3 => array( 'section', $this->ipsclass->section_code ),
				   );
		$this->ipsclass->html .= $this->ipsclass->adskin->start_form( $page_array );

		$this->ipsclass->adskin->td_header[] = array( "&nbsp;"   , "20%" );
		$this->ipsclass->adskin->td_header[] = array( "&nbsp;"   , "80%" );
		$this->ipsclass->html .= $this->ipsclass->adskin->start_table( "Добавить вопрос" );

		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Вопрос:</b>" ,
												  $this->ipsclass->adskin->form_input("question", "" )
									     )      );
		$this->ipsclass->html .= $this->ipsclass->adskin->add_td_row( array( "<b>Ответ:</b>" ,
												  $this->ipsclass->adskin->form_input("answer", "" )
									     )      );

		$this->ipsclass->html .= $this->ipsclass->adskin->end_form("Добавить");
										 
		$this->ipsclass->html .= $this->ipsclass->adskin->end_table();
		
		$this->ipsclass->admin->output();

	}

	function do_add()
	{
		if(!$this->ipsclass->input['question'] or $this->ipsclass->input['question'] == "")
		{
			$this->ipsclass->admin->error("А вопрос-то какой?");
			exit();
		}
		if(!$this->ipsclass->input['answer'] or $this->ipsclass->input['answer'] == "")
		{
			$this->ipsclass->admin->error("Хм... А ответ ввести?");
			exit();
		}

		$this->ipsclass->DB->query("INSERT INTO ".SQL_PREFIX."trivia (`open`,`question`,`answer`,`date`) VALUES (1,'".addslashes($this->ipsclass->input['question'])."','".addslashes($this->ipsclass->input['answer'])."','".time()."')");
		$id = $this->ipsclass->DB->get_insert_id();
		$this->ipsclass->admin->save_log("Вопрос №{$id} вручную добавлен в Викторину");
		$this->ipsclass->admin->done_screen("Вопрос №{$id} вручную добавлен в Викторину", "Викторина - Администрирование", "{$this->ipsclass->form_code}&code=addpage" , 'redirect' );		
	}
}

function reverse_strrchr($haystack, $needle)
{
   $pos = strrpos($haystack, $needle);
   if($pos === false)
   {
       return $haystack;
   }
   return substr($haystack, 0, $pos + 1);
}

function wordScramble($x)
{
        // split up the words and puctuation
        $ar=preg_split('/\b/',$x);
        
        // delete empty elements
        foreach($ar as $y=>$z)
            if(empty($z))
                unset($ar[$y]);
        
        $output=array();
        foreach($ar as $word)
		{
            $elem=count($output)-1;
            if(strlen($word)>3)
			{
                $word_start=substr($word,0,1);  // first letter
                $word_end=substr($word,-1);     // last letter
                $word_rest=substr($word,1,-1);  //rest of the word
                $word_rest_ar=array();
                for($i=0;$i<strlen($word_rest);$i++)
				{
                    // for each letter for the rest of the word
                    $word_rest_ar[]=substr($word_rest,$i,1);
                }
                shuffle($word_rest_ar);
                $word=$word_start.join('',$word_rest_ar).$word_end;
            }
            $output[]=$word;
        }
        $find=array(" ' ",'( ',' )','{ ',' }','[ ',' ]',' .',' ?',' !',' , ');
        $replace=array("'",'(',')','{','}','[',']','.','?','!',', ');
        return preg_replace('/(\r\n|\r|\n){2}/',"\n",str_replace($find,$replace,join(' ',$output)));
}

function make_percent($a, $b)
{
	return ($b?round(100*$a/$b, 1):'0').'%';
}
?>