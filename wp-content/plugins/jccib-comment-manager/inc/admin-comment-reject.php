<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function get_jccib_action_name(){
    if(isset($_GET['action'])){
        if($_GET['action'] == 'reject'){
           return '棄却'; 
        }else if($_GET['action'] == 'disapproveit'){
           return '承認取り消し';             
        }
    }
    return '';
}

function notify_disapprove_jimukyoku($query)
{
    global $wpdb;

    $settings = get_option('enocp_user_settings');

    $subject = sprintf($settings['disapprove-subject'],$query->id);
    $body = sprintf($settings['disapprove-body'],$query->id,$query->subject,$_POST['content_reply_cdF']);

    $revs = $settings['notification-email'];
    array_push($revs,$query->email);

    add_filter ( 'wp_mail_content_type', 'set_html_content_type' );
    $wpmail = wp_mail ( $revs, $subject, $body );
    remove_filter ( 'wp_mail_content_type', 'set_html_content_type' );
    return $wpmail;
}


function notify_reject_jimukyoku($query){
    $delete_id = $query->id;
    $settings = get_option('enocp_user_settings');

    $subject = sprintf($settings['reject-subject'],$delete_id);
    $body = sprintf($settings['reject-body'],$delete_id,$query->subject,$_POST['content_reply_cdF']);

    add_filter ( 'wp_mail_content_type', 'set_html_content_type' );
    $revs = $settings['notification-email'];
    array_push($revs,$query->email);
    $sent = wp_mail ( $revs, $subject , wpautop($body));        
    remove_filter ('wp_mail_content_type', 'set_html_content_type' );
    return $sent;
}
    
function tt_render_reject_page()
{
    	global $wpdb;
    	$query = $wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'contact_form WHERE id='.$_REQUEST['enquiry'] );
        if(empty($query)){
            wp_safe_redirect(menu_page_url('tt_enquiry_test',false));
        }
    	$sent = false;
    	if(isset($_POST['content_reply_cdF']) && isset($_POST['action'])){
            if($_POST['action'] == 'reject'){
                $sent = notify_reject_jimukyoku($query);                
                if($sent){
                    jccib_change_comment_status($wpdb,$query->id,'R',$_POST['content_reply_cdF']);
                }               
            }else if($_POST['action'] == 'disapproveit'){
                $sent = notify_disapprove_jimukyoku($query);                
                if($sent){
                    jccib_change_comment_status($wpdb,$query->id,'N');
                }                               
            }
    	}
        $action_reject = $_GET['action'];
     ob_start();
    if(empty($_POST['content_reply_cdF'])){
?>
    <style>
        #wp-content_reply_cdF-wrap { width:600px; }
        #content_reply_cdF {  height:300px;}
    </style>
    <div class="warp" style="padding:20px;">
    <h2>投稿の<?php echo get_jccib_action_name(); ?></h2>
    <input action="action" type="button" value="戻る" class="button button-primary" onclick="history.go(-1);"  style="float:left;"/>
    <br/><br/><br/>
    <table class="widefat fixed" cellspacing="0">
		<tbody>
			<tr class="alternate">
				<td class="column-columnname"  style="width:150px;" scope="row">会社名</td>
				<td class="column-columnname"><?php echo $query->name; ?></td>
			</tr>
			<tr>
				<td class="column-columnname" scope="row">Eメール</td>
				<td class="column-columnname"><?php echo $query->email; ?></td>
			</tr>
			<tr class="alternate">
				<td class="column-columnname"  style="width:150px;" scope="row">連絡先</td>
				<td class="column-columnname"><?php echo empty($query->mobile)?$query->mobile:'未記入'; ?></td>
			</tr>
			<tr>
				<td class="column-columnname" scope="row">本文</td>
                                <td class="column-columnname"><?php echo wpautop($query->message); ?></td>
			</tr>
			<tr class="alternate">
				<td class="column-columnname"  style="width:150px;" scope="row">題名</td>
				<td class="column-columnname"><?php echo $query->subject; ?></td>
			</tr>
            <tr>
                <td class="column-columnname" scope="row">状態</td>
                <td class="column-columnname"><?php echo jccib_get_comment_status_msg($query->comnt_status); ?></td>
            </tr>
            <tr class="alternate">
                <td class="column-columnname"  style="width:150px;" scope="row">掲載期日</td>
                <td class="column-columnname"><?php echo (new DateTime($query->date_expire))->format('Y年m月d日'); ?></td>
            </tr>            		  
			<tr class="alternate">
				<td class="column-columnname"  style="width:150px;" scope="row">投稿日</td>
				<td class="column-columnname"><?php echo (new DateTime($query->date_added))->format('Y年m月d日 H時i分'); ?></td>
			</tr>
		</tbody>
	</table>
</div>
                                    
    <div style="padding-top:20px;padding-bottom:50px;">
    	<h3>上記の投稿を<?php echo get_jccib_action_name(); ?>します。理由を記入し、投稿者に送信します。</h3>
	<hr>
    <script type="text/javascript">
        function check_before_send(){
            if('' == document.getElementById("content_reply_cdF").value){
                alert('理由を入力してください');
                return false;
            }
            if (!confirm('<?php echo get_jccib_action_name(); ?>します。\nよろしいですか？')) {
                return false;
            }            
        }
    </script>   
        <form action="" method="post" onsubmit="return check_before_send();" style="width: 500px; height:250px;">
            <input type="hidden" name="action" value="<?php echo $action_reject; ?>" />
            <input  class="button button-primary" type="submit" name="send_reply" value="<?php echo get_jccib_action_name(); ?>メールの送信">
            <div>
            理由テンプレート
            </div>
            <select id="reason-template">
                <option></option>
                <?php  foreach(get_option('enocp_email_template') as $reason) { ?>
                <option><?php echo $reason; ?></option>
                <?php } ?>
            </select>
            <div>
            <?php echo get_jccib_action_name(); ?>理由
            <textarea class="admin-jccib-input" style="height:200px;" name="content_reply_cdF" id="content_reply_cdF"></textarea><br/>
            </div>
        </form>
    </div>
    <script type="text/javascript">
        jQuery('#reason-template').change(function(){
            var selected = jQuery("select#reason-template option:selected");
            if(selected.length > 0){
                jQuery('#content_reply_cdF').val(jQuery('#content_reply_cdF').val()+selected[0].text);
            }
        });
    </script>
<?php 
    }else{
        if($sent) { // success to send mail and delete the comment
?>
            <h2>投稿の<?php echo get_jccib_action_name(); ?></h2>
            <p>
                投稿が<?php echo get_jccib_action_name(); ?>されました。下記ボタンで、一覧にお戻りください。
            </p>
        <button type="button" onclick="location.href='<?php echo menu_page_url('tt_enquiry_test',false); ?>'" class="button button-primary"  style="float:left;">戻る</button>
<?php
        }else{ // fail to send mail or delte the comment
            ?>
            <h2>投稿の<?php echo get_jccib_action_name(); ?></h2>
            <p>
                メールの送信に失敗しました。申し訳ありませんが、サイトの管理者にお問い合わせください。
            </p>
         <button type="button" onclick="location.href='<?php echo menu_page_url('tt_enquiry_test',false); ?>'" class="button button-primary"  style="float:left;">戻る</button>
<?php
        }
    } // reject

    $content = ob_get_contents ();
    ob_end_clean ();
    echo $content; 
} 
