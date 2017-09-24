//var responseReceived = true;

$("#parent-name-input").keyup(onKeyUp);

var similar = "";

function onKeyUp() {
    similar = $("#parent-name-input").children().first().val();

}

$(document).ready(function () {
    $(function () {
        $("#article_new_category").autocomplete({
            source: function (request, response) {
                $.ajax({
                    method: "POST",
                    url: dataUrl,
                    data: {
                        similar: similar
                    },
                    success: function (data) {
                        console.log(data);
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

