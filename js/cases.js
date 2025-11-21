document.addEventListener('DOMContentLoaded', function(){
  function loadCasesUrl(href){
    fetch(href, { headers:{ 'X-Requested-With':'XMLHttpRequest' }})
      .then(function(r){ return r.text(); })
      .then(function(html){
        var parser = new DOMParser();
        var doc = parser.parseFromString(html, 'text/html');
        var newGrid = doc.querySelector('.cases-grid');
        var newPag = doc.querySelector('.pagination');
        var curGrid = document.querySelector('.cases-grid');
        var curPag = document.querySelector('.pagination');
        if(newGrid && curGrid){ curGrid.replaceWith(newGrid); }
        if(newPag){
          if(curPag){ curPag.replaceWith(newPag); }
        } else if(curPag){
          curPag.remove();
        }
        if(window.history && window.history.pushState){
          window.history.pushState(null, '', href);
        }
        wireCasesPagination();
      });
  }

  function wireCasesPagination(){
    var pag = document.querySelector('.pagination');
    if(!pag){ return; }
    pag.addEventListener('click', function(e){
      var a = e.target.closest('a');
      if(!a || !pag.contains(a)){ return; }
      var href = a.getAttribute('href');
      if(!href || href.charAt(0) === '#'){ return; }
      e.preventDefault();
      loadCasesUrl(href);
    }, { once:true });
  }

  function wireCasesFilter(){
    var form = document.getElementById('casesFilterForm');
    var resultSel = document.getElementById('resultFilter');
    var severitySel = document.getElementById('severityFilter');
    var vaccineSel = document.getElementById('vaccineFilter');
    var labSel = document.getElementById('labFilter');
    var yearSel = document.getElementById('yearFilter');
    if(!form || !resultSel || !severitySel || !vaccineSel || !labSel || !yearSel){ return; }

    function applyFilters(){
      var base = window.location.pathname;
      var params = [];
      var rVal = resultSel.value || 'all';
      var sVal = severitySel.value || 'all';
      var vVal = vaccineSel.value || 'all';
      var lVal = labSel.value || 'all';
      var yVal = yearSel.value || 'all';
      if(rVal !== 'all'){
        params.push('result=' + encodeURIComponent(rVal));
      }
      if(sVal !== 'all'){
        params.push('severity=' + encodeURIComponent(sVal));
      }
      if(vVal !== 'all'){
        params.push('vaccine=' + encodeURIComponent(vVal));
      }
      if(lVal !== 'all'){
        params.push('lab=' + encodeURIComponent(lVal));
      }
      if(yVal !== 'all'){
        params.push('year=' + encodeURIComponent(yVal));
      }
      // Always reset to page 1 when filters change
      params.push('page=1');
      var href = base + (params.length ? ('?' + params.join('&')) : '');
      loadCasesUrl(href);
    }

    resultSel.addEventListener('change', function(e){ e.preventDefault(); applyFilters(); });
    severitySel.addEventListener('change', function(e){ e.preventDefault(); applyFilters(); });
    vaccineSel.addEventListener('change', function(e){ e.preventDefault(); applyFilters(); });
    labSel.addEventListener('change', function(e){ e.preventDefault(); applyFilters(); });
    yearSel.addEventListener('change', function(e){ e.preventDefault(); applyFilters(); });
  }

  wireCasesPagination();
  wireCasesFilter();
});