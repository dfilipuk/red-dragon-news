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
var buttonUnsubscribe = document.querySelector('#unsubscribe');


if (buttonOpen !== null) {
    buttonOpen.addEventListener('click', function () {
        dialog.showModal();
    });

    buttonClose.addEventListener('click', function () {
        dialog.close();
    });

    buttonSubscribe.addEventListener('click', function () {
        $.post({
            url: "/subscribe-user",
            data:
                {
                    type: $('#subscription').val(),
                    subscribe: 1
                },
            success: function () {
                dialog.close();
                location.reload();
            }
        });
    });
}

if (buttonUnsubscribe !== null) {
    buttonUnsubscribe.addEventListener('click', function () {
        $.post({
            url: "/subscribe-user",
            data:
                {
                    subscribe: 0
                },
            success: function () {
                location.reload();
            }
        });
    });
}