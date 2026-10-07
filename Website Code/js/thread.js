/* Scroll thread: one page-wide SVG path, revealed at the scroll position (no library, no easing). */
(function(){
  var page=document.getElementById('top'),anchor=document.getElementById('ring-anchor');
  if(!page||!anchor||!document.createElementNS)return;
  var NS='http://www.w3.org/2000/svg',reduce=matchMedia('(prefers-reduced-motion: reduce)').matches;
  var svg=document.createElementNS(NS,'svg');
  svg.setAttribute('class','thread');svg.setAttribute('aria-hidden','true');
  svg.innerHTML='<defs><linearGradient id="tg" gradientUnits="userSpaceOnUse" x1="0" y1="0" x2="0" y2="100">'+
    '<stop id="g1" offset="0" stop-color="#8b3dff"/><stop id="g2" offset="1" stop-color="#8b3dff"/>'+
    '<stop id="g3" offset="1" stop-color="#ffffff"/><stop id="g4" offset="1" stop-color="#ffffff"/></linearGradient></defs>'+
    '<path id="tpath" fill="none" stroke="url(#tg)" stroke-linecap="round" stroke-linejoin="round"/>'+
    '<g id="tx"></g><circle id="tring"/><circle id="tdot"/><circle id="tpulse" style="display:none"/><circle id="tcase" style="display:none"/>';
  page.insertBefore(svg,page.firstChild);
  var path=svg.querySelector('#tpath'),ring=svg.querySelector('#tring'),dot=svg.querySelector('#tdot'),tx=svg.querySelector('#tx'),cse=svg.querySelector('#tcase'),pls=svg.querySelector('#tpulse');
  var nodes=[],brs=[];
  var D=page.dataset;
  var LABELS=JSON.parse(D.labels||'[]'),COUNTS=JSON.parse(D.counts||'[]'),BRT=JSON.parse(D.branches||'[]'),CASES=D.cases||'cases';
  var BR={1:{t:BRT[0]},3:{t:BRT[1],hot:1},5:{t:BRT[2]}};
  function mk(tag,at,par){var e=document.createElementNS(NS,tag);for(var k in at)e.setAttribute(k,at[k]);if(par)par.appendChild(e);return e}
  var len=0,knots=[],endT=0,pageTop=0,ready=false,queued=false;

  function pos(el){var x=0,y=0;while(el&&el!==page){x+=el.offsetLeft;y+=el.offsetTop;el=el.offsetParent}return{x:x,y:y}}

  /* rounded orthogonal path: shorten both sides of every corner by r and join with an arc */
  function rounded(pts,r){
    var d='M'+pts[0][0]+' '+pts[0][1];
    for(var i=1;i<pts.length-1;i++){
      var a=pts[i-1],b=pts[i],c=pts[i+1];
      var l1=Math.hypot(b[0]-a[0],b[1]-a[1]),l2=Math.hypot(c[0]-b[0],c[1]-b[1]);
      var rr=Math.min(r,l1/2,l2/2);
      var d1=[(b[0]-a[0])/l1,(b[1]-a[1])/l1],d2=[(c[0]-b[0])/l2,(c[1]-b[1])/l2];
      var sweep=(d1[0]*d2[1]-d1[1]*d2[0])>0?1:0;
      d+=' L'+(b[0]-d1[0]*rr)+' '+(b[1]-d1[1]*rr)+' A'+rr+' '+rr+' 0 0 '+sweep+' '+(b[0]+d2[0]*rr)+' '+(b[1]+d2[1]*rr);
    }
    var e=pts[pts.length-1];return d+' L'+e[0]+' '+e[1];
  }

  function layout(){
    var W=page.clientWidth,H=page.offsetHeight;
    pageTop=page.getBoundingClientRect().top+window.scrollY;
    var wrap=page.querySelector('.wrap');
    var contentLeft=wrap.getBoundingClientRect().left-page.getBoundingClientRect().left+parseFloat(getComputedStyle(wrap).paddingLeft);
    var sw=contentLeft>=100?32:(W>=700?10:6);                 /* thinner on tablet and mobile */
    var r=Math.max(16,Math.round(sw*2/16)*16);                /* 2x stroke, snapped to 16 */
    var xL=contentLeft/2,xR=W-xL;                             /* runs sit in the page margins */
    var a=pos(anchor),cx=a.x,cy=a.y,ringR=sw*2.5;

    var pts=[[cx,cy],[xR,cy]],cur=xR,shiftYs=[];
    page.querySelectorAll('[data-shift]').forEach(function(sec){
      var yb=pos(sec).y,next=cur===xR?xL:xR;                  /* shifts happen in the gap between sections */
      pts.push([cur,yb],[next,yb]);shiftYs.push(yb);cur=next;
    });
    var end=page.querySelector('[data-end]'),ep=pos(end);
    var dy=ep.y+end.offsetHeight*0.5;
    pts.push([cur,dy]);

    svg.setAttribute('width',W);svg.setAttribute('height',H);svg.setAttribute('viewBox','0 0 '+W+' '+H);
    path.setAttribute('d',rounded(pts,r));path.setAttribute('stroke-width',sw);
    len=path.getTotalLength();
    path.style.strokeDasharray=len;

    /* hard colour switch at the top of the CTA block */
    var o=Math.min(1,ep.y/H);
    svg.querySelector('#tg').setAttribute('y2',H);
    ['g2','g3','g4'].forEach(function(id,i){svg.querySelector('#'+id).setAttribute('offset',i<2?o:1)});
    svg.querySelector('#g4').setAttribute('offset',1);

    ring.setAttribute('cx',cx);ring.setAttribute('cy',cy);ring.setAttribute('r',ringR);ring.setAttribute('stroke-width',sw);
    dot.setAttribute('cx',cur);dot.setAttribute('cy',dy);dot.setAttribute('r',Math.max(sw*.9,8));dot.setAttribute('stroke-width',Math.max(sw*.4,4));

    /* scroll -> length map: the pen tip stays at the scroll line on vertical runs;
       each horizontal shift is drawn while the tip crosses a 160px band around the boundary */
    var poly=0,segs=[];
    for(var i=1;i<pts.length;i++){
      var L=Math.abs(pts[i][0]-pts[i-1][0])+Math.abs(pts[i][1]-pts[i-1][1]);
      segs.push({l:L,h:pts[i][1]===pts[i-1][1],y1:pts[i][1],last:i===pts.length-1,nextH:i<pts.length-1&&pts[i+1][1]===pts[i][1]});
      poly+=L;
    }
    var k=len/poly,t=cy,l=0,mids=[],first=true;knots=[{t:t,l:0}];
    segs.forEach(function(s){
      var tn;
      if(s.h)tn=t+160;
      else if(s.last)tn=Math.max(dy,t+1);
      else tn=Math.max(s.y1-(s.nextH?80:0),t+1);
      var l0=l;l+=s.l*k;knots.push({t:tn,l:l});t=tn;
      if(s.h){if(first)first=false;else mids.push((l0+l)/2)}
    });
    endT=t;

    /* process-map layer: step nodes on the thread, rework/deviation branches beside it, the travelling case */
    while(tx.firstChild)tx.removeChild(tx.firstChild);
    nodes=[];brs=[];
    var compact=W<700;
    cse.setAttribute('r',Math.max(sw*.7,5));cse.setAttribute('stroke-width',Math.max(sw*.25,3));
    pls.setAttribute('r',Math.max(sw*.7,5)*1.4);
    mids.forEach(function(mid,i){
      var yb=shiftYs[i],lab=LABELS[i]||'';
      var w=compact?lab.length*7+26:Math.max(112,lab.length*8.4+40),h=compact?28:50;
      var br=BR[i];
      if(br){
        var bw=Math.max(80,Math.min(260,W*.18)),off=compact?34:56,side=(i%4===1)?1:-1;
        var xa=W/2+side*(w/2+30),xb=xa+side*bw;
        xb=Math.max(W*.12,Math.min(W*.88,xb));
        var bp=mk('path',{class:'tbr'+(br.hot?' hot':''),d:rounded([[xa,yb],[xa,yb-off],[xb,yb-off],[xb,yb]],16)},tx);
        var bl=bp.getTotalLength();bp.style.strokeDasharray=bl;bp.style.strokeDashoffset=bl;
        var chip=null;
        if(!compact){
          var cw=br.t.length*7+30;
          chip=mk('g',{class:'tchip'+(br.hot?' hot':''),transform:'translate('+((xa+xb)/2-cw/2)+' '+(yb-off-11)+')'},tx);
          mk('rect',{width:cw,height:22,rx:11},chip);
          var ct=mk('text',{x:cw/2,y:15,'text-anchor':'middle'},chip);ct.textContent=br.t;
        }
        brs.push({path:bp,len:bl,chip:chip,mid:mid});
      }
      var g=mk('g',{class:'tnode',transform:'translate('+(W/2-w/2)+' '+(yb-h/2)+')'},tx);
      mk('rect',{width:w,height:h,rx:14},g);
      var t1=mk('text',{x:w/2,y:compact?18.5:22,'text-anchor':'middle'},g);t1.textContent=lab;
      if(compact)t1.style.fontSize='11px';
      else{var t2=mk('text',{x:w/2,y:38,'text-anchor':'middle',class:'ct'},g);t2.textContent=COUNTS[i]+' '+CASES}
      nodes.push({el:g,l:mid});
    });
    ready=true;draw();
  }

  function draw(){
    queued=false;if(!ready)return;
    var l;
    if(reduce)l=len;
    else{
      var t=window.scrollY-pageTop+window.innerHeight*0.62;   /* tip sits ~62% down the screen */
      if(t<=knots[0].t)l=0;
      else if(t>=endT)l=len;
      else{
        var i=1;while(i<knots.length-1&&knots[i].t<=t)i++;
        var p=knots[i-1],q=knots[i];
        l=p.l+(t-p.t)/(q.t-p.t)*(q.l-p.l);
      }
    }
    path.style.strokeDashoffset=len-l;
    if(l>0)ring.classList.add('on');else ring.classList.remove('on');
    dot.style.opacity=l>=len-0.5?1:0;
    nodes.forEach(function(n){n.el.classList.toggle('on',l>=n.l)});
    brs.forEach(function(b){var on=l>=b.mid;b.path.style.strokeDashoffset=on?0:b.len;if(b.chip)b.chip.classList.toggle('on',on)});
    if(!reduce&&l>0&&l<len-0.5){
      var pt=path.getPointAtLength(l);
      cse.setAttribute('cx',pt.x);cse.setAttribute('cy',pt.y);pls.setAttribute('cx',pt.x);pls.setAttribute('cy',pt.y);
      cse.style.display=pls.style.display='';
    }else cse.style.display=pls.style.display='none';
  }
  function queue(){if(!queued){queued=true;requestAnimationFrame(draw)}}

  var rl=0;function relayout(){cancelAnimationFrame(rl);rl=requestAnimationFrame(layout)}
  addEventListener('scroll',queue,{passive:true});
  addEventListener('resize',relayout);
  addEventListener('load',relayout);
  if(document.fonts&&document.fonts.ready)document.fonts.ready.then(relayout);
  if('ResizeObserver' in window)new ResizeObserver(relayout).observe(page);
  requestAnimationFrame(function(){requestAnimationFrame(layout)});
})();
