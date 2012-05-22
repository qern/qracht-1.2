IncludeJavaScript('functions/admin/js/jquery.infieldlabel.min.js');
$(function(){$(".label").inFieldLabels();})

function toonOpties(optie){
        $('#waiting').show(600);
        $('#admin_center_content').html('');
        
        // Loop door de menuitems.
        $( "#admin_menu span.menuitem" ).each(
            function( intIndex ){
                //als een item active is.. verwijder dan de active status
                if($(this).hasClass('active')){
                    $(this).removeClass('active');
                }
            } 
        );
        $.ajax({
            type : 'POST',
            url : '/functions/admin/includes/opties.php',
            dataType : 'html',
            data: { optie : optie },
            success : function(data){
                $('#waiting').hide(600);
                $('#admin_ajax').html(data);
                $('#'+optie+'_item').addClass('active');
               }
        });
        return false;
};
