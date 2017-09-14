$(document).ready(function ()
{
    var url = window.location.pathname;
    var id = url.substring(url.lastIndexOf('/') + 1);
    $.ajax({
        type: "GET",
        url: "/update-watch-count/" + id
    });
});