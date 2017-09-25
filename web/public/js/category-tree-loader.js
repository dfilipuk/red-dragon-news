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
            $.jstree.defaults.core.themes.variant = "large";
            $(function () { $('#categoryDiv')
                .bind("select_node.jstree", function (e, data) {
                    var href = data.node.a_attr.href;
                    document.location.href = href;
                })
                .jstree({
                    'core': {
                    'themes': {
                        'name': 'proton',
                            'responsive': true
                    }
                },
                    "plugins": [ "contextmenu" ]
                });
            });

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
            cateroryTree +=  '<li><a class="news-href text-32" href="/main/' + tree[k][k]['name'] + '">' + tree[k][k]['name'] + '</a>';
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
