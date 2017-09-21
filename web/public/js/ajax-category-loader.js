var responseReceived = true;

$("#parent-name-input").keyup(onKeyUp);

String.prototype.isEmpty = function() {
    return (this.length === 0 || !this.trim());
};

function onKeyUp() {
    if (responseReceived) {
        var val = $("#parent-name-input").children().first().val();
        val.trim();
        if (!val.isEmpty()) {
            val = $.trim(val);
            sendRequest(val);
        }
    }
}

function sendRequest(similar) {
    $.post(
        dataUrl,
        {
            similar: similar
        },
        function(data) {
            handleResponse(data);
        });
}

function handleResponse(data) {
    $("#similar-categories").empty();
    if (data.length > 0) {
        var list = '';
        for (var i = 0; i < data.length; i++) {
            list += '<li>' + data[i] + '</li>'
        }
        $("#similar-categories").append(list);
        responseReceived = true;
    }
}