(function(w,d){

  var priceWidget = {};

  var template = function(rows){
  	
  		return '<table>'+
			'<thead><tr><th>Курорт</th><th>взр.</th><th>дет.</th></tr></thead>'+
			'<tbody>'+
				rows +
			'</tbody>'+
		'</table>'+
		''};

  priceWidget.init = function(opts){
    var selector = opts.selector || "#priceWidget";
    var el = d.querySelector(selector);
    var data = el.dataset;
    d.querySelector(data.calendar).addEventListener('input',function(e){
      makeRequest(e.target.value, data.api);
    });
  }

  function makeRequest(date, api){
  	
		var xhr = new XMLHttpRequest();
		xhr.open('GET', api + date, true);
		xhr.send();
		xhr.onreadystatechange = function() {
	  	if (this.readyState != 4) return;
	    if (this.status != 200) {
		 alert( 'ошибка: ' + (this.status ? this.statusText : 'запрос не удался') );
		 return;
		}

		render(JSON.parse(this.responseText));
		}
	}


   function render(data){

   		var rows = data.map(function(row){
   			return '<tr>'+row.map(function(cell){
          cell = cell.toString();
          cell = cell.replace('rosaski', 'Роза Хутор');
          cell = cell.replace('krasnajapoljana', 'Красная поляна');
          cell = cell.replace('gazprom', 'Газпром');
          cell = cell.replace('jedinij', 'Единый');
   				return '<td>'+cell+'</td>';
   			}).join('')+'</tr>';
   		}).join('');
   		var tpl = template(rows);
   		var selector = "#priceWidget";
    	var el = d.querySelector(selector);
    	el.innerHTML = tpl;

   }

  

  window.priceWidget = priceWidget;

}(window,document));

priceWidget.init({selector:'#priceWidget'});
