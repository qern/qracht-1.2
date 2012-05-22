/*
# CKEDITOR Edit-In Place jQuery Plugin.
# Created By Dave Earley.
# www.Dave-Earley.com
*/

jQuery.fn.ckeip = function (options, callback) {

    var original_html = jQuery(this);
    var defaults = {
        e_height: '10',
        data: {}, e_url: '',
        e_hover_color: '#eeeeee',
        ckeditor_config: '',
        e_width: '50'
    };
    var settings = jQuery.extend({}, defaults, options);

    return this.each(function () {
        var eip_html = jQuery(this).html();
        var u_id = Math.floor(Math.random() * 99999999);


        jQuery(this).before("<div class='html_editor' id='ckeip_" + u_id + "'  style='display:none;'><textarea id ='ckeip_e_" + u_id + "' cols='" + settings.e_width + "' rows='" + settings.e_height + "'  >" + eip_html + "</textarea>  <br /><a href='#' class='save_html' id='save_ckeip_" + u_id + "'>Save</a> <a href='#' class='cancel_html' id='cancel_ckeip_" + u_id + "'>Cancel </a></div>");

        jQuery('#ckeip_e_' + u_id + '').ckeditor(settings.ckeditor_config);

        jQuery(this).bind("dblclick", function () {

            jQuery(this).hide();
            jQuery('#ckeip_' + u_id + '').show();

        });

        jQuery(this).hover(function () {
            jQuery(this).css({
                backgroundColor: settings.e_hover_color
            });
        }, function () {
            jQuery(this).css({
                backgroundColor: ''
            });
        });


        jQuery("#cancel_ckeip_" + u_id + "").click(function () {
            jQuery('#ckeip_' + u_id + '').hide();
            jQuery(original_html).fadeIn();
            return false;
        });

        jQuery("#save_ckeip_" + u_id + "").click(function () {
            var ckeip_html = jQuery('#ckeip_e_' + u_id + '').val();
            jQuery.post(settings.e_url, {
                content: ckeip_html,
                data: settings.data
            }, function (response) {
                if (typeof callback == "function") callback(response);

                jQuery(original_html).html(ckeip_html);
                jQuery('#ckeip_' + u_id + '').hide();
                jQuery(original_html).fadeIn();

            });;
            return false;

        });

    });
};