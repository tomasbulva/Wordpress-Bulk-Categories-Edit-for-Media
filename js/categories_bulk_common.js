  jQuery(document).ready(function(){
    
    jQuery('form#posts-filter').submit(function(){
        var is_cbec_action = jQuery('form#posts-filter select[name=action]').val();
        var is_cbec_action2 = jQuery('form#posts-filter select[name=action2]').val();

        if ((is_cbec_action == "Categories") || (is_cbec_action2 == "Categories"))
        {
            var select_media_items = "";
            var select_media_items_cnt = 0;
            var select_media_items_text = "";
            
            jQuery('form#posts-filter tbody').find('input[type=checkbox]:checked').each( function(){
                if (select_media_items != "")
                    select_media_items = select_media_items+",";
                select_media_items = select_media_items+jQuery(this).val();
                select_media_items_cnt = select_media_items_cnt + 1;
                
                var p_parent_id = jQuery(this).parent().parent().attr('id');

                var select_media_items_anchor = jQuery('tr#'+p_parent_id+' td.column-media a:first').each(function() {
                    if (select_media_items_text != "")
                        select_media_items_text = select_media_items_text+", ";
                    select_media_items_text = select_media_items_text+jQuery(this).text();
                });
                var select_media_items_anchor = jQuery('tr#'+p_parent_id+' td.column-title a:first').each(function() {
                    if (select_media_items_text != "")
                        select_media_items_text = select_media_items_text+", ";
                    select_media_items_text = select_media_items_text+jQuery(this).text();
                });
            });
            
            if (select_media_items == "")
            {
                show_cbec_error_popup();
            }
            else
            {
                jQuery('span#media-items-count').html( "<strong>("+select_media_items_cnt+"):<br />"+select_media_items_text+"</strong>" );
                show_media_tags_bulk_admin();
            }    
            return false;
        }    
    });
    
    function show_media_tags_bulk_admin()
    {
        //jQuery('#media-tags-bulk-content-buttons .submit').text("submit");
        //jQuery('#media-tags-bulk-content-buttons .cancel').text();
        jQuery("#cbec_action-panel").dialog({
            autoOpen: false,
            width: 600,
            height: "auto",
            resizable: true,
            create: function( event, ui ){
                jQuery( "#media-categories-action-container, .media-categories-list" ).buttonset();
                jQuery("#cbec_action-panel input[name='media_categories_action']").change( function() {
                    if(jQuery(this).val() == "remove"){
                        jQuery(".media-categories-bulk-list-common").hide();
                    }else{
                        jQuery(".media-categories-bulk-list-common").show();
                    }
                });
            },
            buttons: {
                Cancel: function() {
                  jQuery( this ).dialog( "close" );
                },
                "Apply": function() {
                  cbec_process_bulk_selections();
                }
                
            }
        });
        
        jQuery("#cbec_action-panel").dialog('open');        
    }

    function show_cbec_error_popup()
    {

        jQuery("#media-tags-bulk-selection-error").dialog({
            autoOpen: false,
            width: 300,
            height: 200,
            resizable: true,
            position: 'center',
            buttons: {
                Cancel: function() {
                  jQuery( this ).dialog( "close" );
                }
            }
        });
        jQuery("#media-tags-bulk-selection-error").dialog('open');
    }
    
    
    
    function cbec_process_bulk_selections()
    {    
        var media_cat_action = "";
        media_cat_action = jQuery("#cbec_action-panel input[name=media_categories_action]:checked").val();

        if (media_cat_action == "")
        {
            alert("action error");
            jQuery('#media-categories-error').html('<p>You must select and action of Assign or Remove to apply to the Media Items and Media Categories.</p>');
            jQuery('#media-categories-error').focus();
            return false;
        }
        else
        {
            jQuery('#media-categories-error').html('<p></p>');
        }

        var select_media_cat = "";
    
            jQuery('div#cbec_action-panel input.bulk-media-category-item:checked').each(function(){
                if (jQuery(this).val() != "on") {
                    if (select_media_cat != "")
                        select_media_cat = select_media_cat+",";
                    select_media_cat = select_media_cat+jQuery(this).val();
                }
            });
    
        if ( (media_cat_action != "remove") && (select_media_cat == "" && media_tags_cat == "") )
        {
            alert("category not selected error");
            jQuery('#media-categories-error').html('<p>Please enter or select which Media Categories should be applied to the selected Media Items.</p>');
            jQuery('#media-categories-error').focus();
            return false;
        }
   
        var select_media_items = "";
        jQuery('form#posts-filter tbody').find('input[type=checkbox]:checked').each( function(){
            if (jQuery(this).val() != "on")
            {
                if (select_media_items != "")
                    select_media_items = select_media_items+",";
                select_media_items = select_media_items+jQuery(this).val();
            }
        });

        if (select_media_items == "")
        {
            alert("no items error");
            jQuery('#media-categories-error').html('<p>You must first select which Media Items to change.</p>');
            jQuery('#media-categories-error').focus();
            return false;
        }
        else
        {
            jQuery('#media-categories-error').html('<p></p>');
        }

        var data = {
            action: 'custom_bulk_categories_action',
            media_categories_action: media_cat_action,
            select_media_items: select_media_items,
            select_media_categories: select_media_cat
        };
        
        jQuery.post(ajaxurl, data, function(response) {
            //console.log(response);
            location.reload();
        });
        return true;
    }
    
    
});