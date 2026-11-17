(function(){
  // Testimonial slider
  var t=document.getElementById('tst');
  if(t){
    var slides=t.querySelectorAll('.slide'),dots=t.querySelectorAll('.dot'),i=0;
    function show(n){i=(n+slides.length)%slides.length;slides.forEach(function(s,k){s.hidden=k!==i});dots.forEach(function(d,k){d.setAttribute('aria-current',k===i?'true':'false')})}
    t.querySelector('[data-prev]').addEventListener('click',function(){show(i-1)});
    t.querySelector('[data-next]').addEventListener('click',function(){show(i+1)});
    dots.forEach(function(d){d.addEventListener('click',function(){show(+d.dataset.dot)})});
    show(0);
  }
  // Gallery lightbox
  var lb=document.getElementById('lb');
  if(lb){
    var btns=[].slice.call(document.querySelectorAll('[data-lb]')),cur=0,img=lb.querySelector('img'),cnt=lb.querySelector('.cnt'),opener=null;
    function open(n){cur=(n+btns.length)%btns.length;var s=btns[cur].querySelector('img');img.src=s.src;img.alt=s.alt;cnt.textContent=(cur+1)+' / '+btns.length;lb.hidden=false;document.body.style.overflow='hidden'}
    function close(){lb.hidden=true;document.body.style.overflow='';if(opener)opener.focus()}
    btns.forEach(function(b,k){b.addEventListener('click',function(){opener=b;open(k);lb.querySelector('.x').focus()})});
    lb.querySelector('.x').addEventListener('click',close);
    lb.querySelector('.pv').addEventListener('click',function(){open(cur-1)});
    lb.querySelector('.nx').addEventListener('click',function(){open(cur+1)});
    lb.addEventListener('click',function(e){if(e.target===lb)close()});
    document.addEventListener('keydown',function(e){if(lb.hidden)return;if(e.key==='Escape')close();if(e.key==='ArrowLeft')open(cur-1);if(e.key==='ArrowRight')open(cur+1)});
  }
  // Registration form
  var f=document.getElementById('reg');
  if(f){
    var warn=document.getElementById('warn');
    var cf=document.getElementById('company-fields'),co=document.getElementById('company');
    function syncAs(){var b=document.getElementById('as-business').checked;cf.hidden=!b;co.required=b}
    f.querySelectorAll('input[name=booking_as]').forEach(function(r){r.addEventListener('change',syncAs)});syncAs();
    f.addEventListener('submit',function(e){
      if(!document.getElementById('agree-yes').checked){e.preventDefault();warn.hidden=false;warn.scrollIntoView({block:'center'})}
    });
    f.querySelectorAll('input[name=agree]').forEach(function(r){r.addEventListener('change',function(){if(document.getElementById('agree-yes').checked)warn.hidden=true})});
    if(/[?&]error=1/.test(location.search)){var er=document.getElementById('err');er.hidden=false;er.scrollIntoView({block:'center'})}
  }
})();
