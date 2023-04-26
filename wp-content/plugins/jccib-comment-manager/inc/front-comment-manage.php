<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function notify_new_comment_jimukyoku($comment,$lastid)
{
    $wpmail = false;
    $expiry_day = $comment['ayear']."年".$comment['amonth']."月".$comment['aday']."日";

    $mail_subject = "新規投稿申請依頼[".$comment['aname']."]";

    $mail_msg = "新しい投稿が投函されました、バンガロール商工会のホームページにログインして、確認してください。<br/>";
    $mail_msg = $mail_msg . "投稿番号: ".$lastid."<br/>";
    $mail_msg = $mail_msg . "会社名: ".$comment['aname']."<br/>";
    $mail_msg = $mail_msg . "題名: ".$comment['asubject']."<br/>";
    $mail_msg = $mail_msg . "掲載期限: ".$expiry_day."<br/>";
   
    add_filter ( 'wp_mail_content_type', 'set_html_content_type' );
    $settings = get_option('enocp_user_settings');
    $wpmail = wp_mail ( $settings['notification-email'], $mail_subject, $mail_msg );

    remove_filter ( 'wp_mail_content_type', 'set_html_content_type' );
    
    return $wpmail;
}

// call from admin-post
function register_comment_members_form($wpnonce){

    $errors = array();
    $res = false;
    $info = get_transient($wpnonce);
    $comment = $info[1];
    
    global $wpdb;
    $attach = empty($comment['attach-list'])? '':serialize($comment['attach-list']);
        $idata = array (
            'name' => $comment['aname'],
            'email' => $comment['aemail'],
            'mobile' => $comment['amobile'],
            'message' => $comment['aenquiry'],
            'subject' => $comment['asubject'],
            'comnt_status' => 'N',
            'file_upload' => $attach,
            'date_expire' => $comment['ayear']."-".$comment['amonth']."-".$comment['aday'],
            'date_added' => current_time( 'mysql' ),	  
	);
 
    if (empty(trim($comment['aname']))
        || empty(trim($comment['aemail']))
        || empty(trim($comment['aenquiry']))
        || empty(trim($comment['asubject']))                
       ){
        $error[] = '必須項目に入力がありません';
    }else{
        $res = $wpdb->insert ( $wpdb->prefix . 'contact_form', $idata );            
    }

    if($res){
        $lastid = $wpdb->get_var("SELECT LAST_INSERT_ID();");        
    }else{
        $errors[] = "投稿の登録に失敗しました。";
        $errors[] = "申し訳ありませんが、管理者にお問い合わせください。";
        set_transient($wpnonce ,array($errors,$comment));
        wp_redirect(get_permalink().'?_wpnonce='.$wpnonce."&error=1");
        exit();
    }
    
    $res = notify_new_comment_jimukyoku($comment,$lastid);
    if(!$res){
        $errors[] = "事務局への通知に失敗しました。";
        $errors[] = "申し訳ありませんが、管理者にお問い合わせください。";
        $errors[] = "登録された投稿に対する番号は".$lastid."です。";        
        set_transient($wpnonce ,array($errors,$comment));
        wp_redirect(get_permalink().'?_wpnonce='.$wpnonce."&error=1");
        exit();
    }    
    delete_transient($wpnonce);
    wp_redirect(get_permalink().'?members_post_id='.$lastid); 
}

// call from admin-post
function submit_comment_members_form($wpnonce){


	$name = $_POST ['aname'];
	$email = $_POST ['aemail'];

        if(isset($_POST ['amobile'])){
            $phone = $_POST ['amobile'] ;            
        }
	
        $query = $_POST ['aenquiry'];
	$subject = $_POST ['asubject'];
        if(!isset($_POST['attach-list'])){
            $_POST['attach-list'] = '';
        }
	$year = $_POST ['ayear'];
	$month = $_POST['amonth'];
	$day = $_POST['aday'];
	$date_expire = $year . '-' . $month . '-' . $day;
        
	$errors = array ();

        if (empty(trim($name))){
           $errors [] = '会社名が入力されていません';            
        }
        if (empty(trim($email))){
	   $errors [] = 'Eメールが入力されていません';            
        }
        
	if (empty ( $errors )) {
	    if (! is_email ( $email )) {
		$errors [] = '不正なメールアドレスです';
	   }
	}
        
        if (empty(trim($query))){
	   $errors [] = '本文が入力されていません';            
        }
        if (empty(trim($subject))){
	   $errors [] = '題名が入力されていません';            
        }        
        if(strtotime($date_expire) < strtotime('now')){
	   $errors [] = '掲載期限を本日より前に設定することはできません';            
        }
        if(strtotime($date_expire) > strtotime('+180 days')){
	   $errors [] = '掲載期限を180日を超えて設定することはできません';            
        }

        if(!empty($errors)){
            set_transient($wpnonce,array($errors,$_POST)) ;
            wp_redirect(get_permalink().'/?_wpnonce='.$wpnonce."&error=1");            
        }else{
            set_transient($wpnonce,array(array(),$_POST));            
            wp_redirect(get_permalink().'/?_wpnonce='.$wpnonce);
        }
}

function delete_file_members_form($post_id,$wpnonce)
{
    // use includes/file.php
    require_once( ABSPATH . 'wp-includes/post.php' );
    
    $res = wp_delete_attachment($post_id,true );
    if ( $res ){
        $val = get_transient($wpnonce);
        if(!empty($val)){
        
            $comment = $val[1];
            if(is_array($comment['attach-list'])){
                foreach($comment['attach-list'] as  $key => $file_id){
                    if($file_id == $post_id){
                        unset($comment['attach-list'][$key]);
                        break;
                    }
                }
            }
            set_transient($wpnonce, array($val[0],$comment));
        }
        $res = array();
        $res['status'] = "OK";
        $res['post_id'] = $post_id;
        
        echo json_encode($res);
    }
    else{
         $res['status'] = "ERROR";
         echo json_encode($res);

    }
    // Output only status with json format
    exit;
}



function upload_file_members_form()
{
    $post_id = 0;
    // use includes/file.php
    require_once( ABSPATH . 'wp-admin/includes/admin.php' );
    
    $id = media_handle_upload( 'qqfile', $post_id );
    if ( is_wp_error( $id ) ){
         $res = array();
         $res['status'] = "ERROR";
         echo json_encode($res);
    }
    else{
        $res = array();
        $res['success'] = true;
        $res['post_id'] = $id;
        $res['file_name'] = get_file_name($id);
        echo json_encode($res);
    }
    // Output only status with json format
    exit;
}

function check_wpnonce_refer()
{
    return (!empty($_POST['_wpnonce']) && !empty($_POST['_wp_http_referer']));
}

function members_form_init()
{

   if ( is_singular() ) {
        global $post;
	if ( false !== strpos( $post->post_content,'['.COMMENT_MANAGER_SHORTCODE))
        {
            if ( isset($_POST) ) {

                if ( isset($_POST['html-upload']) && !empty($_FILES)  && check_wpnonce_refer()) {
                    upload_file_members_form();
                }
                if ( isset($_POST['html-upload']) && isset($_POST['attach_file_id']) && check_wpnonce_refer()) {
                    delete_file_members_form($_POST['attach_file_id'],$_POST['_wpnonce']);
                }
                if(isset($_POST['action']) && check_wpnonce_refer()){
                    if('submit_member_form' == $_POST['action']){
                        submit_comment_members_form($_POST['_wpnonce']);
                    }else if('register_member_form' == $_POST['action'] && check_wpnonce_refer()){
                        register_comment_members_form($_POST['_wpnonce']);
                    }
                }
            }   
            
        }
    }
    // go through, do nothing
}
add_action('template_redirect','members_form_init');


// show form -> confirm -> register -> complete
function confirm_comment_members_form($comment,$wpnonce)
{
      	ob_start ();
?>
<div>
    <div class="content">
        <div class="postform">
	<div class="entry">
            下記内容で新しい投稿を事務局に申請します。<br/>
            事務局から承認されると投稿が表示されます。

            <div class="comment-details">
                <div class="c-name"><label class="comment-lebel">会社名</label><?php echo $comment['aname'];?></div>
                <div class="c-sub-date" style="padding:0px;color:black;font-weight: normal;"><span class="c-subject" style="float:none;"><label class="comment-lebel">題名</label></span><?php echo $comment['asubject'];?></div>
                <div class="c-comment"><label class="comment-lebel">本文</label><div style="padding-top:5px;"><?php echo wpautop($comment['aenquiry']);?></div></div>
                <div style="padding-bottom:5px"><label class="comment-lebel">添付ファイル</label>
                    <div>
                    <?php if(empty($comment['attach-list'])){ ?>
                        なし
                    <?php } else { foreach($comment['attach-list'] as $post_id) {
                        echo '<div>';
                        echo get_file_name($post_id);
                        echo '</div>';
                     }} ?>
                    </div>
                </div> 
                <div style="padding-bottom:5px"><label class="comment-lebel">掲載期限</label><?php echo $comment['ayear'];?>年<?php echo $comment['amonth'];?>月<?php echo $comment['aday'];?>日</div> 
                <div style="padding-bottom:5px;padding-top:20px;" >
                下記の内容は表示されません。(事務局のみ、投稿の確認に使用します。)
                </div>
                <div style="padding-bottom:5px"><label class="comment-lebel">Eメール</label><?php echo $comment['aemail'];?></div>
                <div style="padding-bottom:5px"><label class="comment-lebel">連絡先</label><?php if(!empty($comment['amobile'])) {echo $comment['amobile'];}else{ echo '未記入';} ?></div>
            </div>
            </div>
        </div>
  </div>
    <form action="<?php the_permalink(); ?>" method="post" name="" onsubmit="document.getElementById('submit').disabled=true;" class="short style" accept-charset="utf-8">
                                                        <div class="actions">
                                                                <input type="hidden" name= "action" value="register_member_form" />
                                                                <input type="hidden" id="" name= "_wpnonce" value="<?php echo $wpnonce; ?>" />
                                                                <?php wp_referer_field(true); ?>
								<input type="submit" style="float:left;" value="申請" tabindex="5" id="submit" name="submit" class="next-button2"/>
								<button type="button"style="float:left;margin-left: 20px;" value="Back" onClick="location.href='<?php the_permalink(); ?>?_wpnonce=<?php echo $wpnonce; ?>&error=1'" class="next-button2">戻る</button>
							</div>
							<div class="msg" style="clear:both;"></div>
						</form>
</div>
                                            <?php
	$content = ob_get_contents ();
	ob_end_clean ();
	return $content;
}


function complete_comment_members_form($refer_id,$listing_page_id)
{
    ob_start ();  
?>
    <div>
        <div class="">
	<div class="content">
            <p>
        新しい投稿の申請が完了しました。<br/>
        投稿番号は<?php echo $refer_id; ?>です。<br>
        投稿が承認又は棄却されるまで、上記番号をお控えください。
            </p>
            <p>
        投稿されたした内容に関して、事務局で確認致します。<br/>
        投稿内容が不適切と判断された場合は、メールでご連絡します。
            </p>
        </div>
	</div>				
       <button type="button"  style="float:left;" class="next-button2" onclick="location.href='<?php echo get_permalink($listing_page_id); ?>'" name="back">一覧に戻る</button>
    </div>
<?php
    $content = ob_get_contents ();
    ob_end_clean ();
    return $content;
}

function get_file_name($post_id){
    
    return esc_html( wp_basename( get_post($post_id)->guid ) ); 
}



function create_comment_members_form($listing_page_id, $val = ''){
    if(!empty($val)){
        $errors = $val[0];
        $set_comment = $val[1];   
    }
    ob_start ();
?>
<div>
    <div>
            <div class="content">
		<div class="postform">
                    <p style="color:red;">
                        <?php if(!empty($errors)): foreach ( $errors as $errror ) { $e='';
                            $e .= $errror . '<br/>';
                        }
                        echo $e; endif;?>
                    </p>
                            <div class="entry">	
                                <form action="<?php the_permalink(); ?>" method="post" name="" class="short style" id="submit-members-form" accept-charset="utf-8">
							<div class="author">
                                                            <label>会社名<span style="color:red;">(必須)</span></label>
								<input type="text" class="new-members-form-input required" title="会社名" placeholder="会社名を入力してください" value="<?php if(!empty($set_comment)): echo $set_comment['aname'];  endif; ?>" data-lable="会社名" style="width: 96%;" aria-required="true" size="22" value="" name="aname">
							</div>
							<div class="email">
								<label>Eメール<span style="color:red;">(必須)</span></label>
								<input type="text" class="new-members-form-input required" title="Eメール" placeholder="連絡先のEメールを入力してください" value="<?php if(!empty($set_comment)): echo $set_comment['aemail'];  endif; ?>" data-lable="Eメール" style="width: 96%;" aria-required="true" size="22" value="" name="aemail">
							</div>
							<div class="mobile">
								<label>連絡先番号</label>
								<input type="text" title="連絡先" class="new-members-form-input" placeholder="連絡先を入力してください" value="<?php if(!empty($set_comment)): echo $set_comment['amobile'];  endif; ?>"data-lable="連絡先" style="width: 96%;" size="22" value="" name="amobile" maxlength="20">
							</div>
							<div class="subject">
								<label>題名<span style="color:red;">(必須)</span></label>
								<input type="text" title="題名" class="new-members-form-input required" placeholder="題名を入力してください" value="<?php if(!empty($set_comment)): echo $set_comment['asubject'];  endif; ?>" data-lable="題名" style="width: 96%;" size="22" value="" name="asubject">					
							</div>
							<div class="comment">
								<label>本文<span style="color:red;">(必須)</span></label></br>
								<span class="textar" ><textarea  title="本文" class="required" placeholder="投稿する内容を入力してください" aria-required="true" data-lable="本文" style="height: 90px; width: 96%; resize:vertical;" cols="2" id="enquiry" name="aenquiry"><?php if(!empty($set_comment)): echo $set_comment['aenquiry'];  endif; ?></textarea></span>
							</div>

                                                        <div id="display-attachment">
        						    <label id="label-attach">添付ファイル</label>
                                                            <ul id="attach-list">
                                                                <?php if(empty($set_comment) || empty($set_comment['attach-list'])): ?>
                                                                なし
                                                                <?php else: foreach($set_comment['attach-list'] as $attach_id){ ?>
                                                                <li id="attach-file<?php  echo $attach_id;  ?>"><input type="hidden" name="attach-list[]" value="<?php echo $attach_id; ?>"/> <?php echo get_file_name($attach_id); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type="button" onclick="mydelete_attach_file(<?php echo $attach_id; ?>, '<?php echo $set_comment['_wpnonce']; ?>')">削除</button></li>                                                                    
                                                                <?php } endif;
                                                                ?>
                                                            </ul>
                                                        </div>
                                                            <div>
                                                            最大サイズ: 5MB<br/>
                                                            添付可能なファイル: ワード、エクセル、テキスト、パワーポイント、PDF、画像<br/>
                                                            添付可能な拡張子: jpg, jpeg, png, gif, doc, docx, xls, xlsx, ppt, pptx, pdf, txt
                                                            </div>
                                                        <div id="upload-attachment"></div>
                                                        <div class="date_expire">
								<label for="date_expire">掲載期限</label>
								<?php
                                                                if(!empty($set_comment)){

                                                                        $cyear = $set_comment['ayear'];
                                                                        $cmonth = $set_comment['amonth'];
                                                                        $cday = $set_comment['aday'];                                                                                                                                            
                                                                }else{
                                                                        $cnow = new DateTime();
                                                                        $cyear = $cnow->format("Y");
                                                                        $cmonth = $cnow->format("m");
                                                                        $cday = $cnow->format("d");                                                                                                                                            
                                                                }


                                                                        $days = range ( 1, 31 );
									$months = range ( 1, 12 );
									$years = range( date( 'Y') + 1, date('Y'));
                                                                        
								?>
								<select name="ayear">
									<?php foreach ( $years as $year ) {	?>
										<option <?php if ($year == $cyear){ echo 'selected=selected'; } ?> value="<?php echo($year); ?>" selected><?php echo($year); ?></option>
									<?php } ?>
								</select>年
								<select name="amonth">
									<?php foreach ( $months as $month ) {	?>
										<option <?php if ($month == $cmonth){ echo 'selected=selected'; } ?> value="<?php echo($month); ?>"><?php echo($month); ?></option>
									<?php } ?>
								</select>月
                                                                <select name="aday">
									<?php foreach ( $days as $day ) { ?>
                                                                                        <option <?php if ($day == $cday){ echo 'selected=selected'; } ?> value="<?php echo($day); ?>"><?php echo($day); ?></option>
									<?php } ?>
								</select>日
							</div>

                                                            
                                                        <?php if(empty($set_comment)) { 
                                                                wp_nonce_field('members-form'); 
                                                        }else{ ?>
                                                                <input type="hidden" id="" name= "_wpnonce" value="<?php echo $set_comment['_wpnonce']; ?>" />
                                                                <?php wp_referer_field(true); ?>
                                                        <?php } ?>
							<input type="text" name="action" style="display:none;" value="submit_member_form"/>
                                                        <div class="actions" >                                                                
                                                            <input type="submit" style="float:left;" value="次へ" tabindex="5" id="submit" class="next-button2" name="submit">
                                                            <button type="button"  style="float:left;margin-left: 20px;" class="next-button2" onclick="location.href='<?php echo get_permalink($listing_page_id); ?>'" name="back">一覧に戻る</button>
                                                        </div>
							<div class="msg"></div>
						</form>
					</div>
				</div>
			</div>				
	</div>

    <style type="text/css">
		span.hovererror {
			display: block;
			font-size: 13px;
			color: #F00;
		}
	</style>
	<script type="text/javascript">
               function mydelete_attach_file(post_id,nonce){
                    $.ajax({
                        url:'<?php the_permalink(); ?>',
                        type:'POST',
                        data :  { 'html-upload' : 'Delete',  'attach_file_id': post_id, '_wpnonce' :  nonce , '_wp_http_referer': '<?php the_permalink(); ?>' },
                        beforeSend : 
                        function() {  
                            var idname = '#attach-file'+ post_id;
                            $(idname).empty(); 
                            $(idname).remove();
                        },
                       success : function(response){
                           if(console){ console.log(response);}
                        },
                        complete:function(){},
                        error:function(){}
                    });
                }
            $(function(){
                var nonce_value = $('#_wpnonce').val();

            $('#upload-attachment').fineUploader({
                request: { endpoint : '<?php the_permalink(); ?>', params: {'html-upload':1 , '_wpnonce' :  nonce_value , '_wp_http_referer': '<?php the_permalink(); ?>'}, paramsInBody: true},
		debug: true,
		multiple: false,
                validation: { allowedExtensions: ['jpg','jpeg','png','gif','doc','docx','xls','xlsx','ppt','pptx','pdf','txt'], 
                        sizeLimit: 5242880 },              
                
                text: {
                    uploadButton: '添付ファイルを選択してください',
                    cancelButton: 'キャンセル',
                    retryButton: '再試行',
                    failUpload: 'アップロードに失敗しました',
                    dragZone: 'アップロードするファイルをドラッグしてください',
                    dropProcessing: '選択したファイルを処理中',
                    formatProgress: "{percent}% 処理中 ファイルサイズ{total_size}",
                    waitingForResponse: "アップロード中です。"
                },              
                messages: {
                    typeError: "不正な拡張子です。{extensions}のファイルのみ添付できます。",
                    sizeError: "ファイルサイズが大きすぎます。最大{sizeLimit}.",
                    minSizeError: "{file} is too small, minimum file size is {minSizeLimit}.",
                    emptyError: "空のファイルのようです。ファイルを選択しなおしてください。",
                    noFilesError: "ファイルのアップロードに失敗しました。",
                    onLeave: "ファイルを添付中です。ページを閉じた場合、処理がキャンセルされます。"
                },
                callbacks :{
                    onComplete: function(id, filename, response){
                        if(response.success){
                            $('.qq-upload-success').remove();
                            $file_ele = '<li id="attach-file'+response.post_id+'"><input type="hidden" name="attach-list[]" value="' + response.post_id + '"/>' + response.file_name + '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type="button" onclick="mydelete_attach_file('+ response.post_id +',\' '+ $('#_wpnonce').val() +'\')">削除</button></li>';
                            if($('#attach-list').find("li").length > 0){
                                $('#attach-list').append($file_ele);                                
                            }else{
                                $('#attach-list').html($file_ele);                                                                
                            }                        
                        }                    
                    }
                }
                });
        
                jQuery('#submit-members-form').submit(function(){
			var error = false;
                       jQuery('#submit-members-form .required').each(function(){
				jQuery(this).parent().find('.error').remove();
				if(jQuery(this).attr('placeholder') == jQuery(this).val() || $.trim(jQuery(this).val()) == ''){
					var labelText = jQuery(this).data('lable');
					jQuery(this).prev().append('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="error hovererror '+labelText+'Error">'+labelText+'は必須です</span>');
					jQuery('.error').mouseover(function() {
						jQuery(this).fadeOut('fast');
					});
					error = true;
				}
				if(!error){
					if(jQuery(this).attr('name') == 'aemail') {
						var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
						if(!emailReg.test(jQuery.trim(jQuery(this).val()))) {
							var labelText = jQuery(this).data('lable');
							jQuery(this).parent().append('<span class="error hovererror">正しいEメールアドレスを入力してください</span>');
							jQuery('.error').mouseover(function() {
								jQuery(this).remove();
							});
							error = true;
						}
					}
				}
			});
                        if(!error){
                            $('#upload-attachment').remove();
                        }
                        return !error;

                    });
            });
	</script>
</div>      
<?php
    $content = ob_get_contents ();
    ob_end_clean ();
    return $content;
}
// call from SHOW_COMMENT_FORM
function add_comment_members_form($atts) {

   extract(shortcode_atts(array(
        'listing_page_id' => 0,
    ), $atts));
    $errors = false;
    
    // confirm
    if(isset($_REQUEST['_wpnonce'])){
        
        $val = get_transient($_REQUEST['_wpnonce']);

        if(!empty($val)){
            if(isset($_REQUEST['error'])){                
                $error_no = intval($_REQUEST['error']);
                if($error_no < 0){
                    $val = array(array(),array("通信エラーが発生しました。"));
                }
                // error
                return create_comment_members_form($listing_page_id,$val);
            }else{
                return confirm_comment_members_form($val[1],$_REQUEST['_wpnonce']);                
            }
        }
    }else if(isset($_REQUEST['members_post_id'])) {
        $refer_id = $_REQUEST['members_post_id'];
        // complete
        return complete_comment_members_form($refer_id,$listing_page_id);
    }
 
    // submit form
    return create_comment_members_form($listing_page_id,'');
}

add_shortcode ( COMMENT_MANAGER_SHORTCODE, 'add_comment_members_form' );