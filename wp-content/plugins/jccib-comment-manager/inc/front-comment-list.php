<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
function add_listing_comment_members_form($atts){
    

    extract(shortcode_atts(array(
        'agreement_page_id' => 0,
        'details_page_id' => 0,
    ), $atts));


                    global $wpdb;
                    $rows_per_page = 10;

                    $expire = date('Y-m-d');
                    $table_name = $wpdb->prefix . "contact_form";
                    $retrieve_data = $wpdb->get_results( "SELECT * FROM $table_name WHERE comnt_status = 'Y' AND date_expire >= '$expire' ORDER BY date_added ASC" );
                    global $wp_rewrite;

                    $current = (intval(get_query_var('paged'))) ? intval(get_query_var('paged')) : 1;
                    if( $wp_rewrite->using_permalinks() )
                    $pagination_args['base'] = user_trailingslashit( trailingslashit( remove_query_arg('s',get_pagenum_link(1) ) ) . 'page/%#%/', 'paged');

                    if( !empty($wp_query->query_vars['s']) )
                    $pagination_args['add_args'] = array('s'=>get_query_var('s'));

                    $start = ($current - 1) * $rows_per_page;
                    $end = $start + $rows_per_page;
                    $end = (sizeof($retrieve_data) < $end) ? sizeof($retrieve_data) : $end;

                        $pagination_args = array(
                            'base' => @add_query_arg('paged','%#%'),
                            'format' => '',
                            'total' => ceil(sizeof($retrieve_data)/$rows_per_page),
                            'current' => $current,
                            'show_all' => false,
                            'type' => 'plain',
                        );
                            ob_start ();
                        ?>
                        <div style="float:right;">
                            <button type="button"  class="next-button2" onclick="location.href='<?php echo get_permalink($agreement_page_id); ?>'">新規投稿</button>
                        </div>
                        <div class="comment-details">

                            <table class="table table-bordered">
                            <col width="25%"></col>
                                <col width="50%"></col>
                            <col width="25%"></col>
                            <tr>
                                <th style="text-align:left;">会社名</th>                                
                                <th style="text-align:left;">題名</th>
                                <th style="text-align:left;">投稿日</th>
                            </tr>
                                <?php
                                    $start = ($current - 1) * $rows_per_page;
                                    $end = $start + $rows_per_page;
                                    $end = (sizeof($retrieve_data) < $end) ? sizeof($retrieve_data) : $end;
                                    for ($i=$start;$i < $end ;++$i ) {
                                    $retrieved_data = $retrieve_data[$i];?>
                           <tr>
                               <td style="text-align:left;">
                                   <?php echo $retrieved_data->name; ?>
                               </td>
                                  <td style="text-align:left;">

                                     <a href="<?php echo get_permalink($details_page_id); ?>?members-form-id=<?php echo $retrieved_data->id; ?>"><?php echo $retrieved_data->subject;?></a>

                                 </td>
                                <td><?php $tobj = new DateTime($retrieved_data->date_added); echo $tobj->format('Y年m月d日');?></td>
                            </tr> 
                        <?php }?>
                             
                        </table>
                        <?php echo paginate_links($pagination_args);?>
                        </div>
<?php
    $content = ob_get_contents ();
    ob_end_clean ();
    return $content;
}
add_shortcode ( COMMENT_MANAGER_LISTING_SHORTCODE, 'add_listing_comment_members_form' );

function add_agreement_comment_members($atts){
     extract(shortcode_atts(array(
        'submit_form_page_id' => 0,
        'listing_page_id' => 0,
    ), $atts));
    $settings = get_option('enocp_user_settings');
    ob_start ();
    echo wpautop($settings['agreement']);
?>
   <div class="actions" >                                                                
       <button type="button" style="float:left;" class="next-button2" onclick="location.href='<?php echo get_permalink($submit_form_page_id); ?>'">同意する</button>
        <button type="button"  style="float:left;margin-left: 20px;" class="next-button2" onclick="location.href='<?php echo get_permalink($listing_page_id); ?>'">戻る</button>
    </div>
<?php
    $content = ob_get_contents ();
    ob_end_clean ();
    return $content;
}
add_shortcode (COMMENT_MANAGER_AGREEMENT_SHORTCODE, 'add_agreement_comment_members');                    