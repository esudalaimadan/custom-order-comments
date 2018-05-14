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
           add_action( 'add_meta_boxes', array( $this, 'add_featured' ) );
           add_action( 'edit_comment', array( $this, 'save_featured' ) );
           add_filter( 'manage_edit-comments_columns', array( $this, 'add_featured_column' ) );
           add_action( 'manage_comments_custom_column', array( $this, 'display_featured_column' ), 10, 2 );
           add_action( 'pre_get_comments', array( $this, 'prepare_comments_query' ) );
        }
        
        /* Adds featured meta box */
        public function add_featured()
        {
            add_meta_box(
                'featured_box', 
                __( 'Position in List of Comments '), 
                array( $this, 'add_featured_box_content' ), 
                'comment',
                'normal',
                'high'
                );
        }

        public function add_featured_box_content( $comment ){
            wp_nonce_field( basename( __FILE__ ), 'featured_nonce');
            $featured = get_comment_meta( $comment->comment_ID, 'featured', true );            
            echo '<label>' . __( 'Please check for featured comment.' ) . '</label>';
            ?>
            <p><input type="checkbox" name="featured" value="yes" <?php if ( $featured == 'yes' ) echo 'checked="checked"'; ?> /></p>
            <?php
        }        

        public function save_featured( $comment_id ){
            if ( ! isset( $_POST['featured_nonce']) || ! wp_verify_nonce( $_POST['featured_nonce'], basename( __FILE__ ) ) ){
                return;
            } 
            if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ){
                return;
            }
            if ( ! current_user_can( 'edit_comment', $comment_id ) ){
                return;
            }
            if ( isset( $_POST['featured'] ) ){
                update_comment_meta( $comment_id, 'featured', $_POST['featured'] );
            }else{
                delete_comment_meta( $comment_id, 'featured');
            }
        }
        /* Adds featured column in admin end */
        public function add_featured_column( $columns ){
            $columns['featured'] = __( 'Featured' );
            return $columns;
        }

        public function display_featured_column( $column, $comment_id ){
            if( $column == 'featured' ){
                echo '<p>' . get_comment_meta( $comment_id, 'featured', true ) . '</p>';
            }
        }
        
        /* Query to display both featured as well as non-featured comments */
        public function prepare_comments_query( $query ) {            
            $query->query_vars['meta_query'] = array(
                'relation' => 'OR',
                array(
                    'key' => 'featured',
                    'value' => 'yes',
                    'compare' => '='
                     ),
                array(
                    'key' => 'featured',
                    'compare' => 'NOT EXISTS'
                    )
                );                
            $query->query_vars['orderby'] = array('meta_value' => 'DESC');            
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