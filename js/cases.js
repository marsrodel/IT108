document.addEventListener('DOMContentLoaded', function(){
    function wireCasesPagination(){
      var pag = document.querySelector('.pagination');
      if(!pag){ return; }
      pag.addEventListener('click', function(e){
        var a = e.target.closest('a');
        if(!a || !pag.contains(a)){ return; }
        var href = a.getAttribute('href');
        if(!href || href.charAt(0) === '#'){ return; }
        e.preventDefault();
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
            if(newPag && curPag){ curPag.replaceWith(newPag); }
            if(window.history && window.history.pushState){
              window.history.pushState(null, '', href);
            }
            wireCasesPagination();
          });
      }, { once:true });
    }
    wireCasesPagination();
  });