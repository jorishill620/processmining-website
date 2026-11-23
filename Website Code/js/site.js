(function(){
  document.documentElement.classList.add('js');
  var nav=document.getElementById('nav'),b=document.getElementById('burger');
  var secs=[].slice.call(document.querySelectorAll('main > section, main > .marq'));
  var f=document.getElementById('fcta'),fx=f&&f.querySelector('.fcta__x'),fdone=false;
  try{fdone=sessionStorage.getItem('pm-fcta')==='1'}catch(e){}

  /* header follows the section underneath it (dark / light) */
  function theme(){
    var y=38,t='dark';
    for(var i=0;i<secs.length;i++){
      var r=secs[i].getBoundingClientRect();
      if(r.top<=y&&r.bottom>y){t=(secs[i].classList.contains('light')||secs[i].classList.contains('paper'))?'light':'dark';break}
    }
    if(nav.getAttribute('data-theme')!==t)nav.setAttribute('data-theme',t);
  }
  /* floating call-to-action: after the hero, not next to the closing call-to-action or footer */
  function pill(){
    if(!f)return;
    var show=!fdone&&window.scrollY>window.innerHeight*.7;
    if(show){
      ['.cta','footer'].forEach(function(sel){
        var el=document.querySelector(sel);
        if(el&&el.getBoundingClientRect().top<window.innerHeight*.92)show=false;
      });
    }
    f.classList.toggle('show',show);
  }
  var tick=false;
  function frame(){tick=false;nav.classList.toggle('solid',window.scrollY>20);theme();pill()}
  function onScroll(){if(!tick){tick=true;requestAnimationFrame(frame)}}
  frame();addEventListener('scroll',onScroll,{passive:true});addEventListener('resize',onScroll);addEventListener('load',onScroll);
  if(fx)fx.addEventListener('click',function(){fdone=true;try{sessionStorage.setItem('pm-fcta','1')}catch(e){}f.classList.remove('show')});

  b.addEventListener('click',function(){var o=nav.classList.toggle('open');b.setAttribute('aria-expanded',o)});
  nav.querySelectorAll('.menu a').forEach(function(a){a.addEventListener('click',function(){nav.classList.remove('open')})});
  /* "Voor wie" dropdown: click/keyboard toggle (hover is handled in CSS on desktop) */
  var dd=nav.querySelector('.dd'),db=nav.querySelector('.dd__btn');
  if(dd&&db){
    db.addEventListener('click',function(e){e.stopPropagation();var o=dd.classList.toggle('open');db.setAttribute('aria-expanded',o)});
    document.addEventListener('click',function(){dd.classList.remove('open');db.setAttribute('aria-expanded','false')});
    document.addEventListener('keydown',function(e){if(e.key==='Escape'){dd.classList.remove('open');db.setAttribute('aria-expanded','false')}});
  }
  var els=document.querySelectorAll('.rv');
  if(!('IntersectionObserver' in window)){els.forEach(function(e){e.classList.add('in')});return}
  var io=new IntersectionObserver(function(es){es.forEach(function(e){if(e.isIntersecting){e.target.classList.add('in');io.unobserve(e.target)}})},{threshold:.12});
  els.forEach(function(e,i){e.style.transitionDelay=(i%4)*70+'ms';io.observe(e)});
})();
