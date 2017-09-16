var dataUrl, sortableColumns, filterableColumns, rowsPerPage;

(function( $ ){
    $.fn.ajaxgrid = function({columnsNames, url, sortColumns, filterColumns, rowsPerPageAmo}) {
        dataUrl = url;
        sortableColumns = sortColumns;
        filterableColumns = filterColumns;
        rowsPerPage = rowsPerPageAmo;
        addTable(columnsNames);

        $.get(
            dataUrl,
            {rowsamo : 3, page : 1},
            function(data, textStatus, jqXHR) {
                handleResponseData(data);
            });
    };
})( jQuery );

function handleResponseData(data) {
    if (data.success) {
        addTableBody(data.items);
    }
}

function addTable(columns) {
    var table = '<table class="table"><thead><tr>';
    for (var k in columns) {
        table += '<td>' + columns[k] + '</td>';
    }
    table += '</tr></thead><tbody id="table-body"></tbody></table>';
    $("#entities-grid").append(table);
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