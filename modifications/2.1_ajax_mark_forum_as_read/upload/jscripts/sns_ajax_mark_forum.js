/*
+--------------------------------------------------------------------------
|   (SnS) AJAX Mark forum as read
|   for IP.Board 2.1.x
|   ========================================
|   (c) 2007 Oleg "Sannis" Efimov <efimovov@yandex.ru>
+---------------------------------------------------------------------------
*/

/*jslint browser: true, onevar: false, newcap: false */
/*global escape, use_enhanced_js, ajax_request, ipb_var_base_url, ipb_md5_check */

function mark_forum_read(fid) {
  var xmlobj;
  
  //----------------------------------
  // Using fancy js?
  //----------------------------------
  
  if (!use_enhanced_js) {
    return false;
  }
  
  //----------------------------------
  // Prevent double-click
  //----------------------------------
  
  var div_content = document.getElementById('f-' + fid).innerHTML;
  
  /*--------------------------------------------*/
  // Main function to do on request
  // Must be defined first!!
  /*--------------------------------------------*/
  
  var do_request_function = function () {
    //----------------------------------
    // Ignore unless we're ready to go
    //----------------------------------
    
    if (!xmlobj.readystate_ready_and_ok()) {
      // Could do a little loading graphic here?
      return;
    }
    
    //----------------------------------
    // INIT
    //----------------------------------
    
    var returned = xmlobj.xmlhandler.responseText;
    
    if (returned !== 'fail') {
      //----------------------------------
      // Explode returned string
      //----------------------------------
      var re = new RegExp('<([^<>]*)><([^<>]*)><([^<>]*)><([^<>]*)>(.*)');
      
      var cookie_name = returned.replace(re, '$1');
      var cookie_value = returned.replace(re, '$2');
      var cookie_domain = returned.replace(re, '$3');
      var cookie_path = returned.replace(re, '$4');
      var img = returned.replace(re, '$5');
      
      //----------------------------------
      // Set cookie
      //----------------------------------
      
      var cookie_expires = new Date();
      cookie_expires.setTime(cookie_expires.getTime() + 365 * 24 * 60 * 60 * 1000);
      var cookie_text = cookie_name + '=' + escape(cookie_value) +
                ((cookie_expires) ? '; expires=' + cookie_expires.toGMTString() : '') +
                ((cookie_path) ? '; path=' + cookie_path : '') +
                ((cookie_domain) ? '; domain=' + cookie_domain : '');
      document.cookie = cookie_text;
      
      //----------------------------------
      // Update forum img
      //----------------------------------
      
      document.getElementById('f-' + fid).innerHTML = img;
      document.getElementById('f-' + fid).onclick = '';
    }
  };
  
  //----------------------------------
  // LOAD XML
  //----------------------------------
  
  xmlobj = new ajax_request();
  xmlobj.onreadystatechange(do_request_function);
  
  xmlobj.process(ipb_var_base_url + 'act=xmlout&do=mark-forum' +
                 '&md5check=' + ipb_md5_check + '&fid=' + fid);
  
  return false;
}
