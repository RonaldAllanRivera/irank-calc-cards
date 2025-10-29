(function(){
  function $all(sel,root){ return Array.prototype.slice.call((root||document).querySelectorAll(sel)); }
  function nearestIndex(track){
    var cards = $all('.irank-card', track); if(!cards.length) return 0;
    var w = cards[0].getBoundingClientRect().width + parseFloat(getComputedStyle(track).columnGap||getComputedStyle(track).gap||0);
    return Math.round(track.scrollLeft / (w||1));
  }
  function scrollToIndex(track, i){
    var cards = $all('.irank-card', track); if(!cards.length) return;
    var w = cards[0].getBoundingClientRect().width + parseFloat(getComputedStyle(track).columnGap||getComputedStyle(track).gap||0);
    track.scrollTo({left: Math.max(0, Math.min(i, cards.length-1))*w, behavior:'smooth'});
  }
  function init(section){
    var track = section.querySelector('.irank-cards__track');
    var prev = section.querySelector('.irank-cards__prev');
    var next = section.querySelector('.irank-cards__next');
    var dotsWrap = section.querySelector('.irank-cards__dots');
    var cards = $all('.irank-card', track);
    if(dotsWrap){ dotsWrap.innerHTML=''; }
    if(dotsWrap){
      cards.forEach(function(_,i){ var b=document.createElement('button'); b.type='button'; b.className='irank-cards__dot'; b.setAttribute('aria-label','Go to slide '+(i+1)); b.addEventListener('click',function(){scrollToIndex(track,i);}); dotsWrap.appendChild(b); });
    }

    function updateUI(){
      var idx = nearestIndex(track);
      var max = Math.max(0, cards.length-1);
      if(dotsWrap){ $all('.irank-cards__dot', dotsWrap).forEach(function(d,i){ d.toggleAttribute('aria-current', i===idx); }); }
      if(prev){ prev.toggleAttribute('aria-disabled', idx<=0); prev.toggleAttribute('disabled', idx<=0); }
      if(next){ next.toggleAttribute('aria-disabled', idx>=max); next.toggleAttribute('disabled', idx>=max); }
      section.classList.toggle('is-at-start', idx<=0);
      section.classList.toggle('is-at-end', idx>=max);
    }
    track.addEventListener('scroll', function(){ window.requestAnimationFrame(updateUI); });
    window.addEventListener('resize', function(){ window.requestAnimationFrame(updateUI); });
    updateUI();

    prev && prev.addEventListener('click', function(){ if(prev.getAttribute('aria-disabled')==='true') return; scrollToIndex(track, nearestIndex(track)-1); });
    next && next.addEventListener('click', function(){ if(next.getAttribute('aria-disabled')==='true') return; scrollToIndex(track, nearestIndex(track)+1); });

    // Keyboard navigation on the track
    track && track.addEventListener('keydown', function(e){
      if(e.key === 'ArrowLeft'){ e.preventDefault(); scrollToIndex(track, nearestIndex(track)-1); }
      else if(e.key === 'ArrowRight'){ e.preventDefault(); scrollToIndex(track, nearestIndex(track)+1); }
    });
  }

  document.addEventListener('DOMContentLoaded', function(){
    $all('.irank-cards').forEach(init);
  });
})();
