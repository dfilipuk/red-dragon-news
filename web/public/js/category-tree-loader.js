$(document).ready(function ()
{
    startLoadingAnimation();
    $.ajax({
        type: "POST",
        url: "/load-tree",
        datatype: "json",
        success: function(result){
            printTree(result);
            $('#categoryDiv').append(cateroryTree);
            stopLoadingAnimation();
        }
    });
});

function startLoadingAnimation()
{
    var imgObj = $("#loadImg");
    imgObj.show();
    var centerY = $(window).scrollTop() + ($(window).height() - imgObj.height())/2;
    var centerX = $(window).scrollLeft() + ($(window).width() - imgObj.width())/2;
    imgObj.offset({top:centerY, left:centerX});
}

function stopLoadingAnimation()
{
    $("#loadImg").hide();
}

var cateroryTree = '';
function printTree(tree) {
    if(tree !== null && count(tree) > 0) {
        cateroryTree += '<ul>';
        for(var k in tree) {
            cateroryTree +=  '<li><h5><a class="news-href" href="/main/' + tree[k][k]['name'] + '">' + tree[k][k]['name'] + '</a></h5>';
            printTree(tree[k]['children']);
            cateroryTree +=  '</li>';
        }
        cateroryTree += '</ul>';
    }
}

function count(array)
{
    var cnt=0;
    for (var i in array) {
        if (i) {
            cnt++
        }
    }
    return cnt
}
