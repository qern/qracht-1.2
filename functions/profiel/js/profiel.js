function statusAction(id, action, status) {
        $('#waiting').show(600);
        $('#statussen').html('');
        $.ajax({
            type : 'POST',
            url : '/functions/profiel/includes/status.php',
            dataType : 'html',
            processDataBoolean: false,
            data: { 
                id : id,
                status : $('#status_schrijven').val(),
                action : action,
                status_id : status,
                gebruiker : $('#gebruiker_id').html()
            },
            success : function(data){
                $('#status_schrijven').val(''),
                $('#waiting').hide(600);
                $('#statussen').html(data);
            }
        });

        return false;
};
function reactieForm(status, action, reactie) {
        $.ajax({
            type : 'POST',
            url : '/functions/profiel/includes/reactie.php',
            dataType : 'html',
            data: {
                id : status,
                reactie : $('#reactie_'+status).val(),
                action : action,
                reactie_id : reactie 
            },
            success : function(data){
                $('#reactielijst_'+status).html(data);
                $('#reactie_'+status).val('');
            }
        });

        return false;
};
huidigeDatum = Math.round(new Date().getTime() / 1000 ) ;
startDatum = 1322719200;
function leesVerderStatus(gebruiker, interval) {
    if(interval === 1){$('#waiting').show(600); $('#statussen_lijst').html('');}
    $.ajax({
        type : 'GET',
        url : '/functions/profiel/includes/status.php',
        dataType : 'html',
        data: { gebruiker : gebruiker, interval: interval },
        success : function(data){
            if(interval === 1){$('#waiting').hide(600);}
            $('#statussen_lijst').append(data);
            if(((huidigeDatum - startDatum) - ((60*60*24*10) * interval)) > 0){
                $('#overige_nieuws_meer_lezen').html('<span class="next_batch" onclick="leesVerderStatus(' + gebruiker + ', ' + (interval + 1) + ')">Meer laden...</span>');
            }else{$('#overige_nieuws_meer_lezen').html('');}
        }
    });return false;
};

function toggle_status_schrijven(){var ele = document.getElementById('nieuwe_status' + id);if(ele.style.display =='block'){ele.style.display = 'none';}else{ele.style.display='block';}}
function toggle_reacties(id){var ele = document.getElementById('reactielijst_' + id);if(ele.style.display =='block'){ele.style.display = 'none';}else{ele.style.display='block';}}
function toggle_reageren(id){var ele = document.getElementById('reageren_' + id);if(ele.style.display =='block'){ele.style.display = 'none';}else{ele.style.display='block';}}

$(function(){$("a.iframe").fancybox({'titleShow': false,'overlayShow': true,'overlayColor': '#000','overlayOpacity': 0.7,'hideOnContentClick': true, 'centerOnScroll': true,'scrolling': 'auto', 'transitionIn': 'elastic','transitionOut': 'elastic','speedIn': 600,'speedOut': 200,'width': 750,'height': 750});}); 