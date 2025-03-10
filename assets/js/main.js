function showUserDetail(id) {
    //$('.show-spin').css('visibility', 'visible');
    $.ajax({
        type: "POST",
        url: {link getDetail!},
        dataType: "Text",
        ContentType: "application/x-www-form-urlencoded; charset=UTF-8",
        data: {
           rowId: id
        },
        success: function(data) {
            if(data) {
                //$('.show-spin').css('visibility', 'hidden');
                $('#detailModal .modal-content').html(data);
                $('#detailModal').modal('toggle');
            }
        }
    });
}

$('.company-li').click(function() {
    $('#frm-simpleGrid-filter-filter-fullname').val('');
    $('#frm-simpleGrid-filter-filter-company_name').val($(this).text());
    $('#frm-simpleGrid-filter-filter-company_name').trigger('keyup');
});

$('#frm-simpleGrid-filter-filter-fullname').keyup(function() {
    $('#frm-simpleGrid-filter-filter-company_name').val('');
    //$('#frm-simpleGrid-filter-filter-company_name').trigger('click');
});

function showSpin() {
    //$('.show-spin').css('visibility', 'visible');
    //alert($("#snippet-simpleGrid-table tr").length);
    //alert($(location).attr('href'));
}

$('.companies-title').click(function() {
    $('#frm-simpleGrid-filter-filter-company_name').val('');
    $('#frm-simpleGrid-filter-filter-company_name').trigger('keyup');
});

$(document).bind("ajaxSend", function() {
    $('.show-spin').css('visibility', 'visible');
}).bind("ajaxStop", function(){
    $('.show-spin').css('visibility', 'hidden');
});

$(document).scroll(function() {
  var y = $(this).scrollTop();
  if (y > 800) {
    $('#spnTop').fadeIn();
  } else {
    $('#spnTop').fadeOut();
  }
});

$('#spnTop').on("click",function() {
    $('html, body').animate({ scrollTop: 0 }, 'slow', function () {
    });
});
