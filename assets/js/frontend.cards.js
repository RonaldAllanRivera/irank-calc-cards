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
    dotsWrap.innerHTML='';
    cards.forEach(function(_,i){ var b=document.createElement('button'); b.type='button'; b.className='irank-cards__dot'; b.setAttribute('aria-label','Go to slide '+(i+1)); b.addEventListener('click',function(){scrollToIndex(track,i);}); dotsWrap.appendChild(b); });

    function updateDots(){
      var idx = nearestIndex(track);
      $all('.irank-cards__dot', dotsWrap).forEach(function(d,i){ d.toggleAttribute('aria-current', i===idx); });
    }
    track.addEventListener('scroll', function(){ window.requestAnimationFrame(updateDots); });
    updateDots();

    prev && prev.addEventListener('click', function(){ scrollToIndex(track, nearestIndex(track)-1); });
    next && next.addEventListener('click', function(){ scrollToIndex(track, nearestIndex(track)+1); });
  }

  document.addEventListener('DOMContentLoaded', function(){
    $all('.irank-cards').forEach(init);
  });
})();
