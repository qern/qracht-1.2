function showStuff(){
$(function(){
    $("#kennis_schrijven").autocomplete({
        source:'/portal/functions/account/includes/kennis_ajax.php',
        minLength:2
    });
});

function goedinOpslaan() {
        $.ajax({
            type : 'POST',
            url : '/portal/functions/account/includes/kennis_check.php',
            dataType : 'html',
            data: {
                action : 'update_goed_in',
                goed_in : $('#goed_in_schrijven').val()
            },
            success : function(data){}
        });

        return false;
};
function refreshAll() {
        $.ajax({
            type : 'POST',
            url : '/portal/functions/account/includes/kennis_check.php',
            dataType : 'html',
            data: {action : 'refreshAll'},
            success : function(data){
                $('#kennis_overzicht_list').html(data);
            }
        });

        return false;
};

function refreshTop10() {
        $.ajax({
            type : 'POST',
            url : '/portal/functions/account/includes/kennis_check.php',
            dataType : 'html',
            data: {action : 'refreshTop10'},
            success : function(data){
                $('#top_10_list').html(data);
            }
        });

        return false;
};
function kennisOpslaan() {
        $.ajax({
            type : 'POST',
            url : '/portal/functions/account/includes/kennis_check.php',
            dataType : 'html',
            data: {
                action : 'createkennis',
                kennis : $('#kennis_schrijven').val()
            },
            success : function(data){
                $('#kennis_schrijven').val(""),
                $('#kennis_overzicht_list').html(data);
                $(".wijzig_info_success").fadeOut(50000);
            }
        });

        return false;
};

function kennisVerwijderen(kennisId) {
        $.ajax({
            type : 'POST',
            url : '/portal/functions/account/includes/kennis_check.php',
            dataType : 'html',
            data: {
                action : 'deletekennis',
                kennis_id : kennisId
            },
            success : function(data){
                refreshAll();
                refreshTop10();
            }
        });

        return false;
};
function uitTop10(kennisId){
    $.ajax({
            type : 'POST',
            url : '/portal/functions/account/includes/kennis_check.php',
            dataType : 'html',
            data: {
                action : 'uitTop10',
                kennis_id : kennisId
            },
            success : function(data){
                $('#top_10_list').html(data);
            }
        });

        return false;
}

$(function(){
    $("#top_10_list").sortable({
        connectWith:'.kennis_top_10',
        update:function(){
            $.ajax({
                type:"POST",
                url:"/portal/functions/account/includes/kennis_check.php",            
                data:{
                    action: 'sort_kennis',
                    source: 'top',
                    kennis:$("#top_10_list").sortable('serialize')
                },
                success:function(data){
                    $('#top_10_list').html(data);
                }
            });
        }
    });
    $("#kennis_overzicht_list").sortable({
        connectWith:'.kennis_top_10',
        update:function(){
            $.ajax({
                type:"POST",
                url:"/portal/functions/account/includes/kennis_check.php",            
                data:{
                    action: 'sort_kennis',
                    source: 'all',
                    kennis:$("#top_10_list").sortable('serialize')
                },
                success:function(data){
                    $('#top_10_list').html(data);
                    refreshAll();
                }
            });
        }
    });
});
}
$(document).ready(showStuff);