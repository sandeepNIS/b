var wpajaxeditcommentedit = {AEC_BlogUrl : 'http://www.jccib.com/wp-admin/admin-ajax.php',AEC_CanScroll : 'true',AEC_Minutes : 'minutes',AEC_Minute : 'minute',AEC_And : 'and',AEC_Seconds : 'seconds',AEC_Second : 'second',AEC_Moderation : 'Mark for Moderation?',AEC_Approve : 'Approve Comment?',AEC_Spam : 'Mark as Spam?',AEC_Delete : 'Delete this comment?',AEC_Anon : 'Anonymous',AEC_Loading : 'Loading...',AEC_Ready : 'Ready',AEC_Sending : 'Sending...',AEC_Sent : 'Message Sent',AEC_LoadSuccessful : 'Comment Loaded Successfully',AEC_Saving : 'Saving...',AEC_Blacklisting : 'Blacklisting...',AEC_Saved : 'Comment Successfully Saved',AEC_Delink : 'De-link Successful',AEC_MoreOptions : 'More Options',AEC_LessOptions : 'Less Options',AEC_UseRTL : 'false',AEC_RequestDeletionSuccess : 'Request has been sent successfully',AEC_RequestError : 'Error sending request',AEC_approving : 'Approving...',AEC_delinking : 'De-linking...',AEC_moderating : 'Moderating...',AEC_spamming : 'Spamming...',AEC_deleting : 'Deleting...',AEC_restoring : 'Restoring...',AEC_restored : 'Comment Restored.',AEC_undoing : 'Undoing...',AEC_undosuccess : 'Undo Successful',AEC_permdelete : 'Comment Deleted Permanently',AEC_fieldsrequired : 'Input Fields are Required',AEC_emailaddresserror : 'E-mail Address is Invalid',AEC_AftertheDeadline : 'false',AEC_AftertheDeadline_lang : 'en',AEC_Expand : 'false',AEC_Yes : 'Yes',AEC_No : 'No',AEC_Sure : 'Are you sure?',AEC_colorbox_width : '580',AEC_colorbox_height : '560'};var aec_popup = {atdlang : 'false',atd : 'false',expand : 'false',title : 'Comment Box'};/*WP Ajax Edit Comments Editor Interface Script
--Created by Ronald Huereca
--Created on: 05/04/2008
--Last modified on: 10/25/2008
--Relies on jQuery, wp-ajax-edit-comments, wp-ajax-response, thickbox
	
	Copyright 2007,2008  Ronald Huereca  (email : ron alfy [a t ] g m ail DOT com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/
jQuery(document).ready(function() {
var $j = jQuery;
$j.ajaxcommenteditor = {
	init: function() { if ( jQuery( 'body.editor' ).length <= 0 ) { return; } initialize_events(); load_comment(); after_the_deadline(); expand_option();},
	width: 0,
	height:0,
	commentBoxHeight: 0
};
	function after_the_deadline() {
		if (wpajaxeditcommentedit.AEC_AftertheDeadline == 'false') { return; }
		AtD.rpc_css_lang = wpajaxeditcommentedit.AEC_AftertheDeadline_lang;
		$j('#comment').addProofreader();
		$j("#submit").click(function() {  
		 	$j(".AtD_edit_button").trigger("click");
		 });
		$j("#AtD_0").click(function() { 
			$j("div#comment").css("width", "98%");
			//Correct an annoying bug when someone clicks on ATD in the expanded comment box, unexpands, and unclicks ATD
			var buttonpos = $j("#message").position().top + $j("#message").height();
			if (parent.jQuery("#cboxWrapper").height() < buttonpos) {
				$j("#comment").css('height', $j.ajaxcommenteditor.commentBoxHeight);	
			}
		});
		var spellcheck = $j("#AtD_0").clone(true);
		$j("#AtD_0").remove();
		$j("#edit_options").append(spellcheck);
	}
	function expand_option(obj) {
		if (wpajaxeditcommentedit.AEC_Expand == 'false') { return; }
		$window = $j(window);
		$j.ajaxcommenteditor.width = parseInt($window.width());
		$j.ajaxcommenteditor.height = parseInt($window.height());
		
		$j("#edit_options").append("<span class='expand'></span>");
		
		$j(".expand").bind("click", function() {
			expand();
		});
		$j(".retract").bind("click", function() {
			retract();
		});
	}
	function expand() {
		$j(".expand").unbind("click");
		var winHeight = parent.jQuery.fn.colorbox.myResize('100%', '100%',true);
		var timer =  setInterval(function() {
			clearTimeout(timer); 
			$j(".expand").removeClass().addClass("retract");
			$j(".retract").bind("click", function() {retract()});
			$j.ajaxcommenteditor.commentBoxHeight = $j("#comment").height();
			//for ATD
			$j("#comment").css("width", "98%");
			$j("#comment").css('height', parent.jQuery("#cboxContent").height() - $j("#container").height() + $j.ajaxcommenteditor.commentBoxHeight-35);
		}, 1000);
	}
	function retract() {
		$j(".retract").unbind("click");
		parent.jQuery.fn.colorbox.myResize($j.ajaxcommenteditor.width, $j.ajaxcommenteditor.height, false);
		$j("#comment").css('height', $j.ajaxcommenteditor.commentBoxHeight);
		var timer2 =  setInterval(function() {
			clearTimeout(timer2); 
			$j(".retract").removeClass().addClass("expand");
			$j(".expand").bind("click", function() {expand()});
		}, 500);
	}
	function more_less_options() {
		if ($j("#comment-options").hasClass("closed")) {
			$j("#comment-options h3 span").text(wpajaxeditcommentedit.AEC_MoreOptions);						
		} else {
			$j("#comment-options h3 span").text(wpajaxeditcommentedit.AEC_LessOptions);	
		}	
	}
	//Initializes the edit links
	function initialize_events() {
  	//Read in cookie values and adjust the toggle box
  	var cookieValue = readCookie('ajax-edit-comments-options');
    if (cookieValue) {
    	$j("#comment-options").attr("class", cookieValue);
		more_less_options();
    }
    
    //The "more options" button
  	$j("#comment-options h3").bind("click", function() { 
    	$j("#comment-options").toggleClass("closed"); 
		more_less_options();
      	createCookie('ajax-edit-comments-options', $j("#comment-options").attr("class"), 365);
      return false; 
    });
    //Cancel button
    $j("#cancel,#status a, #close a").bind("click", function() {  parent.jQuery.fn.colorbox.close();
    return false; });
    //Title for new window
    $j("#title a").bind("click", function() { window.open(this.href); return false; } );
    //Save button event
  }
	function load_comment() {

  	//Change the edit text and events
    $j("#status").show();
    $j("#status").attr("class", "success");
  	$j("#message").html(wpajaxeditcommentedit.AEC_Loading);
  	
	jQuery.post( ajaxurl, { action: $j("#action").attr("value"), cid: parseInt($j("#commentID").attr("value")) ,pid: parseInt($j("#postID").attr("value")), _ajax_nonce: $j('#_wpnonce').val() },
function(data){
		//Add event for save button
		var error = false;
		$j("#save").bind("click", function() { save_comment(); return false; });
		if ( typeof data.error != "undefined" ) { //error
			error = true;
			$j("#status").attr("class", "error");
			$j("#message").html( data.error );
			$j("#close-option").show();
			//remove event for save button
			$j("#save").unbind("click");
		} else { //success
			//Load content
			$j("#comment").html( data.comment_content ); //For everyone else
			$j("#comment").attr("value", data.comment_content  ); //For Opera
			$j("#name").attr("value", data.comment_author);
			$j("#e-mail").attr("value", data.comment_author_email);
			$j("#URL").attr("value", data.comment_author_url);
		}
		if (!error) {
			//Enable the buttons
			$j("#save, #cancel, #check_spelling").removeAttr("disabled");
			//Update status message
			$j("#status").attr("class", "success");
			$j("#message").html(wpajaxeditcommentedit.AEC_LoadSuccessful);
		}
}, "json" );
  } //end load_comment
  function save_comment() {
	//After the deadline
	 if (wpajaxeditcommentedit.AEC_AftertheDeadline == 'true') {
		 $j(".AtD_edit_button").trigger("click");
	 }
  	//Update status message
    $j("#status").attr("class", "success");
    $j("#message").html(wpajaxeditcommentedit.AEC_Saving);
    $j("#save").attr("disabled", "disabled");
    var error = false;
    //Read in dom values
    var name = encodeURIComponent($j("#name").attr("value"));
    var email = encodeURIComponent($j("#e-mail").attr("value"));
    var url = encodeURIComponent($j("#URL").attr("value"));
    var comment = encodeURIComponent($j("#comment").attr("value")); 
    var nonce = $j("#_wpnonce").attr("value");
	var data = {	action: "savecomment", cid: parseInt($j("#commentID").attr("value")) ,pid: parseInt($j("#postID").attr("value")), _ajax_nonce: $j('#_wpnonce').val(), comment_content: comment, comment_author: name, comment_author_email: email, comment_author_url: url };
	//Comment Status
	if ($j("#comment-status-radio").length > 0) {
		var comment_status = $j("#comment-status-radio :checked").attr("value");
		data = $j.extend(data, { comment_status: comment_status});
	}
	//Timestamp
	if ($j("#timestamp").length > 0) {
		var month = encodeURIComponent($j("#mm").attr("value")); 
		var day = encodeURIComponent($j("#jj").attr("value"));
		var year = encodeURIComponent($j("#aa").attr("value"));
		var hour = encodeURIComponent($j("#hh").attr("value"));
		var minute = encodeURIComponent($j("#mn").attr("value"));
		var ss = encodeURIComponent($j("#ss").attr("value"));
		data = $j.extend(data, { mm: month, jj: day, aa: year, hh: hour,mn: minute,ss:ss});
	}
	jQuery.post( ajaxurl, data, 
function ( response ) { 
	if ( typeof response.error != "undefined" ) { //error
		error = true;
		$j("#save").removeAttr("disabled");
		$j("#status").attr("class", "error");
		$j("#message").html( response.error );
		$j("#close-option").show();
	} else { //success 
		var comment = response.content;
		var name = response.comment_author;
		var url = response.comment_author_url;
		var date = response.comment_date;
		var time = response.comment_time;
		var undo = response.undo;
		var comment_approved = response.comment_approved;
		var old_comment_approved = response.old_comment_approved;
		var spam_count = response.spam_count;
		var moderation_count = response.moderation_count;
		var comment_links = response.comment_links;
	}
	if (!error) {
	try {
		self.parent.jQuery.ajaxeditcomments.update_comment("edit-comment" + data.cid,comment);
		self.parent.jQuery.ajaxeditcomments.update_author("edit-author" + data.cid,name, url);
		self.parent.jQuery.ajaxeditcomments.update_date_or_time("aecdate" + data.cid,date);
		self.parent.jQuery.ajaxeditcomments.undo_message(data.cid, undo,true);
		self.parent.jQuery(".spam-count").html(spam_count);
		self.parent.jQuery("#edit-comment-admin-links" + data.cid).html(comment_links);
		self.parent.jQuery(".pending-count").html(moderation_count);
	} catch (err) {}
	$j("#status").attr("class", "success");
	$j("#message").html(wpajaxeditcommentedit.AEC_Saved);
	 parent.jQuery.fn.colorbox.close();
	}
																																																		 }, "json" );
		
  }
  //Cookie code conveniently stolen from http://www.quirksmode.org/js/cookies.html
	function createCookie(name,value,days) {
    if (days) {
      var date = new Date();
      date.setTime(date.getTime()+(days*24*60*60*1000));
      var expires = "; expires="+date.toGMTString();
    }
    else var expires = "";
    document.cookie = name+"="+value+expires+"; path=/";
	}
  function readCookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for(var i=0;i < ca.length;i++) {
      var c = ca[i];
      while (c.charAt(0)==' ') c = c.substring(1,c.length);
      if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
    }
    return null;
  }
  	$j("body").attr("style", "display: block;");
	$j.ajaxcommenteditor.init();
	
});/*WP Ajax Edit Comments Editor Interface Script
--Created by Ronald Huereca
--Created on: 05/04/2008
--Last modified on: 10/25/2008
--Relies on jQuery, wp-ajax-edit-comments, wp-ajax-response, thickbox
	
	Copyright 2007,2008  Ronald Huereca  (email : ron alfy [a t ] g m ail DOT com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/
jQuery(document).ready(function() {
var $j = jQuery;
$j.ajaxblacklistcomment = {
	init: function() { if ( jQuery( '.blacklist' ).length <= 0 ) { return; } initialize(); }
};
	//Initializes the edit links
	function initialize() {
  	//Read in cookie values and adjust the toggle box
    //Cancel button
    $j("#cancel,#status a, #close a").bind("click", function() {  parent.jQuery.fn.colorbox.close();
    return false; });
  	//Pre-process data
	var data = {};
    data._ajax_nonce = $j("#_wpnonce").val();
    data.cid = parseInt($j("#commentID").val());
    data.pid = parseInt($j("#postID").val());
    data.action = $j("#action").val();
    
  	//Change the edit text and events
		//Send button event
  	$j("#send-request").bind("click", function() { submit_blacklist( data ); return false; });
		$j("#status").show();
		$j("#status").attr("class", "success");
		$j("#message").html(wpajaxeditcommentedit.AEC_Ready);
	}
  function submit_blacklist(data) {
  	//Update status message
    $j("#status").attr("class", "success");
    $j("#message").html(wpajaxeditcommentedit.AEC_Blacklisting);
    $j("#send-request").attr("disabled", "disabled");
	var parameters = '';
	var length = $j("input:checked").length;
    $j.each($j("input:checked"), function() {
				length -= 1;
				parameters += $j(this).val();
				if (length > 0) { parameters += ",";}
		});
		data = $j.extend( data, { parameters: parameters });
    	jQuery.post( ajaxurl, data, 
		function( response ) {
			$j("#send-request").removeAttr("disabled");
			if ( typeof response.error != "undefined" ) {
				$j("#status").attr("class", "error");
				$j("#message").html( response.error );
				return;
			}
			self.parent.jQuery("#edit-comment-admin-links" + data.cid).html(response.comment_links);
			self.parent.jQuery(".spam-count").html(response.spam_count);
			self.parent.jQuery(".pending-count").html(response.moderation_count);
			$j("#send-request,#cancel").remove();
			$j("#message").html(response.message);
		}, 'json' ); //end jquery post
  } //end submit_blacklist
	$j("body").attr("style", "display: block;");
	$j.ajaxblacklistcomment.init();
});/*WP Ajax Edit Comments Pop-up Script
--Created by Ronald Huereca
--Created on: 02/18/2010
--Last modified on: 02/18/2010
--Relies on jQuery, wp-ajax-edit-comments, wp-ajax-response, thickbox
	
	Copyright 2010  Ronald Huereca  (email : ron alfy [a t ] g m ail DOT com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/
jQuery(document).ready(function() {
var $j = jQuery;
$j.ajaxcommentpopup = {
	init: function() { if ( jQuery( '#aec-popup' ).length <= 0 ) { return; } close_event();  after_the_deadline(); buttons(); commentbox_height(); fill_commentbox(); $j("#comment").focus(); },
	closed: function() {
		if (aec_popup.atd == 'true') { $j(".AtD_edit_button").trigger("click"); }
		parent.jQuery("#comment").attr("value", $j("#comment").attr("value"));
		parent.jQuery("#comment").focus();
	}
};
	function buttons() {
		$j("#aec_edit_options").append("<span class='aec_retract'></span>");
		$j("#close, .aec_retract").bind("click",function() {  
		 	parent.jQuery.fn.colorbox.close();
		 });	
		$j("#submit").bind("click",function() { 
			if (aec_popup.atd == 'true') { $j(".AtD_edit_button").trigger("click"); }								
		 	parent.jQuery("#comment").attr("value", $j("#comment").attr("value"));
			parent.jQuery("#commentform input[name='submit']").trigger("click");
		 });
	}
	function close_event() {
		parent.jQuery(".aec_expand").bind("cbox_cleanup", function() { 
			$j.ajaxcommentpopup.closed();
			parent.jQuery(".aec_expand").unbind("cbox_cleanup");
		});
	}
	function after_the_deadline() {
		AtD.rpc_css_lang = aec_popup.atdlang;
		if (aec_popup.atd == 'false') { return; }
		$j('#comment').addProofreader();
		var spellcheck = $j("#AtD_0").clone(true);
		$j("#AtD_0").remove();
		$j("#aec_edit_options").append(spellcheck);
	}
	function commentbox_height() {
		var height = $j("#comment").height();
		$j("#comment").css('height', parent.jQuery("#cboxContent").height() - $j("body").height() + height-35);
	}
	function fill_commentbox() {
		var text = parent.jQuery("#comment").attr("value");
		$j("#comment").attr("value", text);
	}
	$j.ajaxcommentpopup.init();
	
});/*WP Ajax Edit Comments Editor Interface Script
--Created by Ronald Huereca
--Created on: 05/04/2008
--Last modified on: 03/22/2011
--Relies on jQuery, wp-ajax-edit-comments, wp-ajax-response, thickbox
	
	Copyright 2007,2008  Ronald Huereca  (email : ron alfy [a t ] g m ail DOT com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/
jQuery(document).ready(function() {
var $j = jQuery;
$j.ajaxemail = {
	init: function() { if ( jQuery( 'body.email' ).length <= 0 ) { return; } initialize_events();after_the_deadline();}
};
	//Initializes the edit links
	function initialize_events() {
    //Cancel button
    $j("#cancel,#status a, #close a").bind("click", function() {  parent.jQuery.fn.colorbox.close();
    return false; });
		$j("#send").bind("click", function() {
			//Clear errors
			$j("#to, #from, #subject, #message, #status").removeClass("error");
			$j("#status_message").html('');
			//Perform validation
			 if (do_validation()) {
				email_comment(); 		
			 }
		});
  	} //end initialize_events
  	function after_the_deadline() {
		if (wpajaxeditcommentedit.AEC_AftertheDeadline == 'false') { return; }
		AtD.rpc_css_lang = wpajaxeditcommentedit.AEC_AftertheDeadline_lang;
		$j('#message').addProofreader();
		var spellcheck = $j("#AtD_0").clone(true);
		$j("#AtD_0").remove();
		$j("#edit_options").append(spellcheck);
	}
	//Checks all fields for validation
	function do_validation() {
		//After the deadline - Move this to function email_comment after performing AJAX validation instead
		 if (wpajaxeditcommentedit.AEC_AftertheDeadline == 'true') {
			 $j(".AtD_edit_button").trigger("click");
		 }
		//Check to see if fields are empty
		var to = $j.trim($j("input[name='to']").attr("value"));
		var from = $j.trim($j("select[name='from']").attr("value"));
		var subject = $j.trim($j("#subject").attr("value"));
		var message = $j.trim($j("#message").attr("value"));
		var error = false;
		//Check to see if fields are empty
		if (to == "") { $j("#to").addClass("error"); error = true;}
		if (from == "") { $j("#from").addClass("error"); error = true;}
		if (subject == "") { $j("#subject").addClass("error"); error = true;}
		if (message == "") { $j("#message").addClass("error"); error = true;}
		
		if (error) {
			$j("#status").removeClass();
			 $j("#status").addClass("error");
			 $j("#status_message").html(wpajaxeditcommentedit.AEC_fieldsrequired);
			 return false;
		}
		return true;
	}
	function email_comment() {
		//Pre-process data
		var nonce = $j("#_wpnonce").attr("value");
		var cid = parseInt($j("#commentID").attr("value"));
		var pid = parseInt($j("#postID").attr("value"));
		var action = $j("#action").attr("value");
		var to = encodeURIComponent($j.trim($j("#to").attr("value")));
		var from = encodeURIComponent($j.trim($j("#from").attr("value")));
		var subject = encodeURIComponent($j.trim($j("#subject").attr("value")));
		var message = encodeURIComponent($j.trim($j("#message").attr("value")));
		//Change the edit text and events
		$j("#status").show();
		$j("#status").attr("class", "success");
		$j("#status_message").html(wpajaxeditcommentedit.AEC_Sending);
		
		jQuery.post( ajaxurl, { _ajax_nonce:nonce,action:action, cid: cid,pid:pid,to:to,from:from,subject:subject,message:message },
function(response){
	if ( typeof response.error != "undefined" ) { //error
		$j("#status").removeClass();
		$j("#status").addClass("error");
		$j("#to").addClass("error");
		$j("#status_message").html(wpajaxeditcommentedit.AEC_emailaddresserror);
	} else {
		$j("#status_message").html(wpajaxeditcommentedit.AEC_Sent);
		parent.jQuery.fn.colorbox.close();
	}
}, 'json' );
  } //end email_comment
	$j.ajaxemail.init(); 
	$j("body").attr("style", "display: block;");
});
/*WP Ajax Edit Move Comments Script
--Created by Ronald Huereca
--Created on: 09/14/2009
--Last modified on: 03/22/1011
--Relies on jQuery, wp-ajax-edit-comments, wp-ajax-response, thickbox
	
	Copyright 2007-2010  Ronald Huereca  (email : ron alfy [a t ] g m ail DOT com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/
jQuery(document).ready(function() {
var $j = jQuery;
$j.ajaxmovecomment = {
	init: function() { if ( jQuery( 'body.move' ).length <= 0 ) { return; } initialize_events(); load_posts(); }
};
	//Initializes the edit links
	function initialize_events() {
    //Cancel button
    $j("#cancel,#status a, #close a").bind("click", function() {  parent.jQuery.fn.colorbox.close();
    return false; });
    //Title for new window
    $j("#title a").bind("click", function() { window.open(this.href); return false; } );
    
		//Title Search button
		$j("#title_search").bind("click", function() {
			$j("#post_title_move").attr("disabled", "disabled");																				 
			load_title_ajax();
		});
		
		//ID Search button
		$j("#id_search").bind("click", function() {
			$j("#post_id_move").attr("disabled", "disabled");
			var data = pre_process();	
			data.post_id = $j("#post_id").val();
		
			//Show and hide certain elements
			$j("#post_id_buttons").addClass("hidden");
			$j("#post_id_loading").removeClass("hidden");
			$j("#id_search").attr("disabled", "disabled");
			$j("#post_id_radio").html("");
			jQuery.post(ajaxurl, data, 
			function ( response ) {
				var count = 0; 
				var radio = '';
				$j("#id_search").removeAttr("disabled");
				$j("#post_id_loading").addClass("hidden");
				if ( typeof response.posts != "undefined" ) {
					count += 1;
					radio += "<input type='radio' name='posts_id' id='posts_id_" + response.posts.post_id + "' value='" + response.posts.post_id + "' />&nbsp;&nbsp;<label for='posts_id_" + response.posts.post_id + "'>" + response.posts.post_title + "</label><br />";	
				}
				
				if (count >= 1) {
					$j("#post_id_buttons").removeClass("hidden");
				}
				//write to screen
				$j("#post_id_radio").html(radio);
				
				//Setup Events for ID
				$j("input[name='posts_id']").click(function() { 
					$j("#post_id_move").removeAttr("disabled");
					var new_id = $j(this).val();
					$j("#post_id_move").bind("click", function() {
						$j("#post_id_move").attr("disabled", "disabled");
						var data = pre_process();
						data.newid = new_id;			
						data = check_approve( data );
						jQuery.post(ajaxurl, data, 
						function ( response ) {
							//for the admin panel
							update_admin_panel( response );
							parent.jQuery.fn.colorbox.close();
						}, 'json' );
					}); //end #post_id_move click																 
				});
			}, 'json' );
		}); //end id_search click
  }
	//Checks to see if the approve button is available and only adds it if the value is one
	function check_approve(data) {
		if ($j("#approved:checked").length > 0) {
				data = $j.extend(data, { approve: "1"});
		}
		return data;
	}
	function load_title_ajax() {
		var data = pre_process();	
		data.post_title =  $j("#move_title").val();
		
		//Show and hide certain elements
		$j("#post_title_buttons").addClass("hidden");
		$j("#post_title_loading").removeClass("hidden");
		$j("#title_search").attr("disabled", "disabled");
		$j("#post_title_radio").html("");
		jQuery.post(ajaxurl, data, 
		function ( response ) {
			count = 0; radio = '';
			$j("#title_search").removeAttr("disabled");
			$j("#post_title_loading").addClass("hidden");
			$j.each( response.posts, function() {
					if (this.data != '') {
					count += 1;
						radio += "<input type='radio' name='posts_title' id='post_title_" + this.post_id + "' value='" + this.post_id + "' />&nbsp;&nbsp;<label for='post_title_" + this.post_id + "'>" + this.post_title + "</label><br />";
					}
			});
			if (count >= 1) {
				$j("#post_title_buttons").removeClass("hidden");
			}
			//write to screen
			$j("#post_title_radio").html(radio);
			
			//Setup events for title
			$j("input[name='posts_title']").click(function() { 
				$j("#post_title_move").removeAttr("disabled");
				var new_id = $j(this).val();
				$j("#post_title_move").bind("click", function() { 
					$j("#post_title_move").attr("disabled", "disabled");
					var data = pre_process();
					data.newid = new_id;			
					data = check_approve( data );
					jQuery.post(ajaxurl, data, 
					function ( response ) {
						//for the admin panel
						update_admin_panel( response );
						parent.jQuery.fn.colorbox.close();
					}, 'json' );
				});																			 
			});

		}, 'json' );
	}
	//Loads a group of posts in the Posts tab.
	//post_offset and direction (true, or false)
	function load_posts_ajax(post_offset, dir) {
		if (post_offset < 0) { 
			post_offset = 0;
		}
		var data = pre_process();
		data.post_offset = post_offset;
		
		jQuery.post(ajaxurl, data, 
		function( response ) {
			if ( typeof response.error != "undefined" ) { alert( response.error ); return; }
			var radio = "";
			var count = 0;
			//Display found posts
			$j.each( response.posts, function() {
				count += 1;
				if ( count < 6 ) {
					radio += "<input type='radio' name='posts' id='post_" + this.post_id + "' value='" + this.post_id + "' />&nbsp;&nbsp;<label for='post_" + this.post_id + "'>" + this.post_title + "</label><br />";		
				}
			});
			//Show and hide certain elements
			$j("#post_loading").addClass("hidden");
			$j("#post_buttons").removeClass("hidden");
			//write to screen
			$j("#post_radio").html(radio);
			
			//Setup events for posts
			$j("input[name='posts']").click(function() { 
				$j("#post_move").removeAttr("disabled");
				var new_id = $j(this).val();
				$j("#post_move").bind("click", function() { 
					$j("#post_move").attr("disabled", "disabled");
					var data = pre_process();
					data.newid = new_id;			
					data = check_approve( data );
					jQuery.post(ajaxurl, data, 
					function ( response ) {
						//for the admin panel
						update_admin_panel( response );
						parent.jQuery.fn.colorbox.close();
					}, 'json' );
				});																			 
			}); //end event for posts
				
			//Write the offset
			//$j("#post_offset").attr("value", count);
			if (count == 6 && dir == "true") {
				//Show next button
				$j("#post_next").removeClass("hidden");
				if (post_offset >= 5) {
					$j("#post_previous").removeClass("hidden");
				}
			} else if (count > 0 && count < 6 && dir == "true") {
				$j("#post_previous").removeClass("hidden");
			}
			if (post_offset >= 5 && dir == "false") {
				$j("#post_offset").attr("value", post_offset);
				$j("#post_previous").removeClass("hidden");
				$j("#post_next").removeClass("hidden");
			} else if (post_offset == 0) {
					$j("#post_next").removeClass("hidden");
			}
		}, 'json' );
 
    	
	} //end load_posts_ajax
	function load_posts() {
		load_posts_ajax(parseInt($j("#post_offset").attr("value")), "true");
		$j("#post_next").bind("click", function() {
			$j("#post_loading").removeClass("hidden");
			$j("#post_radio").html("");
			$j("#post_previous").addClass("hidden");
			$j("#post_next").addClass("hidden");
			$j("#post_buttons").addClass("hidden");
			$j("#post_move").attr("disabled", "disabled");
			p = pre_process();
			var post_offset = parseInt($j("#post_offset").attr("value")) + 5;
			$j("#post_offset").attr("value", post_offset);
			load_posts_ajax(post_offset, "true");
			return false;
		});
		$j("#post_previous").bind("click", function() { 
			$j("#post_loading").removeClass("hidden");
			$j("#post_radio").html("");
			$j("#post_previous").addClass("hidden");
			$j("#post_next").addClass("hidden");
			$j("#post_buttons").addClass("hidden");
			$j("#post_move").attr("disabled", "disabled");
			p = pre_process();
			var post_offset = parseInt($j("#post_offset").attr("value")) - 5;
			$j("#post_offset").attr("value", post_offset);
			load_posts_ajax(post_offset, "false");
			return false;
		});
	}
	//Updates the admin panel when someone moves a comment
	function update_admin_panel( response ) {
		if ( typeof response.nochange != "undefined" ) { return; }
		
		var comment_id = response.comment_id;
		//Update New ID
		var newID = response.new_post.new_id;
		var oldID = response.new_post.old_id;
		var title = response.new_post.title;
		var comments = response.new_post.comments;
		var permalink =response.new_post.permalink;
		
		//Update the edit post link
		if (self.parent.jQuery("#comment-" + comment_id + " .post-com-count-wrapper a:first").length != 0) {
			var new_edit_url = self.parent.jQuery("#comment-" + comment_id + " .post-com-count-wrapper a:first").attr("href");
			//todo - need to update regex to work in admin panel
			new_edit_url = new_edit_url.replace(/[0-9]+$/,newID);
			self.parent.jQuery("#comment-" + comment_id + " .post-com-count-wrapper a:first").attr("href", new_edit_url);
			self.parent.jQuery("#comment-" + comment_id + " .post-com-count-wrapper a:first").html(title);
			
			//Update the edit comment link
			var new_comment_url = self.parent.jQuery("#comment-" + comment_id + " .post-com-count").attr("href");
			new_comment_url = new_comment_url.replace(/[0-9]+$/,newID);
			self.parent.jQuery("#comment-" + comment_id + " .post-com-count-wrapper a:last").attr("href",new_comment_url);
			
			//Update the permalink
			self.parent.jQuery("#comment-" + comment_id + " .response-links a:last").attr("href",permalink);
			
			
			//Update the comments count
			$j.each(self.parent.jQuery(".response:contains(" + title + ")"), function() {
				$j(this).find(".comment-count").html(comments);																																																																											
				}
			);
		}
		//Update Old ID
		var comments = response.old_post.comments;
		var title = response.old_post.title;
		//Update the comments count
		$j.each(self.parent.jQuery(".response:contains(" + title + ")"), function() {
			$j(this).find(".comment-count").html(comments);																																																																											
			}
		);
		
		if ( typeof response.approved != "undefined" ) {
			self.parent.jQuery(".spam-count").html(response.approved.spam_count);
			self.parent.jQuery(".pending-count").html(response.approved.moderation_count);
			self.parent.jQuery(".aec-approve-" + comment_id + ",#approve-comment-" + comment_id).hide();
			self.parent.jQuery(".aec-spam-" + comment_id + ",#spam-comment-" + response.approved.comment_id).show();
			self.parent.jQuery(".aec-moderate-" + comment_id + ",#moderate-comment-" + comment_id).show();
		} 
		
		if ( typeof response.status_message != "undefined" ) {
			self.parent.jQuery("#comment-undo-" + comment_id).html( response.status_message);
		}
	} //end update_admin_panel
	function pre_process(element) {
		var data = {};
		data._ajax_nonce = $j("#_wpnonce").val();
		data.cid = parseInt($j("#commentID").val());
		data.pid = parseInt($j("#postID").val());
		data.action = $j("#action").val();
		return data;
	};
	$j.ajaxmovecomment.init();
	$j('body').show();
	$j("body").attr("style", "display: block;");
});/*WP Ajax Edit Comments Editor Interface Script
--Created by Ronald Huereca
--Created on: 05/04/2008
--Last modified on: 01/07/2010
--Relies on jQuery, wp-ajax-edit-comments, wp-ajax-response, thickbox
	
	Copyright 2007-2010  Ronald Huereca  (email : ron alfy [a t ] g m ail DOT com)

*/
jQuery(document).ready(function() {
var $j = jQuery;
$j.ajaxrequestdeletion = {
	init: function() { if ( jQuery( 'body.request-deletion' ).length <= 0 ) { return; } initialize(); after_the_deadline(); }
};
	function after_the_deadline() {
		if (wpajaxeditcommentedit.AEC_AftertheDeadline == 'false') { return; }
		if ( typeof AtD.rpc_css_lang != "undefined" ) {
			AtD.rpc_css_lang = wpajaxeditcommentedit.AEC_AftertheDeadline_lang;
			$j('#deletion-reason').addProofreader();
		}
	}
	//Initializes the edit links
	function initialize() {
  	//Read in cookie values and adjust the toggle box
    //Cancel button
    $j("#cancel,#status a, #close a").bind("click", function() {  parent.jQuery.fn.colorbox.close();
    return false; });
  	//Pre-process data
	var data = {};
  	data.cid = parseInt($j("#commentID").val());
    data.pid = parseInt($j("#postID").val());
    data.action = $j("#action").val();
  	data._ajax_nonce = $j("#_wpnonce").val();
    
  	//Change the edit text and events
    $j("#status").show();
    $j("#status").attr("class", "success");
  	$j("#message").html(wpajaxeditcommentedit.AEC_Ready);
		//Send button event
  	$j("#send-request").bind("click", function() { send_request( data ); return false; });
	}
  function send_request(data) {
	 //After the deadline - 
	 if (wpajaxeditcommentedit.AEC_AftertheDeadline == 'true') {
		 $j(".AtD_edit_button").trigger("click");
	 }
  	//Update status message
	data.message = encodeURIComponent($j("#deletion-reason").val());
	
    $j("#status").attr("class", "success");
    $j("#message").html(wpajaxeditcommentedit.AEC_Sending);
    $j("#send-request").attr("disabled", "disabled");
		
	jQuery.post( ajaxurl, data, 
	function( response ) {
		$j("#message").html(wpajaxeditcommentedit.AEC_RequestDeletionSuccess);
		//if response error
		if ( typeof response.error != "undefined" ) {
			$j("#message").html(wpajaxeditcommentedit.AEC_RequestError);
			$j("#status").attr("class", "error");
			return;
		}
		try {
			self.parent.jQuery("#comment-undo-" + response.cid).html(wpajaxeditcommentedit.AEC_RequestDeletionSuccess);
			self.parent.jQuery("#edit" + response.cid).unbind();
			self.parent.jQuery("#edit-comment-user-link-" + response.cid).remove();
			//close thickbox
		  parent.jQuery.fn.colorbox.close();
		} catch(err) {}
	}, 'json'); //end ajax
    
  } //end send_request
	$j.ajaxrequestdeletion.init();
});/*
 * jquery.tools 1.1.2 - The missing UI library for the Web
 * 
 * [tools.tabs-1.0.4, tools.tabs.history-1.0.2, tools.tooltip-1.1.3]
 * 
 * Copyright (c) 2009 Tero Piirainen
 * http://flowplayer.org/tools/
 *
 * Dual licensed under MIT and GPL 2+ licenses
 * http://www.opensource.org/licenses
 * 
 * -----
 * 
 * File generated: Fri Apr 23 18:35:58 GMT 2010
 */
(function(d){d.tools=d.tools||{};d.tools.tabs={version:"1.0.4",conf:{tabs:"a",current:"current",onBeforeClick:null,onClick:null,effect:"default",initialIndex:0,event:"click",api:false,rotate:false},addEffect:function(e,f){c[e]=f}};var c={"default":function(f,e){this.getPanes().hide().eq(f).show();e.call()},fade:function(g,e){var f=this.getConf(),j=f.fadeOutSpeed,h=this.getPanes();if(j){h.fadeOut(j)}else{h.hide()}h.eq(g).fadeIn(f.fadeInSpeed,e)},slide:function(f,e){this.getPanes().slideUp(200);this.getPanes().eq(f).slideDown(400,e)},ajax:function(f,e){this.getPanes().eq(0).load(this.getTabs().eq(f).attr("href"),e)}};var b;d.tools.tabs.addEffect("horizontal",function(f,e){if(!b){b=this.getPanes().eq(0).width()}this.getCurrentPane().animate({width:0},function(){d(this).hide()});this.getPanes().eq(f).animate({width:b},function(){d(this).show();e.call()})});function a(g,h,f){var e=this,j=d(this),i;d.each(f,function(k,l){if(d.isFunction(l)){j.bind(k,l)}});d.extend(this,{click:function(k,n){var o=e.getCurrentPane();var l=g.eq(k);if(typeof k=="string"&&k.replace("#","")){l=g.filter("[href*="+k.replace("#","")+"]");k=Math.max(g.index(l),0)}if(f.rotate){var m=g.length-1;if(k<0){return e.click(m,n)}if(k>m){return e.click(0,n)}}if(!l.length){if(i>=0){return e}k=f.initialIndex;l=g.eq(k)}if(k===i){return e}n=n||d.Event();n.type="onBeforeClick";j.trigger(n,[k]);if(n.isDefaultPrevented()){return}c[f.effect].call(e,k,function(){n.type="onClick";j.trigger(n,[k])});n.type="onStart";j.trigger(n,[k]);if(n.isDefaultPrevented()){return}i=k;g.removeClass(f.current);l.addClass(f.current);return e},getConf:function(){return f},getTabs:function(){return g},getPanes:function(){return h},getCurrentPane:function(){return h.eq(i)},getCurrentTab:function(){return g.eq(i)},getIndex:function(){return i},next:function(){return e.click(i+1)},prev:function(){return e.click(i-1)},bind:function(k,l){j.bind(k,l);return e},onBeforeClick:function(k){return this.bind("onBeforeClick",k)},onClick:function(k){return this.bind("onClick",k)},unbind:function(k){j.unbind(k);return e}});g.each(function(k){d(this).bind(f.event,function(l){e.click(k,l);return false})});if(location.hash){e.click(location.hash)}else{if(f.initialIndex===0||f.initialIndex>0){e.click(f.initialIndex)}}h.find("a[href^=#]").click(function(k){e.click(d(this).attr("href"),k)})}d.fn.tabs=function(i,f){var g=this.eq(typeof f=="number"?f:0).data("tabs");if(g){return g}if(d.isFunction(f)){f={onBeforeClick:f}}var h=d.extend({},d.tools.tabs.conf),e=this.length;f=d.extend(h,f);this.each(function(l){var j=d(this);var k=j.find(f.tabs);if(!k.length){k=j.children()}var m=i.jquery?i:j.children(i);if(!m.length){m=e==1?d(i):j.parent().find(i)}g=new a(k,m,f);j.data("tabs",g)});return f.api?g:this}})(jQuery);
(function(d){var a=d.tools.tabs;a.plugins=a.plugins||{};a.plugins.history={version:"1.0.2",conf:{api:false}};var e,b;function c(f){if(f){var g=b.contentWindow.document;g.open().close();g.location.hash=f}}d.fn.onHash=function(g){var f=this;if(d.browser.msie&&d.browser.version<"8"){if(!b){b=d("<iframe/>").attr("src","javascript:false;").hide().get(0);d("body").append(b);setInterval(function(){var i=b.contentWindow.document,j=i.location.hash;if(e!==j){d.event.trigger("hash",j);e=j}},100);c(location.hash||"#")}f.bind("click.hash",function(h){c(d(this).attr("href"))})}else{setInterval(function(){var j=location.hash;var i=f.filter("[href$="+j+"]");if(!i.length){j=j.replace("#","");i=f.filter("[href$="+j+"]")}if(i.length&&j!==e){e=j;d.event.trigger("hash",j)}},100)}d(window).bind("hash",g);return this};d.fn.history=function(g){var h=d.extend({},a.plugins.history.conf),f;g=d.extend(h,g);this.each(function(){var j=d(this).tabs(),i=j.getTabs();if(j){f=j}i.onHash(function(k,l){if(!l||l=="#"){l=j.getConf().initialIndex}j.click(l)});i.click(function(k){location.hash=d(this).attr("href").replace("#","")})});return g.api?f:this}})(jQuery);
(function(c){var d=[];c.tools=c.tools||{};c.tools.tooltip={version:"1.1.3",conf:{effect:"toggle",fadeOutSpeed:"fast",tip:null,predelay:0,delay:30,opacity:1,lazy:undefined,position:["top","center"],offset:[0,0],cancelDefault:true,relative:false,oneInstance:true,events:{def:"mouseover,mouseout",input:"focus,blur",widget:"focus mouseover,blur mouseout",tooltip:"mouseover,mouseout"},api:false},addEffect:function(e,g,f){b[e]=[g,f]}};var b={toggle:[function(e){var f=this.getConf(),g=this.getTip(),h=f.opacity;if(h<1){g.css({opacity:h})}g.show();e.call()},function(e){this.getTip().hide();e.call()}],fade:[function(e){this.getTip().fadeIn(this.getConf().fadeInSpeed,e)},function(e){this.getTip().fadeOut(this.getConf().fadeOutSpeed,e)}]};function a(f,g){var p=this,k=c(this);f.data("tooltip",p);var l=f.next();if(g.tip){l=c(g.tip);if(l.length>1){l=f.nextAll(g.tip).eq(0);if(!l.length){l=f.parent().nextAll(g.tip).eq(0)}}}function o(u){var t=g.relative?f.position().top:f.offset().top,s=g.relative?f.position().left:f.offset().left,v=g.position[0];t-=l.outerHeight()-g.offset[0];s+=f.outerWidth()+g.offset[1];var q=l.outerHeight()+f.outerHeight();if(v=="center"){t+=q/2}if(v=="bottom"){t+=q}v=g.position[1];var r=l.outerWidth()+f.outerWidth();if(v=="center"){s-=r/2}if(v=="left"){s-=r}return{top:t,left:s}}var i=f.is(":input"),e=i&&f.is(":checkbox, :radio, select, :button"),h=f.attr("type"),n=g.events[h]||g.events[i?(e?"widget":"input"):"def"];n=n.split(/,\s*/);if(n.length!=2){throw"Tooltip: bad events configuration for "+h}f.bind(n[0],function(r){if(g.oneInstance){c.each(d,function(){this.hide()})}var q=l.data("trigger");if(q&&q[0]!=this){l.hide().stop(true,true)}r.target=this;p.show(r);n=g.events.tooltip.split(/,\s*/);l.bind(n[0],function(){p.show(r)});if(n[1]){l.bind(n[1],function(){p.hide(r)})}});f.bind(n[1],function(q){p.hide(q)});if(!c.browser.msie&&!i&&!g.predelay){f.mousemove(function(){if(!p.isShown()){f.triggerHandler("mouseover")}})}if(g.opacity<1){l.css("opacity",g.opacity)}var m=0,j=f.attr("title");if(j&&g.cancelDefault){f.removeAttr("title");f.data("title",j)}c.extend(p,{show:function(r){if(r){f=c(r.target)}clearTimeout(l.data("timer"));if(l.is(":animated")||l.is(":visible")){return p}function q(){l.data("trigger",f);var t=o(r);if(g.tip&&j){l.html(f.data("title"))}r=r||c.Event();r.type="onBeforeShow";k.trigger(r,[t]);if(r.isDefaultPrevented()){return p}t=o(r);l.css({position:"absolute",top:t.top,left:t.left});var s=b[g.effect];if(!s){throw'Nonexistent effect "'+g.effect+'"'}s[0].call(p,function(){r.type="onShow";k.trigger(r)})}if(g.predelay){clearTimeout(m);m=setTimeout(q,g.predelay)}else{q()}return p},hide:function(r){clearTimeout(l.data("timer"));clearTimeout(m);if(!l.is(":visible")){return}function q(){r=r||c.Event();r.type="onBeforeHide";k.trigger(r);if(r.isDefaultPrevented()){return}b[g.effect][1].call(p,function(){r.type="onHide";k.trigger(r)})}if(g.delay&&r){l.data("timer",setTimeout(q,g.delay))}else{q()}return p},isShown:function(){return l.is(":visible, :animated")},getConf:function(){return g},getTip:function(){return l},getTrigger:function(){return f},bind:function(q,r){k.bind(q,r);return p},onHide:function(q){return this.bind("onHide",q)},onBeforeShow:function(q){return this.bind("onBeforeShow",q)},onShow:function(q){return this.bind("onShow",q)},onBeforeHide:function(q){return this.bind("onBeforeHide",q)},unbind:function(q){k.unbind(q);return p}});c.each(g,function(q,r){if(c.isFunction(r)){p.bind(q,r)}})}c.prototype.tooltip=function(e){var f=this.eq(typeof e=="number"?e:0).data("tooltip");if(f){return f}var g=c.extend(true,{},c.tools.tooltip.conf);if(c.isFunction(e)){e={onBeforeShow:e}}else{if(typeof e=="string"){e={tip:e}}}e=c.extend(true,g,e);if(typeof e.position=="string"){e.position=e.position.split(/,?\s/)}if(e.lazy!==false&&(e.lazy===true||this.length>20)){this.one("mouseover",function(h){f=new a(c(this),e);f.show(h);d.push(f)})}else{this.each(function(){f=new a(c(this),e);d.push(f)})}return e.api?f:this}})(jQuery);
jQuery(document).ready(function() {
	//For the tabs
		try {
			jQuery("ul.tabs").tabs("div.panes > div", { effect: "fade",fadeInSpeed: 400}).history();
		} catch(err) { }
});