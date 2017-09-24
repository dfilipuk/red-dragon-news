var buttonOpenAddSimilar = document.querySelector('#open-add-similar');
var dialog = document.querySelector('dialog');
var buttonClose = document.querySelector('.close');
var similarCount = $('.mdl-chip--deletable').length;
var similarNewsIds = [];

buttonOpenAddSimilar.addEventListener('click', function () {
    dialog.showModal();
});

function deleteChip(){
    var element = $(event.target);
    element.closest('div').remove();
    similarCount--;
    if (similarCount < 5){
        buttonOpenAddSimilar.style.display = '';
    }
}

function getSimilar(){
    var count = $("#chips").children(".mdl-chip--deletable").length;
    var chips = $("#chips").children(".mdl-chip--deletable");
    chips.each(function(){
        var id = $(this).attr("id");
        id = id.substring(8);
        similarNewsIds.push(id);
    });
}

function addSimilarIds(){
    similarNewsIds = [];
    getSimilar();
    $("#similarNews").val(similarNewsIds.toString());
}


buttonClose.addEventListener('click', function () {
    dialog.close();
});

$(document).ready(function () {
    $(function () {
        getSimilar();
        if (similarCount >= 5){
            buttonOpenAddSimilar.style.display = 'none';
        }
        $("#tag").autocomplete({
            source: function (request, response) {
                $.ajax({
                    url: "/admin/ajax/search",
                    data: request,

                    success: function (data) {
                        response($.map(data, function (item) {
                            return {
                                label: item[1],
                                value: item[0]
                            };

                        }))
                    }
                });
            },
            selectFirst: false,
            select: function (e, ui) {
                if (!similarNewsIds.includes(ui.item.value.toString())) {
                    var newChips = '<div class="mdl-chip mdl-chip--deletable" id="similar-' + ui.item.value + '"><span class="mdl-chip__text">' + ui.item.label + '</span><button type="button" class="mdl-chip__action"  onClick="deleteChip()"><i class="material-icons">cancel</i></button></div>';
                    $("#chips").append(newChips);
                    similarCount++;
                    if (similarCount >= 5) {
                        buttonOpenAddSimilar.style.display = 'none';
                    }
                    $("#tag").val("");
                    $(".close").focus();
                    getSimilar();
                    dialog.close();
                }
                return false;
            }

        });

    });

});
