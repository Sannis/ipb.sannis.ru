/*-------------------------------------------
+
+	Trivia System 2.1.1 JS file
+	sannis[at]mail.ru
+
+------------------------------------------*/

/*--------------------------------------------*/
// Delete question
/*--------------------------------------------*/

function delete_question(qid)
{
	if (confirm( "Вы действительно хотите удалить этот вопрос?" ))
	{
		window.location.href = ipb_var_base_url+'autocom=trivia&CODE=delete&id='+qid;
	}
	else
	{
		alert ( "Как хотите :)" );
	} 
}