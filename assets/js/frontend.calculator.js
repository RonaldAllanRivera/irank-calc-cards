(function(){
  function $(sel,root){ return (root||document).querySelector(sel); }
  function $all(sel,root){ return Array.prototype.slice.call((root||document).querySelectorAll(sel)); }
  function clamp(v,min,max){ return Math.max(min, Math.min(max, v)); }
  function fmt(n){ return Math.round(n); }

  function animateNumber(el, to){
    var from = parseFloat(el.getAttribute('data-from')||el.textContent)||0;
    var start = null; var dur = 300; to = +to;
    function step(ts){ if(!start) start = ts; var p = clamp((ts-start)/dur,0,1); var val = from + (to-from)*p; el.textContent = fmt(val); if(p<1) requestAnimationFrame(step); else el.setAttribute('data-from', to); }
    requestAnimationFrame(step);
  }

  function uuid(){ try{ return sessionStorage.getItem('irank_uuid') || (function(){ var s=(Date.now()+Math.random()).toString(36); sessionStorage.setItem('irank_uuid', s); return s; })(); }catch(e){ return String(Date.now()); } }

  function trackEvent(data){
    try{
      var url = (window.wp && window.wp.url && window.wp.url.addQueryArgs) ? '/wp-json/irank/v1/track' : '/wp-json/irank/v1/track';
      var fd = new FormData();
      for(var k in data){ fd.append(k, data[k]); }
      if(navigator.sendBeacon){ navigator.sendBeacon(url, fd); }
      else { fetch(url,{method:'POST',body:fd,credentials:'same-origin'}); }
    }catch(e){}
  }

  function initCalc(section){
    var unit = section.getAttribute('data-unit')||'lbs';
    var factor = parseFloat(section.getAttribute('data-loss-factor'))||0.15;
    var pageId = section.getAttribute('data-page-id')||'';

    var slider = $('.irank-calc__slider', section);
    var weightEl = $('.irank-calc__weight', section);
    var lossEl = $('.irank-calc__loss-val', section);
    var cta = $('.irank-calc__cta', section);
    var overlay = $('.irank-calc__overlay', section);
    var overlayClose = $('.irank-calc__overlay-close', section);
    var resWeight = $('.irank-calc__res-weight', section);
    var resLoss = $('.irank-calc__res-loss', section);
    var ba = $('.irank-calc__ba', section);
    var baHandle = $('.irank-calc__ba-handle', section);

    var min = parseFloat(slider.min||0), max = parseFloat(slider.max||100);

    function update(){
      var w = parseFloat(slider.value||min);
      var loss = w * factor;
      weightEl.textContent = fmt(w);
      animateNumber(lossEl, loss);
      var pct = (w-min)/(max-min); pct = isFinite(pct)?pct:0.5;
      ba.style.setProperty('--ba', (pct*100).toFixed(2)+'%');
      sessionStorage.setItem('irank_calc_weight', String(w));
      sessionStorage.setItem('irank_calc_loss', String(loss));
      updateLabelVisibility();
    }

    slider.addEventListener('input', update);
    update();

    function openOverlay(){
      var w = parseFloat(slider.value||min); var loss = w*factor;
      resWeight.textContent = fmt(w);
      resLoss.textContent = fmt(loss);
      overlay.hidden = false; overlay.setAttribute('aria-hidden','false'); document.body.classList.add('irank-no-scroll');
      trackEvent({weight:w, loss:loss, page_id:pageId, session_id:uuid(), referrer:document.referrer||''});
    }
    function closeOverlay(){ overlay.hidden = true; overlay.setAttribute('aria-hidden','true'); document.body.classList.remove('irank-no-scroll'); }

    cta && cta.addEventListener('click', function(e){ e.preventDefault(); openOverlay(); });
    overlayClose && overlayClose.addEventListener('click', function(){ closeOverlay(); });
    overlay && overlay.addEventListener('click', function(e){ if(e.target===overlay) closeOverlay(); });
    document.addEventListener('keydown', function(e){ if(e.key === 'Escape' && !overlay.hidden){ closeOverlay(); } });

    // Before/After reveal helpers
    function getBA(){ var val = parseFloat((ba && ba.style.getPropertyValue('--ba') || '50%').replace('%',''))||50; return clamp(val,0,100); }
    function updateLabelVisibility(){
      var beforeBtn = $('.irank-calc__label--before', section);
      var afterBtn  = $('.irank-calc__label--after', section);
      if(!(beforeBtn||afterBtn)) return;
      var pct = getBA();
      var hideBefore = pct <= 8;   // near left edge
      var hideAfter  = pct >= 92;  // near right edge
      if(beforeBtn){ beforeBtn.hidden = !!hideBefore; }
      if(afterBtn){ afterBtn.hidden = !!hideAfter; }
    }
    function setBA(pct){ pct = clamp(pct,0,100); if(ba){ ba.style.setProperty('--ba', pct+'%'); } if(baHandle){ baHandle.setAttribute('aria-valuenow', String(Math.round(pct))); } updateLabelVisibility(); }
    function animateBA(target){
      var from = getBA(); var to = clamp(target,0,100); var start=null, dur=250;
      function step(ts){ if(!start) start=ts; var p=Math.min(1,(ts-start)/dur); setBA(from + (to-from)*p); if(p<1) requestAnimationFrame(step); }
      requestAnimationFrame(step);
    }

    // Keyboard BA handle: left/right move clip
    if(baHandle){
      baHandle.addEventListener('keydown', function(e){
        var val = getBA();
        if(e.key==='ArrowLeft'){ setBA(val-5); e.preventDefault(); }
        if(e.key==='ArrowRight'){ setBA(val+5); e.preventDefault(); }
      });
    }

    // Click Before/After labels to move slider
    var beforeBtn = $('.irank-calc__label--before', section);
    var afterBtn  = $('.irank-calc__label--after', section);
    if(beforeBtn){ beforeBtn.addEventListener('click', function(){ animateBA(0); }); }
    if(afterBtn){ afterBtn.addEventListener('click', function(){ animateBA(100); }); }

    // Ensure labels toggle on load and on resize
    updateLabelVisibility();
    var resizeTO=null; window.addEventListener('resize', function(){ clearTimeout(resizeTO); resizeTO = setTimeout(updateLabelVisibility, 100); });
  }

  document.addEventListener('DOMContentLoaded', function(){
    $all('.irank-calc').forEach(initCalc);
  });
})();
