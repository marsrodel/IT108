document.addEventListener('DOMContentLoaded', function(){
  function loadPatientsUrl(href){
    fetch(href, { headers:{ 'X-Requested-With':'XMLHttpRequest' }})
      .then(function(r){ return r.text(); })
      .then(function(html){
        var parser = new DOMParser();
        var doc = parser.parseFromString(html, 'text/html');
        var newGrid = doc.querySelector('.patients-grid');
        var newPag  = doc.querySelector('.pagination');
        var curGrid = document.querySelector('.patients-grid');
        var curPag  = document.querySelector('.pagination');
        if(newGrid && curGrid){ curGrid.replaceWith(newGrid); }
        if(newPag){
          if(curPag){
            curPag.replaceWith(newPag);
          } else {
            // No existing pagination: append it after the patients grid container
            var container = document.querySelector('.card-body');
            if(container){ container.appendChild(newPag); }
          }
        } else if(curPag){
          curPag.remove();
        }
        if(window.history && window.history.pushState){
          window.history.pushState(null, '', href);
        }
        wirePatientsPagination();
        wirePatientsFilter();
      });
  }

  function wirePatientsPagination(){
    var pag = document.querySelector('.pagination');
    if(!pag){ return; }
    pag.addEventListener('click', function(e){
      var a = e.target.closest('a');
      if(!a || !pag.contains(a)){ return; }
      var href = a.getAttribute('href');
      if(!href || href.charAt(0) === '#'){ return; }
      e.preventDefault();
      loadPatientsUrl(href);
    }, { once:true });
  }

  function wirePatientsFilter(){
    var form = document.getElementById('patientsFilterForm');
    var input = document.getElementById('nameSearch');
    var genderSel = document.getElementById('genderFilter');
    if(!form || !input || !genderSel){ return; }

    var debounceTimer = null;

    function applyFilter(){
      var base = window.location.pathname;
      var params = [];
      var q = input.value.trim();
      var g = genderSel.value || 'all';
      if(q !== ''){
        params.push('q=' + encodeURIComponent(q));
      }
      if(g !== 'all'){
        params.push('gender=' + encodeURIComponent(g));
      }
      // Always go back to page 1 on new search
      params.push('page=1');
      var href = base + (params.length ? ('?' + params.join('&')) : '');
      loadPatientsUrl(href);
    }

    form.addEventListener('submit', function(e){
      e.preventDefault();
      applyFilter();
    });

    // Trigger search when user presses Enter, clears input, or pauses typing
    input.addEventListener('keydown', function(e){
      if(e.key === 'Enter'){
        e.preventDefault();
        applyFilter();
      }
    });
    input.addEventListener('change', function(){ applyFilter(); });
    input.addEventListener('keyup', function(){
      if(debounceTimer){ clearTimeout(debounceTimer); }
      debounceTimer = setTimeout(function(){ applyFilter(); }, 350);
    });
    genderSel.addEventListener('change', function(e){ e.preventDefault(); applyFilter(); });
  }

  wirePatientsPagination();
  wirePatientsFilter();
});