/* Hero canvas: event-log dots enter as chaos, settle onto process lanes and converge into the ring
   where the scroll thread begins. Pauses off-screen; reduced motion shows one still frame. */
(function(){
  var hero=document.querySelector('.hero'),anchor=document.getElementById('ring-anchor');
  if(!hero||!anchor)return;
  var cv=document.createElement('canvas');cv.className='hero__cv';cv.setAttribute('aria-hidden','true');
  hero.insertBefore(cv,hero.firstChild);
  var ctx=cv.getContext('2d');if(!ctx)return;
  var reduce=matchMedia('(prefers-reduced-motion: reduce)').matches;
  var W=0,H=0,R=80,lanes=[],parts=[],focus={x:0,y:0},visible=true,raf=0,last=0,time=0;

  function spawn(initial){
    return {l:Math.floor(Math.random()*lanes.length),u:initial?Math.random():0,sp:.055+Math.random()*.08,
            ph:Math.random()*6.28,amp:.5+Math.random()*.9,dev:Math.random()<.12};
  }
  function setup(){
    var r=hero.getBoundingClientRect(),dpr=Math.min(2,window.devicePixelRatio||1);
    W=r.width;H=r.height;
    cv.width=Math.round(W*dpr);cv.height=Math.round(H*dpr);cv.style.width=W+'px';cv.style.height=H+'px';
    ctx.setTransform(dpr,0,0,dpr,0,0);
    R=W>=1352?80:(W>=700?25:15);                      /* same ring size as the scroll thread */
    var n=W<700?5:9;lanes=[];
    for(var i=0;i<n;i++)lanes.push({t:(i/(n-1))*2-1});
    parts=[];var count=W<700?54:170;
    for(i=0;i<count;i++)parts.push(spawn(true));
    measure();
  }
  function measure(){
    var a=anchor.getBoundingClientRect(),h=hero.getBoundingClientRect();
    focus.x=a.left-h.left;focus.y=a.top-h.top;
  }
  /* point and direction on lane l at progress u (cubic bezier) */
  function lane(l,u){
    var spread=W<700?Math.min(H*.16,110):Math.min(H*.34,260);
    var x0=-30,y0=focus.y+l.t*spread,x3=focus.x-R*.95,y3=focus.y+l.t*R*.55;
    var x1=x0+(x3-x0)*.45,y1=y0,x2=x0+(x3-x0)*.72,y2=y3,m=1-u;
    var x=m*m*m*x0+3*m*m*u*x1+3*m*u*u*x2+u*u*u*x3,y=m*m*m*y0+3*m*m*u*y1+3*m*u*u*y2+u*u*u*y3;
    var dx=3*m*m*(x1-x0)+6*m*u*(x2-x1)+3*u*u*(x3-x2),dy=3*m*m*(y1-y0)+6*m*u*(y2-y1)+3*u*u*(y3-y2),d=Math.hypot(dx,dy)||1;
    return {x:x,y:y,nx:-dy/d,ny:dx/d};
  }
  function draw(){
    ctx.clearRect(0,0,W,H);
    var g=ctx.createRadialGradient(focus.x,focus.y,0,focus.x,focus.y,Math.max(150,R*3));
    g.addColorStop(0,'rgba(139,61,255,.20)');g.addColorStop(1,'rgba(139,61,255,0)');
    ctx.fillStyle=g;ctx.fillRect(0,0,W,H);
    ctx.lineWidth=1;ctx.strokeStyle='rgba(255,255,255,.09)';
    lanes.forEach(function(l){
      ctx.beginPath();
      for(var k=0;k<=40;k++){var p=lane(l,k/40);if(k)ctx.lineTo(p.x,p.y);else ctx.moveTo(p.x,p.y)}
      ctx.stroke();
    });
    /* process steps on the middle lanes */
    ctx.strokeStyle='rgba(255,255,255,.2)';
    for(var i=0;i<lanes.length;i++){
      if(Math.abs(lanes[i].t)>.5)continue;
      [.52,.76].forEach(function(u){var p=lane(lanes[i],u);ctx.strokeRect(p.x-5,p.y-5,10,10)});
    }
    parts.forEach(function(p){
      var pt=lane(lanes[p.l],p.u),j=Math.pow(1-p.u,1.6)*p.amp*46*Math.sin(p.ph+time*1.3+p.u*9);
      var x=pt.x+pt.nx*j,y=pt.y+pt.ny*j,a=Math.min(1,p.u*8)*(p.u>.93?(1-p.u)/.07:1);
      if(p.dev){
        ctx.fillStyle='rgba(164,104,255,'+(.16*a)+')';ctx.beginPath();ctx.arc(x,y,6,0,6.283);ctx.fill();
        ctx.fillStyle='rgba(190,150,255,'+(.95*a)+')';ctx.beginPath();ctx.arc(x,y,2.2,0,6.283);ctx.fill();
      }else{
        ctx.fillStyle='rgba(255,255,255,'+(.78*a)+')';ctx.beginPath();ctx.arc(x,y,1.6,0,6.283);ctx.fill();
      }
    });
  }
  function tick(ts){
    raf=0;if(!visible||document.hidden)return;
    var dt=Math.min(.05,(ts-last)/1000||0);last=ts;time+=dt;measure();
    parts.forEach(function(p,i){p.u+=p.sp*dt*(p.u>.8?1.35:1);if(p.u>=1)parts[i]=spawn(false)});
    draw();raf=requestAnimationFrame(tick);
  }
  function start(){if(!raf&&!reduce){last=performance.now();raf=requestAnimationFrame(tick)}}

  setup();draw();
  if(reduce){setTimeout(function(){measure();draw()},1300)}
  if('IntersectionObserver' in window)new IntersectionObserver(function(es){visible=es[0].isIntersecting;if(visible)start()}).observe(hero);
  document.addEventListener('visibilitychange',function(){if(!document.hidden)start()});
  var rs=0;function relayout(){cancelAnimationFrame(rs);rs=requestAnimationFrame(function(){setup();draw();start()})}
  if('ResizeObserver' in window)new ResizeObserver(relayout).observe(hero);else addEventListener('resize',relayout);
  start();
})();
