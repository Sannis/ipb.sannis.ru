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

/**
* Script type
*/
define( 'IPB_THIS_SCRIPT', 'admin' );
define( 'IPB_LOAD_SQL'   , 'admin_queries' );

require_once( './init.php' );

define ( 'CACHE_PATH', ROOT_PATH );

//===========================================================================
// MAIN PROGRAM
//===========================================================================

$INFO = array();
require_once ROOT_PATH."conf_global.php";

require_once ROOT_PATH."sources/ipsclass.php";
# Initiate super-class
$ipsclass       = new ipsclass();
$ipsclass->vars = $INFO;
//--------------------------------
// Load the DB driver and such
//--------------------------------
$ipsclass->init_db_connection();

$template 	= new template;

//--------------------------------
//  Set up our vars
//--------------------------------
$ipsclass->parse_incoming();
//Main switch
switch($ipsclass->input['page'])
{
	case '1':
		do_install();
		break;
	case '2':
		do_skin_install();
		break;
	case '3':
		do_removal();
		break;
	case '4':
		do_skin_rebuild();
		break;
	case '5':
		do_finish();
		break;
	default:
		do_intro();
		break;
}

function do_intro()
{
	global $ipsclass, $template;
	
	$template->print_top('��������� ��������� ��� IPB 2.1 �� bfarber & Sannis');
	
	$template->contents .= "<table width='100%' border='0' cellpadding='0' cellspacing='0' align='center' class='mainborder'>
							<tr>
							 <td width='100%'><h3>��������� ��������� ��� IPB 2.1</h3>
							  <br />
							   ����� ������������ ���������, ��� �� ��������� � ����� ������ ��� ����������� �����.
							   <br /><br />
								<ul>
								<li>������� <a href='{$ipsclass->vars['board_url']}/trsinstall.php?page=1'>�����</a> ��� ������� ��������� � ������ ���.<br /><br /></li>
								<li>������� <a href='{$ipsclass->vars['board_url']}/trsinstall.php?page=2'>�����</a> ��� ������� ������ �������� ��� ���������.<br />
								&nbsp;&nbsp;*(<b>���������</b>: �� ������ ������������ ��� ����� � ����� ����� ��� ������� �������������� ����� ���������.)<br /><br /></li>
								<li>������� <a href='{$ipsclass->vars['board_url']}/trsinstall.php?page=3'>�����</a> ��� ������ ���� ��������� � ��, ������������ ��� ��������� (����������� ��� ��� �������� ���������).</li></ul>
							   <br>
							   ";
					
	$template->contents .= " </td>
							  </tr>
							 </table>";
	$template->output();
}

function do_install()
{
	global $ipsclass, $template, $INFO;

	$template->print_top('��������� � �� �����������!');

	$query1 = "CREATE TABLE IF NOT EXISTS `".$INFO['sql_tbl_prefix']."trivia` (
  `id` mediumint(8) NOT NULL auto_increment,
  `question` mediumtext,
  `date` varchar(13) NOT NULL default '0',
  `answer` mediumtext,
  `open` tinyint(1) NOT NULL default '0',
  `hidden_by` mediumint(8) NOT NULL default '0',
  `hidden_on` varchar(13) NOT NULL default '0',
  `served` mediumint(8) NOT NULL default '0',
  `correct` mediumint(8) NOT NULL default '0',
  PRIMARY KEY  (`id`)
);";

	$query2 = "CREATE TABLE IF NOT EXISTS `".$INFO['sql_tbl_prefix']."trivia_sessions` (
  `tsid` varchar(32) NOT NULL default '0',
  `mid` mediumint(8) NOT NULL default '0',
  `mname` varchar(75) NOT NULL default '�����',
  `trivia_served` mediumint(8) NOT NULL default '0',
  `trivia_correct` mediumint(8) NOT NULL default '0',
  `trivia_incorrect` mediumint(8) NOT NULL default '0',
  `session_start` varchar(13) NOT NULL default '0',
  `session_activity` varchar(13) NOT NULL default '0',
  `session_end` varchar(13) NOT NULL default '0',
  `current` tinyint(1) NOT NULL default '0',
  `mostcorrect` mediumint(8) NOT NULL default '0',
  `currentcorrect` mediumint(8) NOT NULL default '0'
);";

	$query3 = "CREATE TABLE `".$INFO['sql_tbl_prefix']."trivia_answers` (
  `aid` int(10) NOT NULL auto_increment,
  `ip_address` varchar(16) NOT NULL default '0',
  `mid` mediumint(8) NOT NULL default '0',
  `mname` varchar(255) NOT NULL default '0',
  `date` int(10) NOT NULL default '0',
  `qid` mediumint(8) NOT NULL default '0',
  `answer` varchar(50) NOT NULL default '0',
  `correct` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`aid`)
);";

	$ipsclass->DB->query($query1);
	$ipsclass->DB->query($query2);	
	$ipsclass->DB->query($query3);

	$template->contents .= "
		<div class='centerbox'>
		<div class='tableborder'>
		<div class='maintitle'>��������� � �� ���������</div>
		<div class='tablepad'>
		<b>���������� ������� ������� ��������� ��������� � �� ������, ����������� ��� ������ ���������!</b>
		<br><br>
		������� <a href='{$ipsclass->vars['board_url']}/trsinstall.php?page=2'>�����</a> ��� ����������� (�������� �������� ���������).
		</div>
		</div>
		</div>";

	$template->output();
		
}

function do_skin_install()
{
	global $ipsclass, $template;

	manage_skin_trivia();
	$template->print_top('������� �����������!');
	$template->contents .= "
	<div class='centerbox'>
	<div class='tableborder'>
	<div class='maintitle'>���� ����������� ������� ���������</div>
	<div class='tablepad'>
	<b>������� �����������!</b>
	<br><br>
	������� <a href='{$ipsclass->vars['board_url']}/trsinstall.php?page=4'>�����</a> ��� ����������� ���� ��������.
	</div>
	</div>
	</div>";
						 
	$template->output();
		
}

function do_skin_rebuild()
{
	global $ipsclass, $template;

	$template->print_top('����������� ���� ��������!');
	
	require_once(ROOT_PATH.'sources/lib/admin_cache_functions.php');
	$acp = new admin_cache_functions();
	$acp->ipsclass =& $ipsclass;
	
	$iscompleted = ($ipsclass->input['completed']) ? $ipsclass->input['completed'] : 1;
	$ipsclass->DB->simple_construct(array('select' => '*', 'from' => 'skin_sets', 'where' => 'set_skin_set_id>'.$iscompleted, 'order' => 'set_skin_set_id', 'limit' => array(0, 1)));
	$ipsclass->DB->simple_exec();
	$myrow = $ipsclass->DB->fetch_row();
	$msg = "";
	if ($myrow['set_skin_set_id'] != "")
	{
		$acp->_recache_templates($myrow['set_skin_set_id'], $myrow['set_skin_set_parent'], "skin_trivia");
		$msg = implode("<br />", $acp->messages);
		$msg .= "<br /><br />����������� ����� {$myrow['set_name']} ���������.<br /><br /><center><a href='{$ipsclass->vars['board_url']}/trsinstall.php?page=4&completed={$myrow['set_skin_set_id']}'>������� ����� ��� �������� � ���������� �����.</a></center>";
	}
	else
	{
		$msg = "��� ����� ���� �����������.<br /><br /><center><a href='{$ipsclass->vars['board_url']}/trsinstall.php?page=5'>������� ����� ��� ���������� ���������.</a></center>";
	}
	$template->contents .= "
	<div class='centerbox'>
	<div class='tableborder'>
	<div class='maintitle'>����������� ������</div>
	<div class='tablepad'>
	$msg
	</div>
	</div>
	</div>";

	$template->output();
}

	
function do_removal()
{
	global $ipsclass, $template, $INFO;
	
	# drop trivia data tables
	$ipsclass->DB->query("DROP TABLE IF EXISTS `".$INFO['sql_tbl_prefix']."trivia`;");
	$ipsclass->DB->query("DROP TABLE IF EXISTS `".$INFO['sql_tbl_prefix']."trivia_sessions`;");
	$ipsclass->DB->query("DROP TABLE IF EXISTS `".$INFO['sql_tbl_prefix']."trivia_answers`;");
	# delet trivia ibf_skin_templates & ibf_skin_templates_cache
	$ipsclass->DB->query("DELETE FROM `".$INFO['sql_tbl_prefix']."skin_templates` WHERE group_name='skin_trivia';");
	$ipsclass->DB->query("DELETE FROM `".$INFO['sql_tbl_prefix']."skin_templates_cache` WHERE template_group_name='skin_trivia';");
	# delete trivia ibf_conf_settings & ibf_conf_settings_titles
	$conf = $ipsclass->DB->simple_exec_query( array ( 'select' => 'conf_title_id', 'from' => 'conf_settings_titles', 'where' => "conf_title_keyword='triviasettings'" ) );
	if( $conf['conf_title_id'] )
	{
		$confgroup = $conf['conf_title_id'];

		$ipsclass->DB->query( "DELETE FROM `".SQL_PREFIX."conf_settings_titles` WHERE conf_title_id='".$confgroup."';" );
		$ipsclass->DB->query( "DELETE FROM `".SQL_PREFIX."conf_settings` WHERE conf_group='".$confgroup."';" );
	}
	# delete trivia component
	$ipsclass->DB->query( "DELETE FROM `".SQL_PREFIX."components` WHERE com_filename='trivia';" );
	
	$template->print_top('��������� �����!');
	$template->contents .= "
	<div class='centerbox'>
	<div class='tableborder'>
	<div class='maintitle'>������� ��������� ���� ������� �� �� ������, � ��������� ��������� � ����, ������������ ��� ���������, ����.</div>
	<div class='tablepad'>
	<b>��������� ����� �� ���������! ����������, ������� �� ����� ������ �����, ����������� ��� ��������� �����������!</b>
	<br><br>
	������� <a href='{$ipsclass->vars['board_url']}/trsinstall.php?page=4'>�����</a> ��� ����������� ���� ������ � ���������� �������� ���������.
	</div>
	</div>
	</div>";
						 
	$template->output();
		
}


function do_finish()
{
	global $ipsclass, $template;
	
	
		$template->print_top('Finished!');
		
		$msg = "��������!!! ������� ���������� ('trsinstall.php') ����� ������������!<br />������������� ����� ��� ������ ����������� ������� ��������� ��� ������ �������!
				<br><br>
				<center><b><a href='{$ipsclass->vars['board_url']}/index.php'>������� ����� ��� �������� �� �����!</a></center>";
	
	$template->contents .= "
	<div class='centerbox'>
	<div class='tableborder'>
	<div class='maintitle'>���������</div>
	<div class='tablepad'>
	<b>������� ��������� ��� �������� ��������� ��������!</b>
	<br><br>
	$msg
	</div>
	</div>
	</div>";
						 
	$template->output();
		
}


//+---------------------------------------



function install_error($msg="")
{
	global $ipsclass, $template;
	
	$template->print_top('������!');
	

	
	$template->contents .= "<div class='warnbox'>
						     <strong style='font-size:16px;color:#F00'>������!</strong>
						     <br /><br />
						     <b>����������, �������� ���������� ������ ����� ������������!!</b><br>����� ����� ��������� ����� � ���������� �����.
						     <br><br>
						     $msg
						    </div>";
	
	
	
	$template->output();
}

//+--------------------------------------------------------------------------
// CLASS template
//+--------------------------------------------------------------------------


class template {
	var $contents = "";
	
	function output()
	{
		echo $this->contents;
		echo "   
				 
				 <br><br><br><br><center><span id='copy'>���������� ��������� �� <a href='http://ipbmods.net'>bfarber</a>, ������ ��� IP.Board 2.1.x �� <a href='http://ibpower.ru/index.php?showuser=33'>Sannis</a></span></center>
				 </td></tr></table>
				 </body>
				 </html>";
		exit();
	}
	
	//--------------------------------------

	function print_top($title="", $meta="")
	{
		global $ipsclass;
	
		$this->contents = "<html>
				{$meta}
		          <head><title>��������� 2.1 :: $title </title>
		          <style type='text/css'>
		          	@import url({$ipsclass->vars['board_url']}/trsinstall/bf_css.css);
			  </style>
			  <link rel='{$ipsclass->vars['board_url']}/trsinstall/bf_css.css' type='text/css' />
				  </head>
				 <body marginheight='0' marginwidth='0' leftmargin='0' topmargin='0' bgcolor='#000'>
				 <table cellpadding='1' cellspacing='1' border='0' width='80%' align='center'>
				 <tr><td width='100%' align='left'>
				 <div id='logostrip'><img src='{$ipsclass->vars['board_url']}/trsinstall/img/TriviaHeader.gif' border='0' alt='��������� ���������' /></div>
				 <br />
				 ";
				  	   
	}


}



function manage_skin_trivia()
{
	global $ipsclass, $INFO;


	$q = $ipsclass->DB->query("DELETE FROM ".$INFO['sql_tbl_prefix']."skin_templates WHERE group_name='skin_trivia'");


	$ipsclass->DB->query('REPLACE INTO '.$INFO['sql_tbl_prefix'].'skin_templates SET set_id=1, group_name=\'skin_trivia\', func_name=\'button_new\', section_content=\'<span class=\\\'fauxbutton\\\'><a href=\\\'{ipb.script_url}autocom=trivia&amp;CODE=02\\\'>{ipb.lang[\\\'button_new\\\']}</a></span>\', func_data=\'\'');
	$ipsclass->DB->query('REPLACE INTO '.$INFO['sql_tbl_prefix'].'skin_templates SET set_id=1, group_name=\'skin_trivia\', func_name=\'button_add_question\', section_content=\'<span class=\\\'fauxbutton\\\'><a href=\\\'{ipb.script_url}autocom=trivia&amp;CODE=addquestion\\\'>{ipb.lang[\\\'button_add_question\\\']}</a></span>\', func_data=\'\'');
	$ipsclass->DB->query('REPLACE INTO '.$INFO['sql_tbl_prefix'].'skin_templates SET set_id=1, group_name=\'skin_trivia\', func_name=\'button_continue\', section_content=\'<span class=\\\'fauxbutton\\\'><a href=\\\'{ipb.script_url}autocom=trivia&CODE=03\\\'>{ipb.lang[\\\'button_cont\\\']}</a></span>\', func_data=\'\'');
	$ipsclass->DB->query('REPLACE INTO '.$INFO['sql_tbl_prefix'].'skin_templates SET set_id=1, group_name=\'skin_trivia\', func_name=\'button_finalize\', section_content=\'<if="type==0">\n<span class=\\\'fauxbutton\\\'><a href=\\\'{ipb.script_url}autocom=trivia&amp;CODE=04\\\'>{ipb.lang[\\\'button_end\\\']}</a></span>\n</if>\n<if="type==1">\n<input type=\\\'submit\\\' onclick="document.forms[\\\'question\\\'].andend.value=\\\'1\\\';" value=\\\'{ipb.lang[\\\'button_end\\\']}\\\' class=\\\'fauxbutton\\\' />\n</if>\', func_data=\'$type=0\'');
	$ipsclass->DB->query('REPLACE INTO '.$INFO['sql_tbl_prefix'].'skin_templates SET set_id=1, group_name=\'skin_trivia\', func_name=\'main_page_block\', section_content=\'<div class=\\\'borderwrap\\\'>
 <div class=\\\'maintitle\\\'>
   <p><{CAT_IMG}>&nbsp;{ipb.lang[\\\'maintitle_bar\\\']}</p>
 </div>
 <table width=\\\'100%\\\' cellpadding=\\\'4\\\' cellspacing=\\\'1\\\' border=\\\'0\\\'>
  <tr>
   <td class=\\\'row2\\\' width=\\\'100%\\\'>{$msg}</td>
  </tr>
 </table>
</div>
<br />\', func_data=\'$msg=""\'');


	$ipsclass->DB->query('REPLACE INTO '.$INFO['sql_tbl_prefix'].'skin_templates SET set_id=1, group_name=\'skin_trivia\', func_name=\'mod_block_layout \', section_content=\'<table width=\\\'100%\\\' border=\\\'0\\\' cellspacing=\\\'3\\\' cellpadding=\\\'3\\\'>
<script type="text/javascript" src=\\\'jscripts/trivia.js\\\'></script>
<tr>
 <td width=\\\'100%\\\'><center><b><span style=\\\'font-size:12pt;\\\'>{ipb.lang[\\\'mod_title\\\']}</span></b></center></td>
</tr>
<tr>
 <td width=\\\'100%\\\'>{$block_new}</td>
</tr>
<tr>
 <td width=\\\'100%\\\'>{$block_hidden}</td>
</tr>
</table>
<br /><br />\', func_data=\'$block_new, $block_hidden\'');

	$ipsclass->DB->query('REPLACE INTO '.$INFO['sql_tbl_prefix'].'skin_templates SET set_id=1, group_name=\'skin_trivia\', func_name=\'add_question_form\', section_content=\'<div class=\\\'borderwrap\\\'>
 <div class=\\\'maintitle\\\'>
  <p><{CAT_IMG}>&nbsp;{ipb.lang[\\\'add_question_bar\\\']}</p>
 </div>
 <form action="{ipb.script_url}autocom=trivia&amp;CODE=doaddquestion" method=\\\'post\\\' name=\\\'question\\\'>
 <table cellpadding=\\\'4\\\' cellspacing=\\\'1\\\' border=\\\'0\\\' width=\\\'100%\\\'>
  <tr class=\\\'row1\\\'>
   <td width=\\\'20%\\\'><b>{ipb.lang[\\\'add_question\\\']}:</b></td>
   <td width=\\\'80%\\\'><input type=\\\'text\\\' class=\\\'forminput\\\' size=\\\'50\\\' name=\\\'the_question\\\' /></td>
  </tr>
  <tr class=\\\'row2\\\'>
   <td width=\\\'20%\\\'><b>{ipb.lang[\\\'add_answer\\\']}:</b></td>
   <td width=\\\'80%\\\'><input type=\\\'text\\\' class=\\\'forminput\\\' size=\\\'50\\\' name=\\\'the_answer\\\' /></td>
  </tr>
  <tr class=\\\'row1\\\'>
   <td width=\\\'20%\\\'>&nbsp;</td>
   <td width=\\\'80%\\\'><input type="submit" name="submit" value="{ipb.lang[\\\'add_question_submit\\\']}" /></td>
  </tr>
 </table>
 </form>
</div>
<br />\', func_data=\'\'');


	$ipsclass->DB->query('REPLACE INTO '.$INFO['sql_tbl_prefix'].'skin_templates SET set_id=1, group_name=\'skin_trivia\', func_name=\'main_page_end\', section_content=\'<br />\n<center>{$buttons[0]}\n<if="buttons[1]">\n &nbsp;&nbsp;{$buttons[1]}\n</if>\n<if="buttons[2]">\n &nbsp;&nbsp;{$buttons[2]}\n</if>\n<if="buttons[3]">\n &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$buttons[3]}\n</if>\n</center>\n<br />\', func_data=\'$buttons=array()\'');
	$ipsclass->DB->query('REPLACE INTO '.$INFO['sql_tbl_prefix'].'skin_templates SET set_id=1, group_name=\'skin_trivia\', func_name=\'button_cont_c\', section_content=\'<span class=\\\'fauxbutton\\\'><a href="#" onclick="this.onclick=\\\'\\\';document.forms[\\\'question\\\'].submit();">{ipb.lang[\\\'button_next\\\']}</a></span>\', func_data=\'\'');
	$ipsclass->DB->query('REPLACE INTO '.$INFO['sql_tbl_prefix'].'skin_templates SET set_id=1, group_name=\'skin_trivia\', func_name=\'question_block\', section_content=\'<div class=\\\'borderwrap\\\'>\n <div class=\\\'maintitle\\\'>\n  <p><{CAT_IMG}>&nbsp;{ipb.lang[\\\'maintitle_bar\\\']}</p>\n </div>\n <table cellpadding=\\\'4\\\' cellspacing=\\\'1\\\' border=\\\'0\\\' width=\\\'100%\\\'>\n  <tr>\n   <td class=\\\'row2\\\' width=\\\'100%\\\'>\n    <b>{$ques[\\\'question\\\']}</b><br /><br />\n    <form action="{ipb.script_url}autocom=trivia&amp;CODE=03" method=\\\'post\\\' name=\\\'question\\\'>\n    <input type="hidden" name="id" value="{$ques[\\\'id\\\']}" />\n    <input type=\\\'hidden\\\' name=\\\'andend\\\' value=\\\'0\\\' />\n    {ipb.lang[\\\'enter_answer\\\']}:&nbsp;<input type=\\\'text\\\' class=\\\'forminput\\\' size=\\\'50\\\' name=\\\'the_answer\\\' /><br />\n     </form>\n    </td>\n   </tr>\n </table>\n</div>\n<br />\', func_data=\'$ques=array()\'');
	$ipsclass->DB->query('REPLACE INTO '.$INFO['sql_tbl_prefix'].'skin_templates SET set_id=1, group_name=\'skin_trivia\', func_name=\'prev_question_block\', section_content=\'<div class=\\\'borderwrap\\\'>\n <div class=\\\'maintitle\\\'>\n  <p><{CAT_IMG}>&nbsp;{ipb.lang[\\\'maintitle_bar\\\']}</p>\n </div>\n <table cellpadding=\\\'4\\\' cellspacing=\\\'1\\\' border=\\\'0\\\' width=\\\'100%\\\'>\n  <tr>\n   <td class=\\\'row2\\\' width=\\\'100%\\\'>\n    <span style=\\\'font-size: 18px;\\\'>{ipb.lang[\\\'last_question\\\']}:"{$ques[\\\'question\\\']}"</span><br /><br />\n     <if="ipb.member[\\\'g_access_cp\\\']">{ipb.lang[\\\'hereis_answer\\\']}:&nbsp;<b>{$ques[\\\'answer\\\']}</b><br /></if>\n    {ipb.lang[\\\'your_answer\\\']}:&nbsp;{$ques[\\\'prev_ans\\\']}<br />\n    {ipb.lang[\\\'answer_was\\\']}&nbsp;{$ques[\\\'flag\\\']}<br clear=\\\'all\\\' /><br />\n{$mod}\n     </form>\n    </td>\n   </tr>\n </table>\n</div>\n<br />\', func_data=\'$ques=array(), $mod\'');
	$ipsclass->DB->query('REPLACE INTO '.$INFO['sql_tbl_prefix'].'skin_templates SET set_id=1, group_name=\'skin_trivia\', func_name=\'no_question_block\', section_content=\'<div class=\\\'borderwrap\\\'>
\n <div class=\\\'maintitle\\\'>
\n   <p><{CAT_IMG}>&nbsp;{ipb.lang[\\\'maintitle_bar\\\']}</p>
\n </div>
\n <table width=\\\'100%\\\' cellpadding=\\\'4\\\' cellspacing=\\\'1\\\' border=\\\'0\\\'>
\n  <tr>
\n   <td class=\\\'row2\\\' width=\\\'100%\\\'>{ipb.lang[\\\'no_questions\\\']}</td>
\n  </tr>
\n </table>
\n</div>
\n<br />\', func_data=\'\'');
	$ipsclass->DB->query('REPLACE INTO '.$INFO['sql_tbl_prefix'].'skin_templates SET set_id=1, group_name=\'skin_trivia\', func_name=\'for_popup\', section_content=\'<div class=\\\'borderwrap\\\'>
\n <div class=\\\'maintitle\\\'>
\n  <p><{CAT_IMG}>&nbsp;{ipb.lang[\\\'maintitle_bar\\\']}</p>
\n </div>
\n <table cellpadding=\\\'4\\\' cellspacing=\\\'1\\\' border=\\\'0\\\' width=\\\'100%\\\'>
\n  <tr>
\n   <td class=\\\'row2\\\' width=\\\'100%\\\'>
\n    {$msg}
\n    </td>
\n   </tr>
\n </table>
\n</div>
\n<br />\', func_data=\'$msg=""\'');

	$ipsclass->DB->query('REPLACE INTO '.$INFO['sql_tbl_prefix'].'skin_templates SET set_id=1, group_name=\'skin_trivia\', func_name=\'stat_block_start\', section_content=\'<div class=\\\'borderwrap\\\'>\n <div class=\\\'maintitle\\\'>{$lang}</div>\n <table cellpadding=\\\'4\\\' cellspacing=\\\'1\\\' border=\\\'0\\\' width=\\\'100%\\\'>\n  <tr>\n   <th width=\\\'80%\\\'>{$th1}</th>\n   <th width=\\\'20%\\\'>{$th2}</th>\n  </tr>\', func_data=\'$lang="",$th1="",$th2=""\'');
	$ipsclass->DB->query('REPLACE INTO '.$INFO['sql_tbl_prefix'].'skin_templates SET set_id=1, group_name=\'skin_trivia\', func_name=\'stat_block_end\', section_content=\'</table></div>\', func_data=\'\'');
	$ipsclass->DB->query('REPLACE INTO '.$INFO['sql_tbl_prefix'].'skin_templates SET set_id=1, group_name=\'skin_trivia\', func_name=\'stat_block_row\', section_content=\'<tr>\n <td class=\\\'row2\\\'>{$r[\\\'mname\\\']}</td><td class=\\\'row2\\\'>{$r[\\\'data\\\']}</td>\n</tr>\', func_data=\'$r=array()\'');
	$ipsclass->DB->query('REPLACE INTO '.$INFO['sql_tbl_prefix'].'skin_templates SET set_id=1, group_name=\'skin_trivia\', func_name=\'stat_block_layout\', section_content=\'<table width=\\\'100%\\\' border=\\\'0\\\' cellspacing=\\\'3\\\' cellpadding=\\\'3\\\'>\n<tr>\n <td width=\\\'50%\\\'>{$b1}</td>\n <td width=\\\'50%\\\'>{$b2}</td>\n</tr>\n<tr>\n <td width=\\\'50%\\\'>{$b3}</td>\n <td width=\\\'50%\\\'>{$b4}</td>\n</tr>\n</table>\n<br /><br />\', func_data=\'$b1,$b2,$b3,$b4\'');



	$q2 = $ipsclass->DB->query("OPTIMIZE TABLE ".$INFO['sql_tbl_prefix']."skin_templates");

	return true;
}

?>