var dataUrl, sortableColumns, filterableColumns, rowsPerPage, currentPage, paramsString;

(function( $ ){
    $.fn.ajaxgrid = function({columnsNames, url, sortColumns, filterColumns, rowsPerPageAmo}) {
        dataUrl = url;
        sortableColumns = sortColumns;
        filterableColumns = filterColumns;
        rowsPerPage = rowsPerPageAmo;
        paramsString = 'rowsamo=' + rowsPerPage;
        addDateContainer(columnsNames);
        getPage(1);
    };
})( jQuery );

function sendRequest(params) {
    $.get(
        dataUrl,
        params,
        function(data, textStatus, jqXHR) {
            handleResponse(data);
        });
}

function handleResponse(data) {
    if (data.success) {
        addTableBody(data.items);
        addPagination(data.pagesAmo);
    }
}

function addDateContainer(columns) {
    var table = '<table class="table"><thead><tr>';
    for (var k in columns) {
        table += '<td>' + columns[k] + '</td>';
    }
    table += '</tr></thead><tbody id="table-body"></tbody></table>';
    $("#entities-grid").append(table);
    var pagination = '<div class="navigation text-center"><ul id="pagination" class="pagination"></ul></div>';
    $("#entities-grid").append(pagination);
}

function addTableBody(items) {
    var tableBody = '';
    for (var i = 0; i < items.length; i++) {
        tableBody += '<tr>';
        for (var j = 0; j < items[i].length; j++) {
            tableBody += '<td>' + items[i][j] + '</td>';
        }
        tableBody += '</tr>';
    }
    $("#table-body").append(tableBody);
}

function addPagination(pagesAmo) {
    var pagination = '<li ';
    if (currentPage === 1) {
        pagination += 'class="disabled"';
    } else {
        pagination += 'onclick="getPage(' + (currentPage - 1) + ')"';
    }
    pagination += '"><a href=#>Previous</a></li>';

    for (var i = 0; i < pagesAmo; i++) {
        pagination += '<li ';
        if (currentPage === i + 1) {
            pagination += 'class="active"';
        } else {
            pagination += 'onclick="getPage(' + (i + 1) + ')"';
        }
        pagination += '><a href=#>' + (i + 1) + '</a></li>';
    }

    pagination += '<li ';
    if (currentPage === pagesAmo) {
        pagination += 'class="disabled"';
    } else {
        pagination += 'onclick="getPage(' + (currentPage + 1) + ')"';
    }
    pagination += '"><a href=#>Next</a></li>';

    $("#pagination").append(pagination);
}

function getPage(pageNum) {
    currentPage = pageNum;
    $("#table-body").empty();
    $("#pagination").empty();
    sendRequest(paramsString + '&page=' + pageNum);
}