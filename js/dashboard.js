// Render charts on dashboard using Chart.js and modal enlarge
(function(){
  function qs(sel){return document.querySelector(sel);} 
  function qsa(sel){return document.querySelectorAll(sel);} 

  // Modal logic
  var modal = null;
  function ensureModal(){
    if(!modal){
      modal = document.createElement('div');
      modal.className = 'modal';
      modal.innerHTML = ''+
        '<div class="modal-content">'+
          '<div class="modal-header">'+
            '<div class="modal-title" id="modalTitle">Chart</div>'+
            '<button class="modal-close">Close</button>'+
          '</div>'+
          '<div class="modal-toolbar" id="modalToolbar"></div>'+
          '<canvas id="modalCanvas" width="1200" height="420" style="max-height:420px"></canvas>'+
        '</div>';
      document.body.appendChild(modal);
      modal.addEventListener('click', function(e){ if(e.target.classList.contains('modal') || e.target.classList.contains('modal-close')){ modal.classList.remove('open'); }});
    }
    return modal;
  }

  function renderSeverityInModal(status){
    var dest = qs('#modalCanvas');
    if(!dest){ return; }
    var st = status || 'all';
    var url = '../server/severity_distribution.php';
    if(st){ url += ('?status=' + encodeURIComponent(st)); }
    fetch(url)
      .then(function(r){ return r.json(); })
      .then(function(payload){
        var labels = payload.labels || [];
        var data = payload.counts || [];
        var colorMap = {
          'Mild':'#2fa85f',
          'Moderate':'#10b3a1',
          'Severe':'#f39c2d',
          'Critical':'#e34d4a'
        };
        var colors = labels.map(function(l){ return colorMap[l] || '#cccccc'; });
        if(dest._chartInstance){ dest._chartInstance.destroy(); }
        dest._chartInstance = new Chart(dest.getContext('2d'), {
          type: 'bar',
          data: {
            labels: labels,
            datasets: [{
              label: 'Cases',
              data: data,
              backgroundColor: colors,
              borderColor: colors,
              borderWidth: 1,
              borderRadius: 8,
              maxBarThickness: 60,
              categoryPercentage: 0.6,
              barPercentage: 0.7
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            layout:{ padding:{ top:16,right:24,bottom:16,left:24 }},
            plugins:{
              legend:{ display:false },
              tooltip:{
                callbacks:{
                  label:function(context){
                    var v = context.parsed.y || 0;
                    var label = context.label || '';
                    return label + ': ' + v.toLocaleString() + ' cases';
                  }
                }
              }
            },
            scales:{
              x:{
                ticks:{ color:'#3e5960' },
                grid:{ display:false }
              },
              y:{
                beginAtZero:true,
                ticks:{ color:'#3e5960' },
                grid:{ color:'rgba(15,118,110,0.08)' }
              }
            }
          }
        });
      });
  }

  function renderCasesInModal(year){
    var dest = qs('#modalCanvas');
    if(!dest){ return; }
    var url = '../server/cases_over_time.php';
    if (year && year !== 'all'){
      url += ('?year=' + encodeURIComponent(year));
    }
    fetch(url)
      .then(function(r){ return r.json(); })
      .then(function(rows){
        var labels = rows.map(function(x){ return x.label; });
        var data = rows.map(function(x){ return x.count; });
        if(dest._chartInstance){ dest._chartInstance.destroy(); }
        dest._chartInstance = new Chart(dest.getContext('2d'), {
          type:'line',
          data:{
            labels: labels,
            datasets:[{
              label:'Cases in this year',
              data:data,
              borderColor:'#0b9b9b',
              backgroundColor:'rgba(16,179,161,.15)',
              tension:.3,
              pointRadius:3
            }]
          },
          options:{
            responsive:true,
            maintainAspectRatio:false,
            plugins:{ legend:{ display:false }},
            scales:{
              x:{ ticks:{ color:'#3e5960'}},
              y:{ ticks:{ color:'#3e5960'}}
            }
          }
        });
      });
  }

  function openModal(canvas){
    ensureModal();
    modal.classList.add('open');

    var titleEl = qs('#modalTitle');
    var toolbar = qs('#modalToolbar');
    var dest = qs('#modalCanvas');
    if(!dest){ return; }

    // If severity chart, render dedicated bar chart with its own status filter
    if (canvas && canvas.id === 'severityDist'){
      if(titleEl){ titleEl.textContent = 'Severity Distribution'; }
      if(toolbar){
        toolbar.innerHTML = '';
        var label = document.createElement('label');
        label.textContent = 'Status:';
        label.style.fontWeight = '600';
        label.style.color = 'var(--muted)';
        var sel = document.createElement('select');
        sel.id = 'modalStatus';
        sel.innerHTML = '<option value="all">All</option><option value="positive">Positive</option><option value="negative">Negative</option>';
        sel.className = 'status-select';
        toolbar.appendChild(label);
        toolbar.appendChild(sel);

        // Default to main dropdown selection if present
        var mainSel = qs('#statusFilter');
        if(mainSel){ sel.value = mainSel.value || 'all'; }

        sel.onchange = function(){ renderSeverityInModal(sel.value); };
      }
      renderSeverityInModal(qs('#modalStatus') ? qs('#modalStatus').value : 'all');
      return;
    }

    // Cases Over Time: render its own yearly chart in the modal
    if (canvas && canvas.id === 'casesOverTime'){
      if(titleEl){ titleEl.textContent = 'Cases Over Time (Positive)'; }
      if(toolbar){
        toolbar.innerHTML = '';
        var label = document.createElement('label');
        label.textContent = 'Year:';
        label.style.fontWeight = '600';
        label.style.color = 'var(--muted)';
        var sel = document.createElement('select');
        sel.id = 'modalYear';
        sel.className = 'status-select';
        sel.innerHTML = '<option value="all">All</option>'+
                        '<option value="2020">2020</option>'+
                        '<option value="2021">2021</option>'+
                        '<option value="2022">2022</option>'+
                        '<option value="2023">2023</option>'+
                        '<option value="2024">2024</option>'+
                        '<option value="2025">2025</option>';
        toolbar.appendChild(label);
        toolbar.appendChild(sel);

        // Default to main year selection if present
        var mainYear = qs('#yearFilter');
        if(mainYear){ sel.value = mainYear.value || 'all'; }

        sel.onchange = function(){ renderCasesInModal(sel.value); };
      }
      renderCasesInModal(qs('#modalYear') ? qs('#modalYear').value : 'all');
      return;
    }

    // Default behavior: copy chart config
    if(titleEl){ titleEl.textContent = 'Chart'; }
    if(toolbar){ toolbar.innerHTML = ''; }
    var srcChart = canvas._chartInstance;
    if (srcChart && dest){
      if(dest._chartInstance){ dest._chartInstance.destroy(); }
      var cfg = JSON.parse(JSON.stringify(srcChart.config));
      dest._chartInstance = new Chart(dest.getContext('2d'), cfg);
    }
  }

  function attachEnlarge(canvas){
    canvas.classList.add('chart-clickable');
    canvas.addEventListener('click', function(){ openModal(canvas); });
  }

  function render(){
    var ct = qs('#casesOverTime');
    var sd = qs('#severityDist');
    var statusSel = qs('#statusFilter');
    var yearSel = qs('#yearFilter');
    if(!ct || !sd){ return; }

    var casesUrl = '../server/cases_over_time.php';
    if (yearSel && yearSel.value && yearSel.value !== 'all'){
      casesUrl += ('?year=' + encodeURIComponent(yearSel.value));
    }

    fetch(casesUrl)
      .then(function(r){return r.json();})
      .then(function(rows){
        var labels = rows.map(function(x){return x.label;});
        var data = rows.map(function(x){return x.count;});
        var ctx = ct.getContext('2d');
        if(ct._chartInstance){ ct._chartInstance.destroy(); }
        ct._chartInstance = new Chart(ctx, {
          type: 'line',
          data: { labels: labels, datasets: [{ label: 'Cases in this year', data: data, borderColor: '#0b9b9b', backgroundColor: 'rgba(16,179,161,.15)', tension: .3, pointRadius: 3 }] },
          options: { responsive: true, maintainAspectRatio: false, plugins:{ legend:{ display:false }}, scales:{ x:{ ticks:{ color:'#3e5960'}}, y:{ ticks:{ color:'#3e5960'}} } }
        });
        attachEnlarge(ct);
      });

    var status = statusSel ? statusSel.value : 'all';
    var url = '../server/severity_distribution.php';
    if(status){ url += ('?status=' + encodeURIComponent(status)); }
    fetch(url)
      .then(function(r){return r.json();})
      .then(function(payload){
        var labels = payload.labels || [];
        var data = payload.counts || [];
        var colorMap = {
          'Mild':'#2fa85f',
          'Moderate':'#10b3a1',
          'Severe':'#f39c2d',
          'Critical':'#e34d4a',
          'Unknown':'#9aa7ad'
        };
        var colors = labels.map(function(l){ return colorMap[l] || '#cccccc'; });
        var ctx = sd.getContext('2d');
        if(sd._chartInstance){ sd._chartInstance.destroy(); }
        sd._chartInstance = new Chart(ctx, {
          type: 'pie',
          data: { labels: labels, datasets: [{ data: data, backgroundColor: colors }] },
          options: { responsive: true, maintainAspectRatio: false }
        });
        attachEnlarge(sd);
      });
  }

  document.addEventListener('DOMContentLoaded', render);
  document.addEventListener('DOMContentLoaded', function(){
    var statusSel = qs('#statusFilter');
    var yearSel = qs('#yearFilter');
    if(statusSel){
      statusSel.addEventListener('change', function(){ render(); });
    }
    if(yearSel){
      yearSel.addEventListener('change', function(){ render(); });
    }

    // Toggle Labs table when Labs card is clicked
    var labsCard = qs('.labs-card');
    var labsSection = document.getElementById('labs-section');
    var vaccinesSection = document.getElementById('vaccines-section');
    var locationsSection = document.getElementById('locations-section');
    if(labsCard && labsSection){
      labsCard.addEventListener('click', function(e){
        e.preventDefault();
        var current = window.getComputedStyle(labsSection).display;
        if(current === 'none'){
          labsSection.style.display = 'block';
          labsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
          if(vaccinesSection){ vaccinesSection.style.display = 'none'; }
          if(locationsSection){ locationsSection.style.display = 'none'; }
        } else {
          labsSection.style.display = 'none';
        }
      });
    }

    // Toggle Vaccines table when Vaccines card is clicked
    var vaccinesCard = qs('.vaccines-card');
    vaccinesSection = document.getElementById('vaccines-section');
    if(vaccinesCard && vaccinesSection){
      vaccinesCard.addEventListener('click', function(e){
        e.preventDefault();
        var current = window.getComputedStyle(vaccinesSection).display;
        if(current === 'none'){
          vaccinesSection.style.display = 'block';
          vaccinesSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
          if(labsSection){ labsSection.style.display = 'none'; }
          if(locationsSection){ locationsSection.style.display = 'none'; }
        } else {
          vaccinesSection.style.display = 'none';
        }
      });
    }

    // Toggle Locations table when Covered Locations card is clicked
    var locationsCard = qs('.locations-card');
    if(locationsCard && locationsSection){
      locationsCard.addEventListener('click', function(e){
        e.preventDefault();
        var current = window.getComputedStyle(locationsSection).display;
        if(current === 'none'){
          locationsSection.style.display = 'block';
          locationsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
          if(labsSection){ labsSection.style.display = 'none'; }
          if(vaccinesSection){ vaccinesSection.style.display = 'none'; }
        } else {
          locationsSection.style.display = 'none';
        }
      });
    }

    // See Growth Rate button -> open Growth Rate modal
    var growthBtn = document.getElementById('seeGrowthRateBtn');
    var growthModal = document.getElementById('growthModal');
    var growthClose = growthModal ? growthModal.querySelector('.modal-growth-close') : null;

    function closeGrowthModal(){
      if(growthModal){ growthModal.classList.remove('open'); }
      document.body.style.overflow = '';
    }

    function openGrowthModal(){
      if(!growthModal){ return; }
      growthModal.classList.add('open');
      document.body.style.overflow = 'hidden';

      // Fetch yearly + monthly growth data
      fetch('../server/growth_rate.php')
        .then(function(r){ return r.json(); })
        .then(function(payload){
          var yearly = payload.yearly || [];
          var monthly = payload.monthly || [];
          var yBody = document.getElementById('growthYearlyBody');
          var mBody = document.getElementById('growthMonthlyBody');
          if(yBody){
            yBody.innerHTML = '';
            yearly.forEach(function(row){
              var tr = document.createElement('tr');
              tr.innerHTML = '<td>' + row.period + '</td>'+
                             '<td>' + row.total_positive_case.toLocaleString() + '</td>'+
                             '<td>' + (row.previous_positive_case !== null ? row.previous_positive_case.toLocaleString() : '—') + '</td>'+
                             '<td>' + (row.growth_rate !== null ? row.growth_rate + '%' : '—') + '</td>';
              yBody.appendChild(tr);
            });
          }
          if(mBody){
            mBody.innerHTML = '';
            monthly.forEach(function(row){
              var tr = document.createElement('tr');
              tr.innerHTML = '<td>' + row.period + '</td>'+
                             '<td>' + row.total_positive_case.toLocaleString() + '</td>'+
                             '<td>' + (row.previous_positive_case !== null ? row.previous_positive_case.toLocaleString() : '—') + '</td>'+
                             '<td>' + (row.growth_rate !== null ? row.growth_rate + '%' : '—') + '</td>';
              mBody.appendChild(tr);
            });
          }
        })
        .catch(function(err){ console.error('Error loading growth data', err); });
    }

    if(growthBtn && growthModal){
      growthBtn.addEventListener('click', function(e){
        e.preventDefault();
        openGrowthModal();
      });
    }

    if(growthClose){
      growthClose.addEventListener('click', function(){ closeGrowthModal(); });
    }

    if(growthModal){
      growthModal.addEventListener('click', function(e){
        if(e.target === growthModal){ closeGrowthModal(); }
      });
      document.addEventListener('keydown', function(e){
        if(e.key === 'Escape' && growthModal.classList.contains('open')){
          closeGrowthModal();
        }
      });
    }
  });
})();
