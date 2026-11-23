/* Chapter headings drawn as halftone dots ("Ophalen." "Analyseren." …). Dots appear left to right when the
   heading scrolls into view; reduced motion draws them at once. The canvas is decorative: the section has its own heading. */
(function(){
  var cvs=[].slice.call(document.querySelectorAll('.chapter__cv'));if(!cvs.length)return;
  var reduce=matchMedia('(prefers-reduced-motion: reduce)').matches;
  var state=new WeakMap();

  function build(cv){
    var word=cv.getAttribute('data-word')||'',host=cv.parentNode,sec=cv.closest('section');
    var light=sec&&(sec.classList.contains('light')||sec.classList.contains('paper'));
    var W=Math.min(host.clientWidth||600,760),dpr=Math.min(2,window.devicePixelRatio||1);
    var off=document.createElement('canvas'),o=off.getContext('2d');
    var fs=200;o.font='800 '+fs+'px Montserrat, sans-serif';
    fs=Math.floor(fs*Math.min(1,(W*.97)/o.measureText(word).width));fs=Math.min(fs,W<520?60:150);
    o.font='800 '+fs+'px Montserrat, sans-serif';
    var tw=Math.ceil(o.measureText(word).width)+8,H=Math.ceil(fs*1.25);
    off.width=tw;off.height=H;o=off.getContext('2d');o.font='800 '+fs+'px Montserrat, sans-serif';
    o.fillStyle='#000';o.fillText(word,4,Math.round(fs*.95));
    var data=o.getImageData(0,0,tw,H).data,step=Math.max(6,Math.round(fs/17)),dots=[];
    for(var y=step/2;y<H;y+=step)for(var x=step/2;x<tw;x+=step){
      if(data[((y|0)*tw+(x|0))*4+3]>110)dots.push({x:x,y:y,r:step*.37,p:Math.random()<.07,d:(x/tw)*900+Math.random()*160});
    }
    cv.style.width=tw+'px';cv.style.height=H+'px';cv.width=Math.round(tw*dpr);cv.height=Math.round(H*dpr);
    var c=cv.getContext('2d');c.setTransform(dpr,0,0,dpr,0,0);
    var s={dots:dots,c:c,w:tw,h:H,base:light?'#0a0a0f':'#ffffff',acc:light?'#8b3dff':'#a468ff',shown:!!(state.get(cv)&&state.get(cv).shown),raf:0};
    state.set(cv,s);if(s.shown)paint(s,1e9);else s.c.clearRect(0,0,s.w,s.h);
  }
  function paint(s,t){
    var c=s.c;c.clearRect(0,0,s.w,s.h);
    if(!s.shown&&t<1e9){return}
    s.dots.forEach(function(d,i){
      var k=Math.max(0,Math.min(1,(t-d.d)/260));if(k<=0)return;
      var e=1-Math.pow(1-k,3),r=d.r*e;
      c.fillStyle=d.p?s.acc:s.base;c.globalAlpha=d.p?1:.92;c.beginPath();c.arc(d.x,d.y,r,0,6.283);c.fill();
    });
    c.globalAlpha=1;
  }
  function reveal(cv){
    var s=state.get(cv);if(!s||s.shown)return;s.shown=true;
    if(reduce){paint(s,1e9);return}
    var t0=performance.now();
    (function frame(now){paint(s,now-t0);if(now-t0<1500)s.raf=requestAnimationFrame(frame);else paint(s,1e9)})(t0);
  }
  function init(){
    cvs.forEach(build);
    if('IntersectionObserver' in window){
      var io=new IntersectionObserver(function(es){es.forEach(function(e){if(e.isIntersecting){reveal(e.target);io.unobserve(e.target)}})},{threshold:.35});
      cvs.forEach(function(cv){io.observe(cv)});
    }else cvs.forEach(reveal);
  }
  var started=false;function go(){if(started)return;started=true;init()}
  if(document.fonts&&document.fonts.load){Promise.race([document.fonts.load('800 100px Montserrat'),new Promise(function(r){setTimeout(r,1500)})]).then(go,go)}else go();
  var rs=0;addEventListener('resize',function(){clearTimeout(rs);rs=setTimeout(function(){if(started)cvs.forEach(build)},200)});
})();
