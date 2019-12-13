(function(w,d){
  var priceTable = {};

  var getObjectValues = function(obj) {
    if (!obj) return [];

    return Object.keys(obj).map(function(key) {
      return obj[key];
    });
  }

  var template = function(rows, tarifs, note){
    return '<div class="tarifs">'+tarifs+'</div>'+'<table class="table-bordered-auto">'+
      '<thead><tr><th>Категория</th><th>Роза Хутор</th><th>Красная поляна</th><th>ГТЦ Газпром</th><th>Единый</th></tr></thead>'+
      '<tbody>'+
        rows +
      '</tbody>'+
    '</table>'+
    '<div class="note">'+note+'</div>'
  };

  var loadedPrice = {};

  priceTable.init = function(opts){
    var selector = opts.selector || "#priceTable";
    var el = d.querySelector(selector);
    var data = el.dataset;
    var date = new Date(),
        dateArray = [
          date.getFullYear(),
          ('0' + (date.getMonth() + 1)).slice(-2),
          ('0' + date.getDate()).slice(-2)
        ],
        dateStr = dateArray.join('-');

    makeRequest(dateStr, data.api);

    var calendar = d.querySelector(data.calendar);
    calendar.addEventListener('input', function(e){
      makeRequest(e.target.value, data.api);
    });
    calendar.value = dateStr;
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

      loadedPrice = JSON.parse(this.responseText);
      render(loadedPrice);
      d.querySelector('.tarifs__button[data-id="1"] span').click();
    }
  }

  function changeTarif(id){
    if (loadedPrice.tarif_notes) {
      var data = {
        prices : loadedPrice.prices[id],
        notes : loadedPrice.tarif_notes[id]
      };
    } else {
      var data = {
        prices : loadedPrice.prices[id]
      };
    }
    renderTable(data);
  }

  function renderTable(data){
    var rows = getObjectValues(data.prices).map(function(row) {
      return '<tr>' + getObjectValues(row).map(function(cell) {
        return '<td>' + cell + '</td>';
      }).join('') + '</tr>';
    }).join('');
    var selector = "#priceTable";
    var el = d.querySelector(selector + ' table tbody');
    el.innerHTML = rows;
    if(data.notes){
      var elNote = d.querySelector(selector + ' .note');
      elNote.innerHTML = '<h3>Примечание к тарифу</h3>'+
      (data.notes.rosaski ? '<h4>Роза Хутор</h4>' + data.notes.rosaski + '<br/><br/>' : '') +
      (data.notes.krasnajapoljana ? '<h4>Красная поляна</h4>' + data.notes.krasnajapoljana + '<br/><br/>' : '') +
      (data.notes.gazprom ? '<h4>ГТЦ Газпром</h4>' + data.notes.gazprom + '<br/><br/>' : '') +
      (data.notes.jedinij ? '<h4>Единый</h4>' + data.notes.jedinij + '<br/><br/>' : '');
    }
  }

   function render(data){
     var init_prices = getObjectValues(data.prices[2]);

     if (data.tarif_notes){
       var init_notes = data.tarif_notes[2];
     }

     var rows = init_prices.map(function(row) {
       return '<tr>' + getObjectValues(row).map(function(cell) {
         return '<td>' + cell + '</td>';
       }).join('') + '</tr>';
     }).join('');

     var tarifs = getObjectValues( data.tarif ).map(function(tarif, index) {
       return '<span class="tarifs__button" data-id="'+(index+1)+'"><span>'+tarif+'</span></span>';
     }).join('');

     if( init_notes ){
       var note = '<h3>Примечание тарифа</h3>'+
       (init_notes.rosaski ? '<strong>Роза Хутор</strong><br/>'+
       init_notes.rosaski+'<br/><br/>' : '')+
       (init_notes.krasnajapoljana ? '<strong>Красная поляна</strong><br/>'+
       init_notes.krasnajapoljana+'<br/><br/>' : '')+
       (init_notes.gazprom ? '<strong>ГТЦ Газпром</strong><br/>'+
       init_notes.gazprom+'<br/><br/>' : '') +
       (init_notes.jedinij ? '<strong>Единый</strong><br/>'+
       init_notes.jedinij+'<br/><br/>' : '');
     }else{
       var note = '';
     }

     var tpl = template(rows, tarifs, note);
     var selector = "#priceTable";
     var el = d.querySelector(selector);
     el.innerHTML = tpl;

     function activateTarif(e) {
       window.yvh = e;
       if (e.target.parentElement.className == "tarifs__button") {
         var active = document.querySelector('.tarifs .active');
         active && active.classList.remove('active');
         e.target.parentElement.classList.add('active');
         changeTarif(e.target.parentElement.dataset.id);
       }
     }

     var tarifsEl = document.getElementsByClassName('tarifs__button');
     console.log(tarifsEl);
     for (var i = 0; i < tarifsEl.length; i++) {
       var tarifElem = tarifsEl[i];
       console.log(tarifElem);
       tarifElem.addEventListener('click', activateTarif);
       tarifElem.addEventListener('touchstart', activateTarif);
     }
   }

  window.priceTable = priceTable;

}(window,document));

priceTable.init({selector:'#priceTable'});
