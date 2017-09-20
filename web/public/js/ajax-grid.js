var dataUrl, sortableColumns, filterableColumns, rowsPerPage, sortableColumn, isAscending, filters;
var currentPage;

(function( $ ){
    $.fn.ajaxgrid = function({columnsNames, url, sortColumns, filterColumns, rowsPerPageAmo}) {
        dataUrl = url;
        sortableColumns = sortColumns;
        filterableColumns = filterColumns;
        rowsPerPage = rowsPerPageAmo;
        isAscending = true;
        sortableColumn = sortableColumns[0];
        filters = new Array();
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
    var buttons = '<div class="text-right"><button class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--accent" onClick="resetFilters()">Reset filters</button> <button class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--colored" onClick="applyFilters()">Apply filters</button></div>'
    var table = buttons + '<table class="mdl-data-table mdl-js-data-table  mdl-cell mdl-cell--12-col"><thead><tr>';
    for (var k in columns) {
        table += '<th class="mdl-data-table__cell--non-numeric"><a class="active-href text-14" name="' + sortableColumns[k] + '" onClick="setSortableColumn(\'' + sortableColumns[k] + '\')">' + columns[k] + '</a><br><div class="mdl-textfield mdl-js-textfield"><input class="mdl-textfield__input" type="text" id="' + sortableColumns[k] + '" ><label class="mdl-textfield__label" for="' + sortableColumns[k] + '">' + columns[k] + '</label> </div></th>';
    }
    table += '</tr></thead><tbody id="table-body"></tbody></table>';
    $("#entities-grid").append(table);
    var pagination = '<div class="navigation text-center"><ul id="pagination" class="pagination"></ul></div>';
    $("#entities-grid").append(pagination);
}

function addTableBody(items) {
    var tableBody = '';
    for (var i = 0; i < items.length; i++) {
        var href = '/admin/users/' + items[i][0] + '/edit' ;
        tableBody += '<tr onclick="window.location.href=\'' + href + '\'; return false">';
        for (var j = 1; j < items[i].length; j++) {
            tableBody += '<td class="mdl-data-table__cell--non-numeric">' + items[i][j] + '</td>';
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
    sendRequest(pageNum, (isAscending ? 'asc' : 'desc'));
}

function setSortableColumn(value) {
    sortableColumn = value;
    isAscending = !isAscending;
    getPage(1);
}

String.prototype.isEmpty = function() {
    return (this.length === 0 || !this.trim());
};

function applyFilters()
{
    var currentFilterNumber = 0;
    filters = [];
    for (var k in sortableColumns) {
        var value = $('#' + sortableColumns[k]).val();
        if (value !== undefined) {
            if (!value.isEmpty()) {
                filters[currentFilterNumber++] = new Array(sortableColumns[k], value);
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
    getPage(1);
}



