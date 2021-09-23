/* fs-matches */



//ready
$(function(){
	//fs-matches - click
	$('[fs-matches]').click(function(e){
		_fsMatches($(this).attr('fs-matches'));
	});

	//fs-matches - event
	$(document).on('fs-matches', function(e, date){
		_fsMatches(date);
	});

	//fs-fetch poll
	_fsFetchPoll();
});

//_fsFetchPoll
function _fsFetchPoll(){
	if (!$('#fs-fetch-status').length) return console.debug('not updating');
	let fs_date = _fsMatchesDate();
	let url = `/?date=${fs_date}&fetch-status`;
	console.debug('check fetch status');
	_get(url).then(res => {
		let status = res.data.status;
		if (!_isNull(status)){
			setTimeout(() => _fsFetchPoll(), 3000);
		}
		else _fsUpdateTable();
	})
	.catch(err => console.error(url, err));
}

//_fsUpdateTable
function _fsUpdateTable(){
	let fs_date = _fsMatchesDate();
	let url = `/?date=${fs_date}&fetch-table`;
	_get(url).then(res => {
		let fs_table = _fsMatchesTable();
		if (!fs_table.length) return console.error('Undefined fs table!');
		let div = document.createElement('div');
		div.innerHTML = res.data;
		if (!div.children) return console.error('Invalid update table html response!', res);
		fs_table[0].parentNode.parentNode.innerHTML = div.children[0].innerHTML;
	})
	.catch(err => console.error(url, err));
}

//_fsMatchesTable
function _fsMatchesTable(){
	let table = $('#fs-matches-table');
	if (!table.length) return console.error('Error _fsMatchesTable: Undefined table!');
	return table;
}

//_fsMatchesDate
function _fsMatchesDate(){
	let table = _fsMatchesTable();
	return table ? table.attr('data-date') : null;
}

//_fsMatches
function _fsMatches(date){
	if (!('string' === typeof date && date.length == 10)) return;
	let curr = _fsMatchesDate();
	if (date == curr) return _scrollTop('#fs-matches'); //todo update current
	let url = _location(['query', 'hash']).toString().replace(/\/\s*$/, '') + '/?date=' + date;
	return _goto(url);
}
