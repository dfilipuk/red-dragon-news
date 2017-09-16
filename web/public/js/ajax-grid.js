var dataUrl, sortableColumns, filterableColumns, rowsPerPage;

(function( $ ){
    $.fn.ajaxgrid = function({columnsNames, url, sortColumns, filterColumns, rowsPerPageAmo}) {
        dataUrl = url;
        sortableColumns = sortColumns;
        filterableColumns = filterColumns;
        rowsPerPage = rowsPerPageAmo;
        addTable(columnsNames);
    };
})( jQuery );

function addTable(columns) {
    var table = '<table class="table"><thead><tr>';
    for (var k in columns) {
        table += '<td>' + columns[k] + '</td>';
    }
    table += '</tr></thead><tbody id="table-body"></tbody></table>';
    $("#entities-grid").append(table);
}