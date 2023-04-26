<?php

// function to add default value on plugin activation
function enocp_template_add_default(){
	add_option('enocp_email_template',array());
}


//Function for email notification seeting
function tt_render_template_page(){

    if(isset($_POST['submit'])){
        if($_POST['reason_template']){
            $reasons = array();
            foreach($_POST['reason_template'] as $input){
                if(!empty(trim($input))){
                array_push($reasons,implode( "\n", array_map( 'sanitize_text_field', explode( "\n", $input ) )));                        
                }
            }
            update_option('enocp_email_template',$reasons);
            wp_safe_redirect('?page=reject_reason_template');
        }
    }
    $reasons = get_option('enocp_email_template');
    ob_start ();
?>

<div class="wrap">
		<div class="icon32" id="icon-options-general"><br></div>
		<h2>理由のテンプレート</h2>

		<form action="" method="POST">
			<table class="form-table">
                                <?php foreach($reasons as $value){ ?>
				<tr>
                                        <td >
                                            <textarea class="admin-jccib-input" name="reason_template[]"><?php echo $value; ?></textarea>
                                        </td>
				</tr>                                
                                <?php }?>
				<tr>
                                        <td style="width:300px;">
                                            <textarea class="admin-jccib-input" style="width:500px;" name="reason_template[]"></textarea>
                                        </td>
				</tr>
				<tr valign="top">
					<th scope="row"><input type="submit" value="更新" class="button button-primary" id="submit" name="submit"></th>
					<td></td>
				</tr>
			</table>
		</form>
	</div>
<?php
    $content = ob_get_contents ();
    ob_end_clean ();
    echo $content;
}
 