(() => {
  const $ = (s, c=document) => c.querySelector(s);
  const $$ = (s, c=document) => [...c.querySelectorAll(s)];

  const header = $('[data-header]');
  const menuBtn = $('[data-menu-toggle]');
  const menu = $('[data-mobile-menu]');
  if (menuBtn && menu) {
    menuBtn.addEventListener('click', () => {
      const open = menu.classList.toggle('is-open');
      menuBtn.classList.toggle('is-open', open);
      menuBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
      document.body.classList.toggle('menu-open', open);
    });
    $$('a', menu).forEach(a => a.addEventListener('click', () => {
      menu.classList.remove('is-open'); menuBtn.classList.remove('is-open'); document.body.classList.remove('menu-open');
    }));
  }

  const onScroll = () => header && header.classList.toggle('is-scrolled', window.scrollY > 24);
  onScroll(); window.addEventListener('scroll', onScroll, {passive:true});

  const io = 'IntersectionObserver' in window ? new IntersectionObserver(entries => {
    entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('is-visible'); io.unobserve(e.target); } });
  }, {threshold:.12, rootMargin:'0px 0px -40px'}) : null;
  $$('.reveal').forEach(el => io ? io.observe(el) : el.classList.add('is-visible'));

  const videoObserver = 'IntersectionObserver' in window ? new IntersectionObserver(entries => {
    entries.forEach(e => { const v=e.target; if(e.isIntersecting) v.play().catch(()=>{}); else v.pause(); });
  }, {threshold:.35}) : null;
  $$('.video-card video').forEach(v => videoObserver && videoObserver.observe(v));

  $$('[data-accordion] .faq-item button').forEach(btn => btn.addEventListener('click', () => {
    const item = btn.closest('.faq-item');
    const parent = item.parentElement;
    $$('.faq-item', parent).forEach(other => {
      if (other !== item) { other.classList.remove('is-open'); const b=$('button',other); b.setAttribute('aria-expanded','false'); $('b',b).textContent='+'; }
    });
    const open = item.classList.toggle('is-open');
    btn.setAttribute('aria-expanded', open ? 'true':'false'); $('b',btn).textContent = open ? '−':'+';
  }));

  const form = $('[data-newsletter-form]');
  if (form) form.addEventListener('submit', async (e) => {
    e.preventDefault(); const status=$('[data-form-status]',form); const email=$('input[name="email"]',form).value.trim();
    if (!/^\S+@\S+\.\S+$/.test(email)) { status.textContent=MentraVN.messages.emailRequired; return; }
    status.textContent='Đang gửi…';
    const data = new URLSearchParams({action:'mentra_vn_newsletter',nonce:MentraVN.nonce,email});
    try { const r=await fetch(MentraVN.ajaxUrl,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:data}); const j=await r.json(); status.textContent=j.success?MentraVN.messages.success:(j.data?.message||MentraVN.messages.error); if(j.success) form.reset(); }
    catch(_){ status.textContent=MentraVN.messages.error; }
  });
})();
