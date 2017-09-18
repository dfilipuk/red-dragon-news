var dataUrl, sortableColumns, filterableColumns, rowsPerPage, sortableColumn, isAscending, filters = new Array();
var currentPage;

(function( $ ){
    $.fn.ajaxgrid = function({columnsNames, url, sortColumns, filterColumns, rowsPerPageAmo}) {
        dataUrl = url;
        sortableColumns = sortColumns;
        filterableColumns = filterColumns;
        rowsPerPage = rowsPerPageAmo;
        isAscending = true;
        sortableColumn = sortableColumns[0];
        addDateContainer(columnsNames);
        getPage(1);
    };
})( jQuery );

function sendRequest(pageNum, isAscending) {
    $.post(
        dataUrl,
        {
            rowsamo: rowsPerPage,
            page: pageNum,
            sortbyfield : sortableColumn,
            order: isAscending,
            filters: filters,
        },
        function(data) {
            handleResponse(data);
        });
}

function handleResponse(data) {
    if (data.success) {
        if (data.items.length > 0) {
            addTableBody(data.items);
            addPagination(data.pagesAmo);
        }
    }
}

function addDateContainer(columns) {
    var buttons = '<button onClick="resetFilters()">Reset filters</button> <button onClick="applyFilters()">Apply filters</button>'
    var table = buttons + '<table class="table"><thead><tr>';
    for (var k in columns) {
        table += '<td><a name="' + sortableColumns[k] + '" onClick="setSortableColumn(\'' + sortableColumns[k] + '\')">' + columns[k] + '</a><br><input id="' + sortableColumns[k] + '"></td>';
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

    isAscending = !isAscending;
    //params += getFilteringParams(filterableColumns[0], 'ROLE_ADMIN');
    sendRequest(pageNum, (isAscending ? 'asc' : 'desc'));
}

function setSortableColumn(value) {
    sortableColumn = value;
    getPage(1);
}

String.prototype.isEmpty = function() {
    return (this.length === 0 || !this.trim());
};

function applyFilters()
{

    for (var k in sortableColumns) {
        var value = $('#' + sortableColumns[k]).val();
        if (value !== undefined) {
            if (!value.isEmpty()) {
                filters[k] = new Array(sortableColumns[k], value);
            }
        }
    }
    getPage(1);
}

function resetFilters()
{
    filters = [];
    for (var k in sortableColumns) {
        $('#' + sortableColumns[k]).val("");

    }
}

