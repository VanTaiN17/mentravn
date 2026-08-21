(function(){
  'use strict';
  const qs=(s,r=document)=>r.querySelector(s), qsa=(s,r=document)=>Array.from(r.querySelectorAll(s));
  const root=document.documentElement;

  function initHeader(){
    const header=qs('.site-header'); if(!header) return;
    const mobileButton=qs('button[aria-label="Mở menu"],button[aria-label="Open menu"]',header);
    const setScrolled=()=>{
      const on=window.scrollY>18;
      header.classList.toggle('is-scrolled',on);
      if(header.classList.contains('site-header--transparent')){
        const rail=qs('.desktop-nav-rail',header);
        if(rail) rail.style.color=on?'var(--ink-primary)':'#fff';
        qsa('.lg\\:hidden button span',header).forEach(x=>x.style.backgroundColor=on?'var(--ink-primary)':'#fff');
      }
    };
    setScrolled(); window.addEventListener('scroll',setScrolled,{passive:true});

    if(mobileButton){
      const menu=document.createElement('div');
      menu.className='mentra-mobile-menu';
      menu.setAttribute('aria-hidden','true');
      menu.innerHTML=`
        <a href="${MENTRA_VN.home}mentra-os/">MentraOS <span aria-hidden="true">→</span></a>
        <a href="${MENTRA_VN.home}mentra-live/">Kính Mentra Live <span aria-hidden="true">→</span></a>
        <a href="${MENTRA_VN.home}ung-dung/">Ứng dụng <span aria-hidden="true">→</span></a>
        <a href="${MENTRA_VN.home}nha-phat-trien/">Nhà phát triển <span aria-hidden="true">→</span></a>
        <a href="${MENTRA_VN.home}ve-mentra/">Về Mentra <span aria-hidden="true">→</span></a>
        <a href="${MENTRA_VN.home}ho-tro/">Hỗ trợ <span aria-hidden="true">→</span></a>
        <a class="mobile-menu-small" href="${MENTRA_VN.home}lien-he/">Liên hệ</a>
        <a class="mobile-menu-small" href="${MENTRA_VN.home}tin-tuc/">Tin tức</a>`;
      document.body.appendChild(menu);
      const close=()=>{menu.classList.remove('is-open');menu.setAttribute('aria-hidden','true');mobileButton.setAttribute('aria-expanded','false');document.body.classList.remove('mentra-menu-open');};
      mobileButton.setAttribute('aria-expanded','false');
      mobileButton.addEventListener('click',()=>{
        const open=!menu.classList.contains('is-open');
        menu.classList.toggle('is-open',open);menu.setAttribute('aria-hidden',String(!open));mobileButton.setAttribute('aria-expanded',String(open));document.body.classList.toggle('mentra-menu-open',open);
        qsa('span',mobileButton).forEach((s,i)=>{ if(open){s.style.backgroundColor='var(--ink-primary)'; if(i===0){s.style.transform='translateY(7px) rotate(45deg)'} if(i===1){s.style.opacity='0'} if(i===2){s.style.width='100%';s.style.transform='translateY(-7px) rotate(-45deg)'}} else {s.style.transform='';s.style.opacity='';s.style.width='';} });
      });
      menu.addEventListener('click',e=>{if(e.target.closest('a')) close();});
      window.addEventListener('keydown',e=>{if(e.key==='Escape') close();});
    }
  }


  function initDesktopMegaMenu(){
    if(window.matchMedia('(max-width: 1023px)').matches) return;
    const header=qs('.site-header');
    const rail=header && qs('.desktop-nav-rail',header);
    if(!header || !rail) return;

    const menus={
      'OS':{
        groups:[
          {title:'NỀN TẢNG',links:[
            ['MentraOS','mentra-os/'],
            ['Tải ứng dụng','https://mentraglass.com/get-mentra'],
            ['Cổng nhà phát triển','https://console.mentraglass.com',true],
            ['GitHub','https://github.com/Mentra-Community/MentraOS',true]
          ]}
        ]
      },
      'Kính':{
        wide:true,
        groups:[
          {title:'SẢN PHẨM',links:[['Mentra Live','mentra-live/']]},
          {title:'PHỤ KIỆN',links:[
            ['Tròng kính theo độ','trong-kinh/'],
            ['Infinity Cable','mentra-live/#charging']
          ]},
          {title:'KÍNH ĐƯỢC HỖ TRỢ',links:[
            ['Even Realities G1 & G2','even-realities/'],
            ['NIMO','nimo/']
          ]}
        ],
        visual:{src:'assets/closed_mentra_live.webp',label:'Mentra Live',href:'mentra-live/'}
      },
      'Nhà phát triển':{
        groups:[
          {title:'NHÀ PHÁT TRIỂN',links:[
            ['Devs','nha-phat-trien/'],
            ['Cổng nhà phát triển','https://console.mentraglass.com',true],
            ['Tài liệu','https://docs.mentraglass.com',true],
            ['GitHub','https://github.com/Mentra-Community/MentraOS',true]
          ]}
        ]
      },
      'Công ty':{
        groups:[
          {title:'CÔNG TY',links:[
            ['Về Mentra','ve-mentra/'],
            ['Tin tức','tin-tuc/'],
            ['Tuyển dụng','tuyen-dung/'],
            ['Mạng xã hội','mang-xa-hoi/'],
            ['Discord','discord/']
          ]}
        ]
      },
      'Liên hệ':{
        groups:[
          {title:'LIÊN HỆ',links:[
            ['Kinh doanh','lien-he/?topic=sales'],
            ['Hỗ trợ','ho-tro/'],
            ['Đối tác','doi-tac/'],
            ['Truyền thông','truyen-thong/']
          ]}
        ]
      }
    };

    const panel=document.createElement('div');
    panel.className='mentra-desktop-menu';
    panel.setAttribute('aria-hidden','true');
    const scrim=document.createElement('div');
    scrim.className='mentra-nav-scrim';
    document.body.appendChild(scrim);
    document.body.appendChild(panel);

    let closeTimer=null, current=null;
    const resolveHref=(href)=>/^https?:\/\//.test(href)?href:MENTRA_VN.home+href.replace(/^\//,'');
    const externalIcon=`<svg class="mentra-desktop-menu__external" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M6 3.5H3.75A1.25 1.25 0 0 0 2.5 4.75v7.5a1.25 1.25 0 0 0 1.25 1.25h7.5a1.25 1.25 0 0 0 1.25-1.25V10"/><path d="M9 2.5h4.5V7"/><path d="m13.25 2.75-6.5 6.5"/></svg>`;

    const positionPanel=(trigger)=>{
      const h=header.getBoundingClientRect();
      const tr=trigger.getBoundingClientRect();
      const left=Math.max(24,Math.min(window.innerWidth-420,Math.round(tr.left-20)));
      panel.style.setProperty('--mentra-menu-top',Math.round(h.bottom)+'px');
      panel.style.setProperty('--mentra-menu-content-left',left+'px');
      scrim.style.setProperty('--mentra-menu-top',Math.round(h.bottom)+'px');
    };

    const renderGroup=(group)=>`<div class="mentra-desktop-menu__group"><p class="mentra-desktop-menu__eyebrow">${group.title}</p><div class="mentra-desktop-menu__simple-links">${group.links.map(([title,href,external])=>`<a class="mentra-desktop-menu__simple-link" href="${resolveHref(href)}"${external?' target="_blank" rel="noopener noreferrer"':''}><span>${title}</span>${external?externalIcon:''}</a>`).join('')}</div></div>`;
    const render=(key)=>{
      const data=menus[key]; if(!data) return;
      const groups=data.groups.map(renderGroup).join('');
      const visual=data.visual?`<a class="mentra-desktop-menu__visual" href="${resolveHref(data.visual.href)}"><img src="${MENTRA_VN.theme}/${data.visual.src}" alt="${data.visual.label}"><span>${data.visual.label}</span></a>`:'';
      panel.classList.toggle('mentra-desktop-menu--wide',!!data.wide);
      panel.innerHTML=`<div class="mentra-desktop-menu__inner"><div class="mentra-desktop-menu__content">${groups}${visual}</div></div>`;
    };
    const open=(trigger)=>{
      if(closeTimer) clearTimeout(closeTimer);
      const key=trigger.textContent.trim(); if(!menus[key]) return;
      positionPanel(trigger);
      if(current!==trigger){render(key);current=trigger;}
      qsa('.desktop-nav-trigger',rail).forEach(x=>{x.classList.toggle('is-active',x===trigger);x.setAttribute('aria-expanded',String(x===trigger));});
      header.classList.add('mentra-nav-open'); panel.classList.add('is-open'); panel.setAttribute('aria-hidden','false'); scrim.classList.add('is-open');
    };
    const close=()=>{
      qsa('.desktop-nav-trigger',rail).forEach(x=>{x.classList.remove('is-active');x.setAttribute('aria-expanded','false');});
      header.classList.remove('mentra-nav-open'); panel.classList.remove('is-open'); panel.setAttribute('aria-hidden','true');scrim.classList.remove('is-open');current=null;
    };
    const scheduleClose=()=>{if(closeTimer)clearTimeout(closeTimer);closeTimer=setTimeout(close,130);};
    qsa('.desktop-nav-trigger',rail).forEach(trigger=>{
      trigger.addEventListener('pointerenter',()=>open(trigger));
      trigger.addEventListener('focus',()=>open(trigger));
      trigger.closest('.desktop-nav-zone')?.addEventListener('pointerleave',scheduleClose);
    });
    rail.addEventListener('pointerenter',()=>{if(closeTimer)clearTimeout(closeTimer)});
    panel.addEventListener('pointerenter',()=>{if(closeTimer)clearTimeout(closeTimer)});
    panel.addEventListener('pointerleave',scheduleClose);
    scrim.addEventListener('pointerenter',scheduleClose);
    document.addEventListener('keydown',e=>{if(e.key==='Escape')close();});
    window.addEventListener('resize',()=>{if(window.innerWidth<1024)close(); else if(panel.classList.contains('is-open')&&current) positionPanel(current);});
  }

  function initHeroVideo(){
    qsa('video[data-hero="true"]').forEach(v=>{v.muted=true;v.loop=true;v.playsInline=true;const p=v.play();if(p&&p.catch)p.catch(()=>{});});
  }

  function initMentraLiveIntro(){
    const v=qs('video[src*="animation_website.mp4"]');
    if(!v) return;
    v.muted=true;
    v.playsInline=true;
    v.preload='auto';
    v.loop=true;
    v.setAttribute('autoplay','');
    v.setAttribute('loop','');
    const play=()=>{const p=v.play();if(p&&p.catch)p.catch(()=>{});};
    if('IntersectionObserver' in window){
      const io=new IntersectionObserver(entries=>entries.forEach(entry=>{
        if(entry.isIntersecting) play();
        else v.pause();
      }),{threshold:.2,rootMargin:'100px 0px 100px'});
      io.observe(v);
    }else play();
  }

  function initB2BVideos(){
    const files=['Mechanic_MentraAI_B2B.mp4','HVAC_Videocall_B2B.mp4','Delivery_Navigation_B2B.mp4'];
    const icons={
      pause:'<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5v14M16 5v14"/></svg>',
      play:'<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m8 5 11 7-11 7Z" fill="rgba(255,255,255,.98)" stroke="rgba(255,255,255,.98)"/></svg>',
      mute:'<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M11 5 6.5 9H3v6h3.5L11 19V5Z"/><path d="m16 9 5 6M21 9l-5 6"/></svg>',
      sound:'<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M11 5 6.5 9H3v6h3.5L11 19V5Z"/><path d="M15 9.5a4 4 0 0 1 0 5M18 7a7 7 0 0 1 0 10"/></svg>',
      fullscreen:'<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 3H3v5M16 3h5v5M8 21H3v-5M16 21h5v-5"/></svg>'
    };

    qsa('.home-b2b-video-panel').forEach((panel,i)=>{
      const frame=qs('.home-b2b-video-frame',panel);
      const shell=qs('.home-b2b-video-element',panel);
      if(!frame||!shell||!files[i]) return;

      let v=qs('video',shell);
      if(!v){
        v=document.createElement('video');
        v.src=MENTRA_VN.theme+'/assets/videos/'+files[i];
        shell.appendChild(v);
      }
      v.muted=true;
      v.loop=true;
      v.playsInline=true;
      v.autoplay=true;
      v.preload='metadata';
      v.setAttribute('aria-hidden','true');

      if(!qs('.home-b2b-video-controls',frame)){
        const controls=document.createElement('div');
        controls.className='home-b2b-video-controls';
        controls.innerHTML=`
          <button class="home-b2b-video-control home-b2b-video-control--play" type="button" aria-label="Tạm dừng video">${icons.pause}</button>
          <button class="home-b2b-video-control home-b2b-video-control--mute" type="button" aria-label="Bật âm thanh">${icons.mute}</button>
          <button class="home-b2b-video-control home-b2b-video-control--fullscreen" type="button" aria-label="Xem toàn màn hình">${icons.fullscreen}</button>`;
        frame.appendChild(controls);
        const play=qs('.home-b2b-video-control--play',controls);
        const mute=qs('.home-b2b-video-control--mute',controls);
        const full=qs('.home-b2b-video-control--fullscreen',controls);
        play.addEventListener('click',()=>{
          if(v.paused){const promise=v.play();if(promise&&promise.catch)promise.catch(()=>{});play.innerHTML=icons.pause;play.setAttribute('aria-label','Tạm dừng video');}
          else{v.pause();play.innerHTML=icons.play;play.setAttribute('aria-label','Phát video');}
        });
        mute.addEventListener('click',()=>{
          v.muted=!v.muted;
          mute.innerHTML=v.muted?icons.mute:icons.sound;
          mute.setAttribute('aria-label',v.muted?'Bật âm thanh':'Tắt âm thanh');
        });
        full.addEventListener('click',()=>{
          if(document.fullscreenElement){document.exitFullscreen?.();return;}
          (frame.requestFullscreen||v.requestFullscreen)?.call(frame.requestFullscreen?frame:v);
        });
      }

      if('IntersectionObserver' in window){
        const io=new IntersectionObserver(es=>es.forEach(en=>{
          if(en.isIntersecting){const promise=v.play();if(promise&&promise.catch)promise.catch(()=>{});}
          else v.pause();
        }),{threshold:.22});
        io.observe(v);
      }else{
        const promise=v.play();if(promise&&promise.catch)promise.catch(()=>{});
      }
    });
  }

  function initFAQ(){
    const faqRoot=qs('.home-faq-section');
    if(!faqRoot) return;

    const data={
      'platform-&-sdk':[
        ['MentraOS là gì?','MentraOS là hệ điều hành mã nguồn mở dành cho kính thông minh. Nền tảng cung cấp SDK, kho ứng dụng và hỗ trợ nhiều phần cứng, trong đó có Mentra Live.'],
        ['Nhà phát triển có thể xây dựng ứng dụng cho Mentra Live không?','Có. Mentra Live được thiết kế cho nhà phát triển xây dựng ứng dụng và quy trình riêng bằng MentraOS SDK, bao gồm các trải nghiệm dùng camera, micro, loa và AI.'],
        ['Mentra Miniapp Store là gì?','Mentra Miniapp Store là lớp phân phối ứng dụng dành cho kính chạy MentraOS, giúp người dùng khám phá và cài các miniapp tương thích.'],
        ['Tôi có thể triển khai ứng dụng riêng cho đội ngũ mà không cần xuất bản lên Mentra Miniapp Store không?','Có. Với triển khai nội bộ, doanh nghiệp có thể xây dựng quy trình và ứng dụng riêng cho đội ngũ của mình mà không cần biến chúng thành ứng dụng công khai.']
      ],
      'enterprise-&-pilots':[
        ['Mentra Live có phù hợp cho triển khai doanh nghiệp không?','Có. Mentra Live hướng tới các nhóm hiện trường cần hỗ trợ từ xa, ghi nhận công việc và các quy trình AI tùy chỉnh.'],
        ['Có thể bắt đầu bằng dự án thử nghiệm hoặc pilot không?','Có. Bạn có thể bắt đầu với một nhóm nhỏ, kiểm chứng quy trình thực tế rồi mở rộng khi mô hình đã phù hợp.'],
        ['Mentra Live có thể tích hợp với công cụ nội bộ không?','MentraOS là nền tảng mở, vì vậy nhà phát triển có thể xây dựng tích hợp phù hợp với hệ thống và quy trình của doanh nghiệp.'],
        ['Tôi cần trao đổi về triển khai số lượng lớn ở đâu?','Hãy dùng mục Liên hệ kinh doanh trên website để trao đổi về nhu cầu triển khai, tích hợp và quy mô đội ngũ.']
      ],
      'hardware-&-specs':[
        ['Mentra Live nặng bao nhiêu?','Mentra Live có trọng lượng 43 g.'],
        ['Mentra Live có những phần cứng chính nào?','Kính có camera góc nhìn 119°, ba micro, âm thanh stereo, các nút điều khiển và thanh vuốt trên gọng.'],
        ['Pin và phương thức sạc của Mentra Live như thế nào?','Kính có pin 260 mAh và hỗ trợ cáp sạc khi đang đeo cùng hộp sạc 2.200 mAh để kéo dài thời gian sử dụng.'],
        ['Điện thoại nào tương thích?','Trang thông số của Mentra liệt kê khả năng tương thích với iOS 15.1 trở lên và Android 12 trở lên.']
      ],
      'data-&-privacy':[
        ['MentraOS có phải mã nguồn mở không?','Có. MentraOS được phát triển theo hướng mã nguồn mở để nhà phát triển và doanh nghiệp có khả năng kiểm soát sâu hơn đối với nền tảng.'],
        ['Doanh nghiệp có thể kiểm soát ứng dụng và dữ liệu của mình không?','Kiến trúc mở của MentraOS được thiết kế để doanh nghiệp tự xây dựng ứng dụng, quy trình và cách dữ liệu được xử lý theo yêu cầu triển khai của mình.'],
        ['Ứng dụng có thể sử dụng camera và micro không?','Các ứng dụng được xây dựng cho Mentra Live có thể sử dụng những khả năng phần cứng mà MentraOS SDK cung cấp, tùy theo quyền và cách triển khai của ứng dụng.'],
        ['Có thể dùng ứng dụng riêng thay vì ứng dụng công khai không?','Có. Các ứng dụng nội bộ có thể được dùng cho quy trình riêng của đội ngũ mà không nhất thiết phải phát hành công khai.']
      ],
      'ordering':[
        ['Tôi có thể đặt Mentra Live trực tuyến không?','Có. Mentra cung cấp luồng đặt Mentra Live trực tiếp từ website; bản Việt Nam có thể nối nút mua hàng với WooCommerce.'],
        ['Hộp sạc có được hỗ trợ không?','Mentra Live hỗ trợ hộp sạc 2.200 mAh và cáp sạc khi đang đeo cho các phiên sử dụng dài.'],
        ['Có tròng kính theo độ không?','Có. Mentra Live được thiết kế để hỗ trợ giải pháp tròng kính theo độ; xem trang Tròng kính để biết thêm chi tiết.'],
        ['Tôi cần báo giá cho doanh nghiệp thì làm thế nào?','Với nhu cầu số lượng lớn hoặc triển khai doanh nghiệp, hãy sử dụng biểu mẫu Liên hệ kinh doanh để nhận tư vấn phù hợp.']
      ]
    };

    const tabs=qsa('.home-faq-tab',faqRoot);
    const panel=qs('.home-faq-panel',faqRoot);
    if(!tabs.length||!panel) return;

    const slugFor=(tab)=>{
      const id=tab.id||'';
      return id.replace(/^home-faq-tab-/,'').toLowerCase();
    };
    const render=(tab)=>{
      const slug=slugFor(tab);
      const items=data[slug]||data['platform-&-sdk'];
      panel.setAttribute('aria-labelledby',tab.id);
      panel.id='home-faq-tabpanel-'+slug;
      panel.innerHTML=items.map((item,index)=>{
        const qid='home-faq-btn-'+slug+'-'+index;
        const aid='home-faq-answer-'+slug+'-'+index;
        return `<div class="home-faq-item${index===0?' is-open':''}"><button aria-controls="${aid}" aria-expanded="${index===0?'true':'false'}" class="home-faq-question" id="${qid}" type="button"><span>${item[0]}</span><span aria-hidden="true" class="home-faq-toggle">${index===0?'−':'+'}</span></button><div aria-labelledby="${qid}" class="home-faq-answer" id="${aid}" role="region"><p>${item[1]}</p></div></div>`;
      }).join('');
      bindQuestions();
    };
    const bindQuestions=()=>{
      qsa('.home-faq-item',panel).forEach(item=>{
        const btn=qs('.home-faq-question',item);
        const toggle=qs('.home-faq-toggle',item);
        if(!btn) return;
        btn.addEventListener('click',()=>{
          const open=item.classList.toggle('is-open');
          btn.setAttribute('aria-expanded',String(open));
          if(toggle) toggle.textContent=open?'−':'+';
        });
      });
    };

    tabs.forEach(tab=>{
      tab.addEventListener('click',()=>{
        tabs.forEach(x=>{x.classList.remove('is-active');x.setAttribute('aria-selected','false');x.setAttribute('tabindex','-1');});
        tab.classList.add('is-active');
        tab.setAttribute('aria-selected','true');
        tab.setAttribute('tabindex','0');
        render(tab);
      });
      tab.addEventListener('keydown',e=>{
        if(!['ArrowDown','ArrowUp','ArrowRight','ArrowLeft'].includes(e.key)) return;
        e.preventDefault();
        const idx=tabs.indexOf(tab);
        const next=(e.key==='ArrowDown'||e.key==='ArrowRight')?(idx+1)%tabs.length:(idx-1+tabs.length)%tabs.length;
        tabs[next].focus();tabs[next].click();
      });
    });

    const selected=tabs.find(t=>t.getAttribute('aria-selected')==='true')||tabs[0];
    render(selected);
  }


  function initRxFAQ(){
    qsa('.rx-faq').forEach(root=>{
      const items=qsa('.rx-faq-item',root);
      items.forEach(item=>{
        const button=qs('.rx-faq-summary',item);
        const answer=qs('.rx-faq-answer',item);
        if(!button||!answer) return;
        const sync=(open)=>{
          item.setAttribute('data-open',String(open));
          button.setAttribute('aria-expanded',String(open));
          answer.hidden=!open;
        };
        sync(item.getAttribute('data-open')==='true');
        button.addEventListener('click',()=>{
          const willOpen=item.getAttribute('data-open')!=='true';
          items.forEach(other=>{
            const b=qs('.rx-faq-summary',other), a=qs('.rx-faq-answer',other);
            if(!b||!a) return;
            other.setAttribute('data-open','false');
            b.setAttribute('aria-expanded','false');
            a.hidden=true;
          });
          sync(willOpen);
        });
      });
    });
  }

  function initNewsletter(){
    qsa('form[data-mentra-newsletter="1"]').forEach(form=>{
      const email=qs('input[type="email"]',form), btn=qs('button[type="submit"]',form); if(!email||!btn) return;
      const sync=()=>btn.disabled=!email.value.trim()||!email.checkValidity(); sync(); email.addEventListener('input',sync);
      form.addEventListener('submit',async e=>{
        e.preventDefault(); if(!email.checkValidity()) return email.reportValidity();
        btn.disabled=true; const old=btn.innerHTML; btn.textContent='Đang gửi…';
        try{const body=new URLSearchParams({action:'mentra_vn_newsletter',nonce:MENTRA_VN.nonce,email:email.value.trim()}); const r=await fetch(MENTRA_VN.ajax,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded;charset=UTF-8'},body}); const j=await r.json(); btn.textContent=j.success?'Đã đăng ký':'Thử lại'; if(j.success) email.value='';}
        catch(_){btn.textContent='Thử lại';}
        setTimeout(()=>{btn.innerHTML=old;sync()},1800);
      });
    });
  }

  function initReveal(){
    if(matchMedia('(prefers-reduced-motion: reduce)').matches){qsa('[data-mentra-reveal]').forEach(x=>x.classList.add('is-visible'));return;}
    const io=new IntersectionObserver(es=>es.forEach(e=>{if(e.isIntersecting){e.target.classList.add('is-visible');io.unobserve(e.target)}}),{threshold:.08,rootMargin:'0px 0px -6%'});
    qsa('[data-mentra-reveal]').forEach(x=>io.observe(x));
  }


  function initContact(){
    qsa('form[data-mentra-contact="1"]').forEach(form=>{
      const submit=qs('button[type="submit"]',form);
      form.addEventListener('submit',async e=>{
        e.preventDefault();
        const name=qs('#contact-name,input[name="name"]',form);
        const email=qs('#contact-email,input[type="email"]',form);
        const company=qs('#contact-company,input[name="company"]',form);
        const subject=qs('#contact-subject,select[name="subject"],select[name="topic"]',form);
        const message=qs('#contact-message,textarea',form);
        if(!name||!email||!message||!name.value.trim()||!email.checkValidity()||!message.value.trim()){
          if(email && !email.checkValidity()) email.reportValidity();
          return;
        }
        const old=submit?submit.innerHTML:''; if(submit){submit.disabled=true;submit.textContent='Đang gửi…';}
        try{
          const body=new URLSearchParams({action:'mentra_vn_contact_ajax',nonce:MENTRA_VN.nonce,name:name.value.trim(),email:email.value.trim(),company:company?company.value.trim():'',subject:subject?subject.value:'Liên hệ',message:message.value.trim()});
          const r=await fetch(MENTRA_VN.ajax,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded;charset=UTF-8'},body});
          const j=await r.json();
          if(submit) submit.textContent=j.success?'Đã gửi':'Gửi lại';
          if(j.success){form.reset();}
        }catch(_){if(submit)submit.textContent='Gửi lại';}
        setTimeout(()=>{if(submit){submit.innerHTML=old;submit.disabled=false}},1800);
      });
    });
  }


  function initHydratedTextFallbacks(){
    const spans=qsa('.mentra-vn-index span[style*="opacity:0"][style*="translateY(20px)"]');
    if(!spans.length) return;
    const revealGroup=(target)=>{
      const group=target.closest('p') ? qsa('span[style*="opacity:0"]',target.closest('p')) : [target];
      group.forEach((el,i)=>setTimeout(()=>{el.style.opacity='1';el.style.transform='none';},i*105));
    };
    if(!('IntersectionObserver' in window)){spans.forEach(revealGroup);return;}
    const seen=new WeakSet();
    const io=new IntersectionObserver(entries=>entries.forEach(entry=>{
      if(entry.isIntersecting && !seen.has(entry.target)){seen.add(entry.target);revealGroup(entry.target);io.unobserve(entry.target);}
    }),{threshold:.2});
    spans.forEach(el=>io.observe(el));
  }

  function initExternalSourceArtifacts(){
    // Hydrogen SSR leaves a few disabled buttons and empty video shells that React used to hydrate.
    // The local WordPress runtime restores only the visible interactions needed by the mirrored pages.
    qsa('a[href="#"],a[href=""]').forEach(a=>a.addEventListener('click',e=>e.preventDefault()));
  }


  function initNewsroomFilters(){
    const root=qs('[data-newsroom-grid="1"]');
    if(!root) return;
    const items=qsa('[data-newsroom-item="1"]',root);
    const tabs=qsa('[data-newsroom-filter]');
    const search=qs('[data-newsroom-search="1"]');
    const empty=qs('#mentra-newsroom-empty');
    if(!items.length||!tabs.length) return;

    let active=(tabs.find(t=>t.getAttribute('aria-selected')==='true')||tabs[0]).dataset.newsroomFilter||'all';
    let query='';

    const syncTab=(tab,isActive)=>{
      tab.setAttribute('aria-selected',String(isActive));
      tab.setAttribute('tabindex',isActive?'0':'-1');
      tab.style.color=isActive?'var(--ink-primary)':'var(--ink-tertiary)';
      tab.style.fontWeight=isActive?'600':'400';
      const spans=qsa('span',tab);
      if(spans[0]) spans[0].style.color=isActive?'var(--brand)':'var(--ink-tertiary)';
      const indicator=spans[spans.length-1];
      if(indicator) indicator.style.background=isActive?'var(--brand)':'transparent';
    };

    const apply=()=>{
      let shown=0;
      items.forEach(item=>{
        const kind=item.dataset.newsroomKind||'blogs';
        const text=((item.dataset.newsroomSearch||'')+' '+(item.textContent||'')).toLowerCase();
        const categoryMatch=active==='all'||kind===active;
        const searchMatch=!query||text.includes(query);
        const visible=categoryMatch&&searchMatch;
        item.hidden=!visible;
        item.style.display=visible?'':'none';
        if(visible) shown++;
      });
      if(empty) empty.hidden=shown!==0;
    };

    tabs.forEach((tab,index)=>{
      syncTab(tab,(tab.dataset.newsroomFilter||'')===active);
      tab.addEventListener('click',()=>{
        active=tab.dataset.newsroomFilter||'all';
        tabs.forEach(t=>syncTab(t,t===tab));
        apply();
      });
      tab.addEventListener('keydown',e=>{
        if(!['ArrowLeft','ArrowRight','Home','End'].includes(e.key)) return;
        e.preventDefault();
        let next=index;
        if(e.key==='ArrowRight') next=(index+1)%tabs.length;
        if(e.key==='ArrowLeft') next=(index-1+tabs.length)%tabs.length;
        if(e.key==='Home') next=0;
        if(e.key==='End') next=tabs.length-1;
        tabs[next].focus();
        tabs[next].click();
      });
    });

    if(search){
      search.addEventListener('input',()=>{query=search.value.trim().toLowerCase();apply();});
      document.addEventListener('keydown',e=>{
        if((e.metaKey||e.ctrlKey)&&e.key.toLowerCase()==='k'){
          e.preventDefault(); search.focus(); search.select();
        }
      });
    }
    apply();
  }

  document.addEventListener('DOMContentLoaded',()=>{initHeader();initDesktopMegaMenu();initHeroVideo();initMentraLiveIntro();initB2BVideos();initFAQ();initRxFAQ();initNewsletter();initContact();initReveal();initHydratedTextFallbacks();initExternalSourceArtifacts();initNewsroomFilters();});
})();
