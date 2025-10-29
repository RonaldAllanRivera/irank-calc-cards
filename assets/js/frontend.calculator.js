(function(){
  function $(sel,root){ return (root||document).querySelector(sel); }
  function $all(sel,root){ return Array.prototype.slice.call((root||document).querySelectorAll(sel)); }
  function clamp(v,min,max){ return Math.max(min, Math.min(max, v)); }
  function fmt(n){ return Math.round(n); }

  function animateNumber(el, to){
    var from = parseFloat(el.getAttribute('data-from')||el.textContent)||0;
    var start = null; var dur = 300; to = +to;
    var prefix = el.getAttribute('data-sign') || '';
    function step(ts){ if(!start) start = ts; var p = clamp((ts-start)/dur,0,1); var val = from + (to-from)*p; el.textContent = prefix + fmt(val); if(p<1) requestAnimationFrame(step); else el.setAttribute('data-from', to); }
    requestAnimationFrame(step);
  }

  function uuid(){ try{ return sessionStorage.getItem('irank_uuid') || (function(){ var s=(Date.now()+Math.random()).toString(36); sessionStorage.setItem('irank_uuid', s); return s; })(); }catch(e){ return String(Date.now()); } }

  function trackEvent(){ /* REST removed */ }

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
    var overlayInner = $('.irank-calc__overlay-inner', section);
    var form = $('.irank-calc__form', section);
    var formResult = $('.irank-calc__form-result', section);
    var ba = $('.irank-calc__ba', section);
    var baHandle = $('.irank-calc__ba-handle', section);

    var min = parseFloat(slider.min||0), max = parseFloat(slider.max||100);
    // REST removed: no restRoot

    function update(){
      var w = parseFloat(slider.value||min);
      var loss = w * factor;
      weightEl.textContent = fmt(w);
      animateNumber(lossEl, loss);
      var pct = (w-min)/(max-min); pct = isFinite(pct)?pct:0.5;
      ba.style.setProperty('--ba', (pct*100).toFixed(2)+'%');
      if(slider && slider.style){ slider.style.setProperty('--slider-pct', ((pct*100).toFixed(2)+'%')); }
      sessionStorage.setItem('irank_calc_weight', String(w));
      sessionStorage.setItem('irank_calc_loss', String(loss));
      updateLabelVisibility();
    }

    slider.addEventListener('input', update);
    update();

    function openOverlay(){
      var w = parseFloat(slider.value||min); var loss = w*factor;
      overlay.hidden = false; overlay.setAttribute('aria-hidden','false'); document.body.classList.add('irank-no-scroll');
      // Reset success state each open
      if(form){ form.classList.remove('irank-calc__form--success'); }
      if(formResult){ formResult.classList.remove('is-success'); formResult.textContent=''; }
      if(overlayInner){ overlayInner.classList.remove('is-success'); }
      trackEvent();
    }
    function closeOverlay(){ overlay.hidden = true; overlay.setAttribute('aria-hidden','true'); document.body.classList.remove('irank-no-scroll'); }

    cta && cta.addEventListener('click', function(e){ e.preventDefault(); openOverlay(); });
    overlayClose && overlayClose.addEventListener('click', function(){ closeOverlay(); });
    overlay && overlay.addEventListener('click', function(e){ if(e.target===overlay) closeOverlay(); });
    document.addEventListener('keydown', function(e){ if(e.key === 'Escape' && !overlay.hidden){ closeOverlay(); } });

    // Form submit -> admin-ajax (stores lead), then success screen and auto-close
    if(form){
      form.addEventListener('submit', function(e){
        e.preventDefault();
        var w = parseFloat(slider.value||min); var loss = w*factor;
        var emailInput = form.querySelector('#irank_email');
        var emailVal = emailInput ? String(emailInput.value||'').trim() : '';
        var emailOk = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailVal);
        if(!emailOk){
          if(formResult){ formResult.textContent = 'Please enter a valid email address.'; }
          if(emailInput){ emailInput.focus(); }
          return;
        }
        var fd = new FormData(form);
        fd.append('action','irank_cc_lead');
        fd.append('nonce', (window.irankCC && irankCC.nonce) || '');
        fd.append('weight', fmt(w));
        fd.append('loss', fmt(loss));
        fd.append('page_id', pageId);
        fd.append('session_id', uuid());
        fd.append('referrer', document.referrer||'');
        var submitBtn = form.querySelector('button[type="submit"]');
        if(submitBtn){ submitBtn.disabled = true; }
        if(formResult){ formResult.textContent = 'Submitting...'; }
        var ajaxUrl = (window.irankCC && irankCC.ajaxUrl) || '/wp-admin/admin-ajax.php';
        fetch(ajaxUrl, { method:'POST', body: fd, credentials:'same-origin' })
          .then(function(r){ return r.json().catch(function(){ return { success:false }; }); })
          .then(function(resp){
            if(resp && resp.success){
              if(form){ form.classList.add('irank-calc__form--success'); }
              if(overlayInner){ overlayInner.classList.add('is-success'); }
              if(formResult){ formResult.textContent = "Thanks! We'll be in touch soon."; formResult.classList.add('is-success'); }
              // Auto close overlay after 5s
              setTimeout(function(){ if(!overlay.hidden){ closeOverlay(); } }, 5000);
              form.reset();
            } else {
              if(formResult){ formResult.textContent = (resp && resp.data && resp.data.error === 'invalid_email') ? 'Please enter a valid email address.' : 'Please try again.'; }
              if(emailInput){ emailInput.focus(); }
            }
          })
          .catch(function(){ if(formResult){ formResult.textContent = 'Network error. Please try again.'; } })
          .finally(function(){ if(submitBtn){ submitBtn.disabled = false; } });
      });
    }

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
