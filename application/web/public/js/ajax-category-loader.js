//var responseReceived = true;

$("#parent-name-input").keyup(onKeyUp);

var similar = "";

function onKeyUp() {
    similar = $("#parent-name-input").children().first().val();

}

$(document).ready(function () {
    $(function () {
        $("#" + searchInput).autocomplete({
            source: function (request, response) {
                $.ajax({
                    method: "POST",
                    url: dataUrl,
                    data: {
                        similar: similar
                    },
                    success: function (data) {
                        response($.map(data, function (item) {
                            return {
                                label: item,
                                value: item
                            };

                        }))
                    }
                });

            },
            selectFirst: false

        });
    })
});

