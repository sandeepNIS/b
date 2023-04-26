<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function tt_render_details_page(){
    	global $wpdb;
    	$query = $wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'contact_form WHERE id='.$_GET['enquiry'] );
        if(empty($query)){
                        wp_redirect(admin_url('admin.php').'?page=tt_enquiry_test&orderby=id&order=desc');
        }
        ob_start();
?>
    <div class="wrap">
    	<div id="icon-users" class="icon32">
    		<br />
    	</div>
    	<h2>投稿の詳細 <?php echo $_GET['enquiry']; ?></h2>
        
        <button action="action" type="button" onclick="history.go(-1);" class="button button-primary"  style="float:left;">戻る</button>
        <br/><br/><br/>
        <table class="widefat fixed" cellspacing="0">
    		<tbody>
                        <tr>
                            <td class="column-columnname" scope="row">状態</td>
                            <td class="column-columnname"><?php echo jccib_get_comment_status_msg($query->comnt_status); ?>
                            </td>
                        </tr>
                    <tr class="alternate">
                    <td class="column-columnname"  style="width:100px;" scope="row">掲載期日</td>
                    <td class="column-columnname"><?php echo (new DateTime($query->date_expire))->format('Y年m月d日');
                    if($query->date_expire < date('Y-m-d')){
                        echo '(期限切れ)';
                    }
                    ?></td>
                        </tr> 
                      <?php if($query->comnt_status == 'R' && isset($query->reject_reason)) { ?>
                            <tr class="alternate">
                                <td class="column-columnname" scope="row">棄却理由</td>
                                <td class="column-columnname"><?php echo wpautop($query->reject_reason); ?></td>
                            </tr>
                       <?php } ?>
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
    			    <td class="column-columnname"><?php echo empty($query->mobile)?'未記入':$query->mobile; ?></td>
    			</tr>
    			<tr>
                            <td class="column-columnname"  style="width:150px;" scope="row">題名</td>
                            <td class="column-columnname"><?php echo $query->subject; ?></td>
                        </tr>
    			<tr class="alternate">
    				<td class="column-columnname" scope="row">本文</td>
                                <td class="column-columnname"><?php echo wpautop($query->message); ?></td>
    			</tr>
    			<tr class="alternate">
    				<td class="column-columnname" scope="row">添付ファイル</td>
    				<td class="column-columnname">
                                    <div>
                                    <?php if(empty($query->file_upload)){ ?>
                                        なし
                                    <?php } else { $attach = unserialize($query->file_upload);  
                                        foreach($attach as $post_id) { $post_data = get_post($post_id); ?>
                                        <div>
                                        <a href="<?php echo $post_data->guid; ?>"><?php echo esc_html( wp_basename( $post_data->guid ) ); ?></a>
                                        </div>
                                    <?php }} ?>                                </td>
    			</tr>


    		<tr class="alternate">
    			<td class="column-columnname"  style="width:100px;" scope="row">投稿日時</td>
    			<td class="column-columnname"><?php echo (new DateTime($query->date_added))->format('Y年m月d日 H時i分'); ?></td>
    		</tr>
    		</tbody>
    	</table>
    </div>
<?php
    $content = ob_get_contents ();
    ob_end_clean ();
    echo $content;
}
