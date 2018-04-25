<?php
/*
Plugin Name: WordPress Custom Order Comments
Plugin URI: 
Description: This Plugin assists admin in ordering comments according to requirements.  By default WordPress allows only ascending and descending order of comments by publishing date.
Author: Ezhilarasi
Version: 1.0
License: GPL2
*/
if(!class_exists('Custom_Order_Comments'))
{
    class Custom_Order_Comments
    {
        /* Called when Custom_Order_Comments class is instantiated */
        public function __construct()
        {
           add_action( 'add_meta_boxes', array( $this, 'add_priority' ) );
           add_action( 'edit_comment', array( $this, 'save_priority' ) );
           add_filter( 'manage_edit-comments_columns', array( $this, 'add_priority_column' ) );
           add_action( 'manage_comments_custom_column', array( $this, 'display_priority_column' ), 10, 2 );
           add_action( 'pre_get_comments', array( $this, 'prepare_comments_query' ) );
        }
        
        /* Adds Priority meta box */
        public function add_priority()
        {
            add_meta_box(
                'priority_box', 
                __( 'Position in List of Comments '), 
                array( $this, 'add_priority_box_content' ), 
                'comment',
                'normal'
                );
        }

        public function add_priority_box_content( $comment ){
            wp_nonce_field( basename( __FILE__ ), 'priority_nonce');
            $current_pos = get_comment_meta( $comment->comment_ID, 'priority', true );            
            echo '<p>' . __( 'Enter the Position at which you would like the comment to appear.  For example, "1" will appear first and "2" will appear second, and so forth.' ) . '</p>';
            ?>
            <p><input type="number" name="priority" value="<?php echo $current_pos; ?>" /></p>
            <?php
        }        

        public function save_priority( $comment_id ){
            if ( ! isset( $_POST['priority_nonce']) || ! wp_verify_nonce( $_POST['priority_nonce'], basename( __FILE__ ) ) ){
                return;
            } 
            if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ){
                return;
            }
            if ( ! current_user_can( 'edit_comment', $comment_id ) ){
                return;
            }
            if ( isset( $_POST['priority'] ) ){
                update_comment_meta( $comment_id, 'priority', $_POST['priority'] );
            }
        }
        /* Adds Priority column in admin end */
        public function add_priority_column( $columns ){
            $columns['priority'] = __( 'Priority' );
            return $columns;
        }

        public function display_priority_column( $column, $comment_id ){
            if( $column == 'priority' ){
                echo '<p>' . get_comment_meta( $comment_id, 'priority', true ) . '</p>';
            }
        }
        
        /* TODO Not sure whether pre_get_comments hook works */
        public function prepare_comments_query($query){
            $query->set( 'order_by', 'meta_value' );
            $query->set( 'meta_key', 'priority' );
            $query->set( 'order', 'ASC' );
            return $query;
        }

        public static function activate()
        {
            //Do Something
        }

        public static function deactivate()
        {
            //Do Something
        }
    }
}
if(class_exists('Custom_Order_Comments'))
{
    //Activation and deactivation hooks
    register_activation_hook( __FILE__, array('Custom_Order_Comments', 'activate'));
    register_deactivation_hook( __FILE__, array('Custom_Order_Comments', 'deactivate'));
    //Instantiate the class
    $plugin = new Custom_Order_Comments();
}
?>