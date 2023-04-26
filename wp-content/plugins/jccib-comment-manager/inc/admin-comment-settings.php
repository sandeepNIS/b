<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


function enocp_settings_add_default(){
    add_option('enocp_user_settings',array());
}

function tt_render_settings_page(){
    $settings = get_option('enocp_user_settings');

?>
<div class="wrap">
    <div class="icon32" id="icon-options-general"><br></div>
    <h2>会員投稿設定画面</h2>
    <form action="" method="POST">
            <?php  wp_nonce_field('jccib','admin-comment-settings'); ?>
            <input type="hidden" name="page" value="<?php echo $_REQUEST[page]; ?>"/>
            <table class="form-table">
                    <tr>
                        <th scope="row" class="column-columnname" >通知先メール</th>
                        <td><input class="admin-jccib-input" type="text" name="notification-email" value="<?php echo empty($settings['notification-email'])?'':esc_attr(implode(";",$settings['notification-email']));?>"/></td>
                    </tr>
                    <tr>
                        <th scope="row" class="column-columnname" >承認メール題名</th>
                        <td><input class="admin-jccib-input" type="text" name="approve-subject" value="<?php echo $settings['approve-subject'];?>"/></td>
                    </tr>
                    <tr>
                        <th scope="row" class="column-columnname" >承認メール本文</th>
                        <td><textarea  class="admin-jccib-input" name="approve-body" ><?php echo $settings['approve-body']; ?></textarea></td>
                    </tr>
                    <tr>
                        <th scope="row" class="column-columnname" >承認取り消しメール題名</th>
                        <td><input class="admin-jccib-input" type="text" name="disapprove-subject" value="<?php echo $settings['disapprove-subject'];?>"/></td>
                    </tr>
                    <tr>
                        <th scope="row" class="column-columnname" >承認取り消しメール本文</th>
                        <td><textarea class="admin-jccib-input" name="disapprove-body" ><?php echo $settings['disapprove-body'];?></textarea></td>
                    </tr>
                    <tr>
                        <th scope="row" class="column-columnname" >棄却メール題名</th>
                        <td><input class="admin-jccib-input" type="text" name="reject-subject" value="<?php echo $settings['reject-subject'];?>"/></td>
                    </tr>
                    <tr>
                        <th scope="row" class="column-columnname" >棄却メール本文</th>
                        <td><textarea style="height:80px;"class="admin-jccib-input" name="reject-body" ><?php echo $settings['reject-body'];?></textarea></td>
                    </tr>                    
                    <tr>
                        <th scope="row" class="column-columnname" >利用規約</th>
                        <td><?php wp_editor( stripslashes($settings['agreement']), 'agreement', $settings = array() ); ?></td>
                    </tr>
                    <tr>
                        <th scope="row"><input type="submit" value="更新" class="button button-primary" id="submit" name="submit"></th>
                        <td></td>
                    </tr>
            </table>
    </form>
</div>
<?php
}

function tt_admin_comment_settings_notice(){
    
?>
    <?php if($messages = get_transient('tt-admin-comment-settings-error')) : ?>
<div class="error">
    <ul>
        <?php foreach($messages as $message): ?>
        <li><?php echo esc_html($message); ?></li>
        <?php endforeach; ?>
    </ul>
</div>
    <?php endif; ?>
<?php    
}
add_action('admin_notices','tt_admin_comment_settings_notice');

function tt_admin_comment_settings(){
    if(isset($_POST) && isset($_POST['page']) && check_admin_referer('jccib','admin-comment-settings')){
        if($_POST['page'] == 'admin-comment-settins'){

            $e = new WP_Error();
            $settings = get_option('enocp_user_settings');
     

            $mails = explode(';', trim($_POST['notification-email']));
            if(empty($mails)){
                $e->add('error', '通知先メールを入力してください');
            }else{
                foreach($mails as $mail){
                    if(!is_email($mail)){
                        $e->add('error', '正しいEメールアドレスを入力してください');
                    }
                }
                if(empty($e->get_error_messages())){
                    $settings['notification-email'] = $mails;
                }
            }

            
            if(empty(trim($_POST['approve-subject']))){
                $e->add('error', '承認メールの題名を入力してください');
            }else{
                $settings['approve-subject'] = stripslashes(wp_filter_post_kses(addslashes($_POST['approve-subject'])));                
            }
            
            if(empty(trim($_POST['approve-body']))){
                $e->add('error', '承認メールの本文を入力してください');
            }else{
                $settings['approve-body'] = stripslashes(wp_filter_post_kses(addslashes($_POST['approve-body'])));                
            }
            
            if(empty(trim($_POST['disapprove-subject']))){
                $e->add('error', '承認取り消しメールの題名を入力してください');
            }else{
                $settings['disapprove-subject'] = stripslashes(wp_filter_post_kses(addslashes($_POST['disapprove-subject'])));                
            }

            if(empty(trim($_POST['disapprove-body']))){
                $e->add('error', '承認取り消しメールの本文を入力してください');
            }else{
                $settings['disapprove-body'] = stripslashes(wp_filter_post_kses(addslashes($_POST['disapprove-body'])));                
            }
            
            if(empty(trim($_POST['reject-subject']))){
                $e->add('error', '棄却メールの題名を入力してください');
            }else{
                $settings['reject-subject'] = stripslashes(wp_filter_post_kses(addslashes($_POST['reject-subject'])));                
            }
            
            if(empty(trim($_POST['reject-body']))){
                $e->add('error', '棄却メールの題名を入力してください');
            }else{
                $settings['reject-body'] = stripslashes(wp_filter_post_kses(addslashes($_POST['reject-body'])));                
            }
            
            if(empty(trim($_POST['agreement']))){
                $e->add('error', '利用規約を入力してください');
            }else{
                $settings['agreement'] = stripslashes(wp_filter_post_kses(addslashes($_POST['agreement'])));                
            }
            
            if(empty($e->get_error_messages())){
                delete_transient('tt-admin-comment-settings-error');
                update_option('enocp_user_settings',$settings); 
            }else{
               set_transient('tt-admin-comment-settings-error', $e->get_error_messages(),10);                

            }            
        }
    }
}
add_action('admin_init','tt_admin_comment_settings');
