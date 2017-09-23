'use strict';

$(function() {

  $('.search-input input').blur(function() {

    if ($(this).val()) {
      $(this)
        .find('~ label, ~ span:nth-of-type(n+3)')
        .addClass('not-empty');
    } else {
      $(this)
        .find('~ label, ~ span:nth-of-type(n+3)')
        .removeClass('not-empty');
    }
  });

  $('.search-input input ~ span:nth-of-type(4)').click(function() {
    $('.search-input input').val('');
    $('.search-input input')
      .find('~ label, ~ span:nth-of-type(n+3)')
      .removeClass('not-empty');
  });


});


var buttonOpen = document.querySelector('#show-dialog');
var buttonClose = document.querySelector('.close');
var buttonSubscribe = document.querySelector('#subscribe');
var dialog = document.querySelector('dialog');

buttonOpen.addEventListener('click', function() {
    dialog.showModal();
});

buttonClose.addEventListener('click', function() {
    dialog.close();
});

buttonSubscribe.addEventListener('click', function() {
    $.post({
        url: "/subscribe-user",
        data:
            {
              type: $('#subscription').val()
            },
        success: function(){
            dialog.close();
        }
    });
});