<?php
/*
Plugin Name: Bulk Edit Categories for media
Plugin URI: http://www.cchnageinc.com
Description: custom bulk action for categories for media
Author: Tomas Bulva - based on work of Justin Stern
Author URI: http://www.cchnageinc.com
Version: 0.1

    Copyright: © 2013 Tomas Bulva (tbulva@cchnageinc.com)
    License: GNU General Public License v3.0
    License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

if (!class_exists('CFM_Custom_Bulk_Action')) {
 
    class CFM_Custom_Bulk_Action {
        
        public function __construct() {
            
            $plugindir_node 						= dirname(plugin_basename(__FILE__));	
    		//$this->plugindir_url 					= get_bloginfo('wpurl') . "/wp-content/plugins/". $plugindir_node;
    		$plugindir_url 					= WP_CONTENT_URL . "/plugins/". $plugindir_node;
            
            if(is_admin()) {
                // admin actions/filters
                add_action('admin_footer-edit.php', array(&$this, 'custom_bulk_admin_footer'));
                add_action('load-edit.php',         array(&$this, 'custom_bulk_action'));
                add_action('admin_notices',         array(&$this, 'custom_bulk_admin_notices'));
                
                wp_enqueue_style( 'mediatags-stylesheet', $plugindir_url .'/css/mediatags_style_admin.css', false, 0.1);
            
                wp_enqueue_script('jquery'); 
            	wp_enqueue_script('jquery-ui-core'); 
            	wp_enqueue_script('jquery-ui-dialog');
            	wp_enqueue_style('mediatags-jquery-ui', $plugindir_url .'/js/jquery-ui/css/flick/jquery-ui-1.7.3.custom.css', array('mediatags-stylesheet'), 0.1 );
            	wp_enqueue_script('mediatags-bulk-common', $plugindir_url .'/categories_bulk_common.js', array('jquery', 'jquery-ui-core', 'jquery-ui-dialog'), 0.1);
            	wp_enqueue_script('mediatags-bulk-library', $plugindir_url .'/js/mediatags_bulk_library.js', array('jquery', 'mediatags-bulk-common'), 0.1);
            	wp_enqueue_script('mediatags', $plugindir_url .'/js/mediatags.js', array('jquery'), 0.1);
            		}
            	}
                
            }
        }
        
        
        /**
         * Step 1: add the custom Bulk Action to the select menus
         */
        function custom_bulk_admin_footer() {
            global $post_type;
            
            if($post_type == 'attachment') {
                ?>
                    <script type="text/javascript">
                        jQuery(document).ready(function() {
                            jQuery('<option>').val('Categories').text('<?php _e('Categories')?>').appendTo("select[name='action']");
                            //jQuery('<option>').val('Categories').text('<?php _e('Export')?>').appendTo("select[name='action2']");
                        });
                    </script>
                <?php
            }
            
            global $post_type;

        
            if ($post_type == 'attachment')
            {    
                    mediatags_bulk_admin_panel();
                    ?>
                    
                    <div id="media-category-bulk-selection-error" title="Media Category Selection Error" style="display:none">
                        <p>You must first select which Media Items to change.</p><p>Please close this dialog window and make your selection.</p>
                    </div>
                    
                    <script type="text/javascript">
                        jQuery(document).ready(function() {
                            jQuery('<option>').val('Categories').text('Categories').appendTo("select[name='action']");
                            jQuery('<option>').val('Categories').text('Categories').appendTo("select[name='action2']");
                        });
                    </script>
                    <?php
            }
        }

        


        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        /**
         * Step 2: handle the custom Bulk Action
         * 
         * Based on the post http://wordpress.stackexchange.com/questions/29822/custom-bulk-action
         */
        function custom_bulk_action() {
            global $typenow;
            $post_type = $typenow;
            
            if($post_type == 'attachment') {
                
                // get the action
                $wp_list_table = _get_list_table('WP_Media_List_Table');  // depending on your resource type this could be WP_Users_List_Table, WP_Comments_List_Table, etc
                $action = $wp_list_table->current_action();
                
                $allowed_actions = array("Categories");
                if(!in_array($action, $allowed_actions)) return;
                
                // security check
                check_admin_referer('bulk-media');
                
                // make sure ids are submitted.  depending on the resource type, this may be 'media' or 'ids'
                if(isset($_REQUEST['media'])) {
                    $post_ids = array_map('intval', $_REQUEST['media']);
                }
                
                if(empty($post_ids)) return;
                
                // this is based on wp-admin/edit.php
                $sendback = remove_query_arg( array('exported', 'untrashed', 'deleted', 'ids'), wp_get_referer() );
                if ( ! $sendback )
                    $sendback = admin_url( "edit.php?post_type=$post_type" );
                
                $pagenum = $wp_list_table->get_pagenum();
                $sendback = add_query_arg( 'paged', $pagenum, $sendback );
                
                switch($action) {
                    case 'Categories':
                        
                        // if we set up user permissions/capabilities, the code might look like:
                        //if ( !current_user_can($post_type_object->cap->export_post, $post_id) )
                        //    wp_die( __('You are not allowed to export this post.') );
                        
                        /*
$exported = 0;
                        foreach( $post_ids as $post_id ) {
                            
                            if ( !wp_set_post_categories( $post_ID, $post_categories ) $post_id) )
                                wp_die( __('Error exporting post.') );
            
                            $exported++;
                        }
                        
                        $sendback = add_query_arg( array('exported' => $exported, 'ids' => join(',', $post_ids) ), $sendback );
*/


                        

                    break;
                    
                    default: return;
                }
                
                $sendback = remove_query_arg( array('action', 'action2', 'tags_input', 'post_author', 'comment_status', 'ping_status', '_status',  'post', 'bulk_edit', 'post_view'), $sendback );
                
                wp_redirect($sendback);
                exit();
            }
        }
        
        function custom_bulk_action_admin_panel()
        {
            ?>
            <div id="media-tags-bulk-panel" title="Bulk Media Categories Management" style="display:none">
        
                <p class="header">Assign or Remove Media Categories from the selected Media items. <strong>Note this action cannot be undone!</strong></p>
        
                <div id="media-tags-error"></div>
                <div id="media-items-count-container">
                    <p>Number of Media Items Selected: <span id="media-items-count"></span></p>
                </div>
                <div id="media-tag-action-container">
                    <input type="radio" name="media_tags_action" id="media_tags_action_assign" value="media_tags_assign">&nbsp;
                    <label for="media_tags_action_assign">Assign Selected items to...</label><br />
                    <input type="radio" name="media_tags_action" id="media_tags_action_remove" value="media_tags_remove">&nbsp;
                    <label for="media_tags_action_remove">Remove Selected items from...</label><br />
                </div>
                <div style="clear:both; height: 10px"></div>
                <?php
                    $mediatag_terms = (array) get_terms('category', array( 'hide_empty' => false ));
                    if ($mediatag_terms)
                    {
                        ?>
                        <div class="media-tags-bulk-list-common">
                            <p><strong>Select from the category:</strong></p>
                            <ul class="media-tags-list"><?php
                            foreach($mediatag_terms  as $idx => $tag_item)
                            {
                                ?><li><input type="radio" id="bulk-media-category-<?php echo $tag_item->term_id; ?>"
                                     class="bulk-media-category-item" value="<?php echo $tag_item->term_id; ?>"
                                    name='bulk-media-category-<?php echo $tag_item->term_id; ?>'  />
                                    <label for='bulk-media-category-<?php 
                                        echo $tag_item->term_id; ?>'><?php echo $tag_item->name; ?></label></li><?php
                            }
                            ?>
                            </ul>
                        </div>
                        <div style="clear:both"></div>
                        <?php
                    }
                ?>
                <p class="ml-submit"><input type="submit" class="button savebutton" style="display:none;" name="meditags-save" id="meditags-save" value="Update Media-Tags" /></p>
            </div>
            <?php
        }
        
        
        
        
        
        
        /**
         * Step 3: display an admin notice on the Posts page after exporting
         */
        function custom_bulk_admin_notices() {
            global $post_type, $pagenow;
            
            if($pagenow == 'edit.php' && $post_type == 'attachment' && isset($_REQUEST['exported']) && (int) $_REQUEST['exported']) {
                $message = sprintf( _n( 'Media Categorie Changed.', '%s Media Categories Changed.', $_REQUEST['exported'] ), number_format_i18n( $_REQUEST['exported'] ) );
                echo "<div class=\"updated\"><p>{$message}</p></div>";
            }
        }
        
        function perform_export($post_id) {
            // do whatever work needs to be done
            return true;
        }
    }
}

new CFM_Custom_Bulk_Action();