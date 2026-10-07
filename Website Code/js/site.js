(function(){
  var nav=document.getElementById('nav'),b=document.getElementById('burger');
  function s(){nav.classList.toggle('solid',window.scrollY>20)}
  s();addEventListener('scroll',s,{passive:true});
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
