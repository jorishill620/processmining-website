/* Product window demo ("Het probleem" section): rows fill in, the deviation row lights up, a cursor clicks it and
   a detail strip opens. Loops while visible; reduced motion (or no JS) shows the finished state. */
(function(){
  var win=document.getElementById('win');if(!win)return;
  var rows=[].slice.call(win.querySelectorAll('.win__row:not(.win__row--h)')),dev=win.querySelector('.win__row--dev');
  var reduce=matchMedia('(prefers-reduced-motion: reduce)').matches;
  if(reduce||!dev)return;                         /* finished state is the default markup */
  win.classList.add('demo');
  var timers=[],on=false;
  function at(ms,fn){timers.push(setTimeout(fn,ms))}
  function clear(){timers.forEach(clearTimeout);timers=[]}
  function reset(){rows.forEach(function(r){r.classList.remove('in')});dev.classList.remove('hl','go','click');win.classList.remove('open')}
  function run(){
    if(!on)return;reset();
    rows.forEach(function(r,i){at(350+i*260,function(){r.classList.add('in')})});
    at(2300,function(){dev.classList.add('hl')});
    at(2800,function(){dev.classList.add('go')});
    at(4100,function(){dev.classList.add('click')});
    at(4400,function(){win.classList.add('open')});
    at(8800,function(){win.classList.remove('open');dev.classList.remove('go','click')});
    at(9600,function(){rows.forEach(function(r){r.classList.remove('in')});dev.classList.remove('hl')});
    at(10500,run);
  }
  function finished(){clear();rows.forEach(function(r){r.classList.add('in')});dev.classList.add('hl');win.classList.add('open')}
  finished();
  if('IntersectionObserver' in window)new IntersectionObserver(function(es){
    if(es[0].isIntersecting){if(!on){on=true;run()}}else{on=false;finished()}
  },{threshold:.35}).observe(win);
})();
