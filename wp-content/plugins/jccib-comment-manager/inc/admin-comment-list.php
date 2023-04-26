<?php

// Right now. I don't have enough time to finish reject mail template.
//require_once COMMENT_MANAGER_PLUGIN_DIR."/inc/admin-reject-template-settings.php";    

if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class TT_Example_List_Table extends WP_List_Table {
    
    function __construct(){
        global $status, $page;
                
        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'enquiry',     //singular name of the listed records
            'plural'    => 'enquiries',    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ) );
        
    }
    
    function column_default($item, $column_name){
        switch($column_name){
            case 'number':
                return $item['id'];
            case 'name':
            case 'email':
            case 'message':
	    case 'subject':
            case 'file_upload':
            case 'comnt_status':
            case 'date_expire':
            case 'date_added':           
                return $item[$column_name];
            default:
                return print_r($item,true); //Show the whole array for troubleshooting purposes
        }
    }
    
    function column_name($item){
         //Build row actions
        if(isset($_GET['category']) && $_GET['category'] == 'D'){
            $actions = array(
            'view'      => sprintf('<a href="?page=%s&action=%s&enquiry=%s&trashbox=1">詳細</a>',$_REQUEST['page'],'view',$item['id']),
            'recover'   => sprintf('<a href="?page=%s&action=%s&enquiry=%s&trashbox=1">元に戻す</a>',$_REQUEST['page'],'recover',$item['id'])
            );                        
        }else{
            $temp_var =$item['comnt_status'];
            if($item['date_expire']<date('Y-m-d')){
                $expired = 1;
            }else{
                $expired = 0;                
            }
            $actions = array();
            $actions['view'] =     sprintf('<a href="?page=%s&action=%s&enquiry=%s">詳細</a>',$_REQUEST['page'],'view',$item['id']);
            if($temp_var != 'R'){                
                if($temp_var == 'Y'){
                    $actions['disapproveit'] = sprintf('<a href="?page=%s&action=%s&enquiry=%s">承認取り消し</a>',$_REQUEST['page'],'disapproveit',$item['id']);
                }else{
                    $actions['approveit'] =  '<a href="#" onclick="confirm_go('.sprintf("'page=%s&action=%s&enquiry=%s'",$_REQUEST['page'],'approveit',$item['id']).');">承認する</a>';
                }
                if($temp_var == 'N'){
                $actions['reject'] =  sprintf('<a href="?page=%s&action=%s&enquiry=%s">棄却</a>',$_REQUEST['page'],'reject',$item['id']);                    
                }

            }
            $actions['delete']    = '<a href="#" onclick="confirm_delete('.sprintf("'page=%s&action=%s&enquiry=%s'",$_REQUEST['page'],'delete',$item['id']).','.$expired.');" >削除</a>';
        }


        //Return the title contents
        //return sprintf('%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
        return sprintf('%1$s %3$s',
            /*$1%s*/ $item['name'],
            /*$2%s*/ $item['id'],
            /*$3%s*/ $this->row_actions($actions)
        );
    }
    
    function column_number($item){
    	return $item['id'];
    }

    function column_comnt_status($item){
        $show =  jccib_get_comment_status_msg($item['comnt_status']);
        return $show.'<br/>'.jccib_get_expire_msg($item['date_expire']);
    }

     function column_file_upload($item){
        if(empty($item['file_upload'])){
                    return 'なし';
        }else{
                   return 'あり';
        }        
    }

    
    function column_date_added($item){
    	return date('Y年m月d日 H時i分',strtotime($item['date_added']));
    }
    function column_date_expire($item){
    	return date('Y年m月d日',strtotime($item['date_expire']));
    }

    function get_columns(){
        $columns = array(
        	'number'   => '投稿番号',
                'name'     => '会社名',
                'email'    => 'Eメール',
                'message'  => '本文',
        	'subject'  => '題名',
                'file_upload'  => '添付',
                'comnt_status'  => '状態',
                'date_expire'  => '掲載期日',                		  
                'date_added' => '投稿日'
        );
        return $columns;
    }
    
    function get_sortable_columns() {
        $sortable_columns = array(
        	'number' => array('number',true),
                'name'     => array('name',false),     //true means it's already sorted
                'email'    => array('email',false),
                'message' => array('message', false),
            	'subject' => array('subject',false),
                'file_upload' => array('file_upload', false),
                'comnt_status' => array('comnt_status', false),
                'date_expire' => array('date_expire',false),                
                'date_added' =>array('date_added', false)		  
        );
        return $sortable_columns;
    }
    
    function get_bulk_actions() {
        $actions = array();
        return $actions;
    }
        
    function process_bulk_action() {
       
        if('approveit'===$this->current_action() && isset($_GET['enquiry'])){
            $this->approve_this_enquery($_GET['enquiry']);
        }
        //Detect when a bulk action is being triggered...
       else  if( 'delete'===$this->current_action() && isset($_GET['enquiry'])) {
            $this->delete_this_enquiry($_GET['enquiry']);
        }
        else if('recover'===$this->current_action() && isset($_GET['enquiry'])){
            $this->recover_this_enquery($_GET['enquiry']);
        }
        else{
            return;
        }
        wp_safe_redirect(menu_page_url('tt_enquiry_test',false));
    }
    
    function recover_this_enquery($id){
        global $wpdb;
        return jccib_change_comment_status($wpdb,$id,'N');
    }
        
    function approve_this_enquery($id){
        global $wpdb;
        jccib_change_comment_status($wpdb,$id,'Y');
    	return notify_approve_jimukyoku ($id);
    }

    function delete_this_enquiry($id) {
        global $wpdb;
        return jccib_change_comment_status($wpdb,$id,'D');
    }
    
    function prepare_items() {
        global $wpdb;

        $per_page = 20;
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->process_bulk_action();
        $expire = date('Y-m-d');                    
        if(trim($_GET['category'])){
            $cat = trim($_GET['category']);
            switch($cat){
                default:
                $sql = 'SELECT * from '.$wpdb->prefix.'contact_form where comnt_status = "'.$cat.'"  ORDER BY id desc';
                    break;
                case 'expired':
                $sql = 'SELECT * from '.$wpdb->prefix.'contact_form where date_expire < "'.$expire.'"  ORDER BY id desc';
                    break;
            }
        }else{
        $sql = 'SELECT * from '.$wpdb->prefix.'contact_form where (comnt_status = "Y" or comnt_status = "N") AND date_expire >= '.$expire.' ORDER BY id desc';
        }

           $querydata = $wpdb->get_results($sql);
           $data = array();
           foreach ($querydata as $querydatum ) {
               array_push($data, (array)$querydatum);                
           }

        function usort_reorder($a,$b){
            $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'id'; //If no sort, default to title
            $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'asc'; //If no order, default to asc
            $result = strcmp($a[$orderby], $b[$orderby]); //Determine sort order
            return ($order==='asc') ? -$result : $result; //Send final sort direction to usort
        }
        $current_page = $this->get_pagenum();
        $total_items = count($data);
        $data = array_slice($data,(($current_page-1)*$per_page),$per_page);
        $this->items = $data;
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
        ) );
    }
}


    
    
function notify_approve_jimukyoku($id)
{
    global $wpdb;
    $query = $wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'contact_form WHERE id='.$id );
    $settings = get_option('enocp_user_settings');
    $subject = sprintf($settings['approve-subject'],$id);
    $body = sprintf($settings['approve-body'],$id,$query->subject);

    $revs = $settings['notification-email'];
    array_push($revs,$query->email);
    
    add_filter ( 'wp_mail_content_type', 'set_html_content_type' );
    $wpmail = wp_mail ( $revs, $subject, $body );
    remove_filter ( 'wp_mail_content_type', 'set_html_content_type' );
    return $wpmail;
}

function jccib_get_expire_msg($date_expire){
    if($date_expire < date('Y-m-d')){
        return '(期限切れ)';
    }else{
        return '';
    }
}

function jccib_get_comment_status_msg($status){
    switch($status){
        case 'N':
            return '未承認';

        case 'Y':
           return '承認済み';

        case 'D':
            return '削除済み';
         case 'R':
            return '棄却';
        case 'expired':
            return '期限切れ';
        }    
}

function jccib_change_comment_status($wpdb,$msg_id,$status,$reason='')
{
    if($status == 'R'){
        return $wpdb->query( $wpdb->prepare('UPDATE '. $wpdb->prefix.'contact_form SET reject_reason=%s,comnt_status=%s WHERE id = %d',$reason,$status,$msg_id));
    }else{
        return $wpdb->query('UPDATE '. $wpdb->prefix.'contact_form SET comnt_status="'.$status.'" WHERE id = '.$msg_id);               
    }
}

function get_tt_selected_category($key)
{
    if(empty($key) && !isset($_GET['category'])){
        return 'font-weight:bold;color:black;';
    }else{
        if($_GET['category'] == $key){
        return 'font-weight:bold;color:black;';            
        }
    }
    return 'font-weight:normal;';
}

function tt_render_list_page(){
    
    if(isset($_GET['action']) && $_GET['action'] == 'view' && isset($_GET['enquiry']) && $_GET['enquiry']){
        echo tt_render_details_page();
    } elseif(isset($_GET['action']) && ($_GET['action'] == 'reject' || $_GET['action'] == 'disapproveit') && isset($_GET['enquiry']) && $_GET['enquiry']){ 
        echo tt_render_reject_page();
    }else{ 
// list of comment     
    //Create an instance of our package class...
    $testListTable = new TT_Example_List_Table();
    //Fetch, prepare, sort, and filter our data...
    $testListTable->prepare_items();
?>
<div class="wrap">

    <div id="icon-users" class="icon32">
    		<br />
    </div>
    <h2>投稿一覧      <?php echo jccib_get_comment_status_msg(trim($_GET['category']));?></h2>
    	<!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
        | <a style="<?php echo get_tt_selected_category(''); ?>" href="?page=<?php echo $_REQUEST['page']; ?>">すべて(棄却および削除済みを除く)</a> | 
        <a style="<?php echo get_tt_selected_category('N'); ?>" href="?page=<?php echo $_REQUEST['page']; ?>&category=N">未承認</a> | 
        <a style="<?php echo get_tt_selected_category('Y');?>;" href="?page=<?php echo $_REQUEST['page']; ?>&category=Y">承認済み</a> | 
        <a style="<?php echo get_tt_selected_category('expired'); ?>;" href="?page=<?php echo $_REQUEST['page']; ?>&category=expired">期限切れ</a> | 
        <a style="<?php echo get_tt_selected_category('R'); ?>;" href="?page=<?php echo $_REQUEST['page']; ?>&category=R">棄却</a> |
        <a style="<?php echo get_tt_selected_category('D'); ?>;" href="?page=<?php echo $_REQUEST['page']; ?>&category=D">削除済み</a> |
    <form id="enquiries-filter" method="get">
    		<!-- For plugins, we also need to ensure that the form posts back to our current page -->
    		<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
    		<!-- Now we can render the completed list table -->
            <?php $testListTable->display(); ?>
     </form>
</div>
                <script type="text/javascript">
                    function confirm_delete(link,expired){
                        if(expired){
                            location.href='?'+link;
                            return;
                        }
                        if (!confirm('この投稿は期限切れになっていませんが、削除しますか？')) {
                            return;
                        }
                        location.href='?'+link;                        
                    }
                    function confirm_go(link){
                        var params = {};
                        var paramList = link.split("&")
                        for(var i = 0 ; i < paramList.length; i++){
                            var pair = paramList[i].split("=");
                            params[pair[0]] = pair[1];
                        }
                        if(params['action'] == 'approveit'){
                            var msg = '投稿を承認します。';
                        }else{
                            return;
                        }
                        if (confirm(msg+'\nよろしいですか？')) {
                            location.href='?'+link;
                        }
                        return false;

                    }
                </script>
<?php
  }
}
