<?php
/*
Plugin Name: Custom Bulk Edit Categories for media
Plugin URI: https://github.com/tomasbulva/Wordpress-Bulk-Categories-Edit-for-Media
Description: Allows to edit categories in bulk at attachement/media page, needs to have categories for media enabled.
Author: Tomas Bulva - based on work of Justin Stern and Paul Menard
Author URI: http://www.cchnageinc.com
Version: 1.0
*/

if (!class_exists('cbec_action')) {
 
    class cbec_action {
        
        public function __construct() { //constractor
                $plugindir_node = dirname(plugin_basename(__FILE__));
                $this->plugindir_url = WP_CONTENT_URL . "/plugins/". $plugindir_node;

            if(is_admin()) {
                add_action('admin_footer', array(&$this, 'custom_bulk_categories_admin_footer'));
                add_action( 'wp_ajax_custom_bulk_categories_action', array(&$this, 'cbec_action_callback') );
                
                if ($this->bulkcat_check_url('wp-admin/upload.php'))
                {
                    wp_enqueue_style( 'cbec-jquery-ui-style', $this->plugindir_url .'/js/jquery-ui/css/smoothness/jquery-ui.css');
                    wp_enqueue_style( 'cbec-jquery-ui-theme', $this->plugindir_url .'/js/jquery-ui/css/smoothness/jquery.ui.theme.css');
                    
                    if(wp_script_is('jquery-ui')){ 
                        wp_enqueue_script('jquery'); 
                        wp_enqueue_script('jquery-ui-core'); 
                        wp_enqueue_script('jquery-ui-dialog');
                        wp_enqueue_script('jquery-ui-widget');
                        wp_enqueue_script('jquery-ui-button');
                    }
                    
                    wp_enqueue_script('cat_bulk_common', $this->plugindir_url .'/js/categories_bulk_common.js', array('jquery'));
                }
            }
        }
        
        public function cbec_action_admin_panel()
        {
            ?>
            <div id="cbec_action-panel" title="Bulk Media Categories Management" style="display:none">
        
                <p class="header">Assign or Remove Media Categories from the selected Media items. <br /><strong>Note this action cannot be undone!</strong></p>
        
                <div id="media-categories-error"></div>
                <div id="media-items-count-container">
                    <p>Number of Media Items Selected: <span id="media-items-count"></span></p>
                </div>
                <div id="media-categories-action-container">
                    <input type="radio" name="media_categories_action" id="media_categories_action_assign" value="assign" checked="checked"><label for="media_categories_action_assign">Assign</label>
                    <input type="radio" name="media_categories_action" id="media_categories_action_remove" value="remove"><label for="media_categories_action_remove">Remove</label>
                </div>
                <div style="clear:both; height: 10px"></div>
                <?php
                    $mediacategories_terms = (array) get_terms('category', array( 'hide_empty' => false ));
                    if ($mediacategories_terms)
                    {
                        ?>
                        <div class="media-categories-bulk-list-common">
                            <p><strong>Select from the category:</strong></p>
                            <div class="media-categories-list"><?php
                            foreach($mediacategories_terms  as $idx => $tag_item)
                            {
                                ?>
                                <input type="radio" id="bulk-media-category-<?php echo $tag_item->term_id; ?>" class="bulk-media-category-item" value="<?php echo $tag_item->term_id; ?>" name='bulk-media-category' />
                                <label for='bulk-media-category-<?php echo $tag_item->term_id; ?>'><?php echo $tag_item->name; ?></label>
                                <?php
                            }
                            ?>
                            </div>
                        </div>
                        <div style="clear:both"></div>
                        <?php
                    }
                ?>
                <p class="ml-submit"><input type="submit" class="button savebutton" style="display:none;" name="meditags-save" id="medicategories-save" value="Update Categories" /></p>
            </div>
            <?php
        }
        
        public function cbec_action_callback() {
            
            //print_r($_REQUEST);
            
            if (isset($_REQUEST['media_categories_action']))
                $media_categories_action = $_REQUEST['media_categories_action'];
            else
                $media_categories_action = "";
        
            if ((isset($_REQUEST['select_media_categories'])) && (strlen($_REQUEST['select_media_categories'])))
                $select_media_categories = explode(",",$_REQUEST['select_media_categories']);
            else
                $select_media_categories = array();
                
            if ((isset($_REQUEST['select_media_items'])) && (strlen($_REQUEST['select_media_items'])))
                $select_media_items = explode(",", $_REQUEST['select_media_items']);
            else
                $select_media_items = array();
        
            
            if ( (strlen($media_categories_action)) && (count($select_media_items)) )
            {
                $index = 0;
                foreach($select_media_categories as $select_media_category)
                {
                    $term = get_term($select_media_categories[0], "category");
                    $slug = $term->slug;
                }
                if ($media_categories_action == "assign")
                {
                    foreach($select_media_items as $select_media_item_id)
                    {
                        wp_set_object_terms($select_media_item_id, NULL, "category");
                    }
                    
                    foreach($select_media_items as $select_media_item_id)
                    {
                        wp_set_object_terms($select_media_item_id, $slug, "category");
                    }
                }
                else if ($media_categories_action == "remove")
                {
                    foreach($select_media_items as $select_media_item_id)
                    {
                        wp_set_object_terms($select_media_item_id, NULL, "category");
                    }
                }
            }
            die();
        }

        public function custom_bulk_categories_admin_footer() {
            global $post_type;
            if ($post_type == 'attachment')
            {    
                    $this->cbec_action_admin_panel();
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
        
        public function bulkcat_check_url($url='')
        {
            if (!$url) return;
            
            $_REQUEST_URI = explode('?', $_SERVER['REQUEST_URI']);
            $url_len     = strlen($url);
            $url_offset = $url_len * -1;
        
            // If out test string ($url) is longer than the page URL. skip
            if (strlen($_REQUEST_URI[0]) < $url_len) return;
        
            if ($url == substr($_REQUEST_URI[0], $url_offset, $url_len))
                    return true;
        }
        

    } // class end
}

if (class_exists("cbec_action")) {
    new cbec_action();
}


?>
