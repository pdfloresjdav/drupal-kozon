(function($) {
var datas;
$(document).on("click", '.page_change', function(event) {

    datas = event.currentTarget;

    var page = $(datas).attr('page');
    var address = new Object;
        address['page']=page;

    var url = '/kozon/load_page'; 
    
    $.ajax({
      url: url,
      type: 'POST',
      data: address,
      dataType: 'json',
      success: function(data){
      	var childrenEl = $('.page_change');
      	childrenEl.each(function( i ) {
				$(this).removeClass('pag-selected');
                    
        });
        $('.post-kozon-main').html('');
        $('.post-kozon-main').html(data[0].data);
        $(datas).addClass('pag-selected');
      }
    });

  });

$(document).on("click", '.page-more', function(event) {

    var data = event.currentTarget;

    var prev = $(data).attr('prev');
    var next = $(data).attr('next');
    
    $('.pag-'+next).removeClass('page-inactive');
    $('.pag-'+prev).addClass('page-inactive');

  });

  $(document).on("click", '.page-less', function(event) {

    var data = event.currentTarget;

    var prev = $(data).attr('prev');
    var next = $(data).attr('next');
    
    $('.pag-'+next).addClass('page-inactive');
    $('.pag-'+prev).removeClass('page-inactive');

  });

  $(document).on("click", '.more-sub-articles', function(event) {

    var data = event.currentTarget;
    var page = $(data).attr('page');
    var tid = $(data).attr('tid');

    var address = new Object;
        address['page']=page;
        address['tid']=tid;

    var url = '/kozon_ajax_tag';
    
    $.ajax({
      url: url,
      type: 'POST',
      data: address,
      dataType: 'json',
      success: function(data){
        $('.k-button').remove();
        $('.forum-categories-sub').append(data[0].data);
      }
    });

  });
  
})(jQuery);




