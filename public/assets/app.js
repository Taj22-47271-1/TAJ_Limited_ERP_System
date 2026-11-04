(function(){
  const d=document.getElementById('drawer'),o=document.getElementById('drawerOverlay');
  const open=()=>{d?.classList.add('open');o?.classList.add('open');document.body.classList.add('nav-open')};
  const close=()=>{d?.classList.remove('open');o?.classList.remove('open');document.body.classList.remove('nav-open')};
  document.getElementById('drawerToggle')?.addEventListener('click',open);
  document.getElementById('drawerClose')?.addEventListener('click',close);
  o?.addEventListener('click',close);
  d?.querySelectorAll('a').forEach(a=>a.addEventListener('click',()=>{if(window.innerWidth<=930)close()}));
  document.addEventListener('keydown',e=>{if(e.key==='Escape')close()});
  window.addEventListener('resize',()=>{if(window.innerWidth>930)close()});
  setTimeout(()=>document.querySelectorAll('.toast').forEach(x=>x.remove()),4500);
})();

window.setupDynamicRows=function(containerId,tplId,buttonId,prefix){
  const c=document.getElementById(containerId),t=document.getElementById(tplId),b=document.getElementById(buttonId);
  if(!c||!t||!b)return;
  let i=0;
  const add=()=>{
    const n=t.content.firstElementChild.cloneNode(true);
    n.querySelectorAll('[data-name]').forEach(e=>e.name=`${prefix}[${i}][${e.dataset.name}]`);
    n.querySelector('.remove-row').onclick=()=>n.remove();
    c.appendChild(n);
    i++;
  };
  b.onclick=add;
  add();
};

window.initPOS=function(opts={}){
  const currency=opts.currency||'৳';
  const g=document.getElementById('productGrid');
  const s=document.getElementById('productSearch');
  const l=document.getElementById('cartLines');
  const inp=document.getElementById('cartInputs');
  const disc=document.getElementById('discount');
  const tax=document.getElementById('tax');
  const paid=document.getElementById('paidAmount');
  const sizeDialog=document.getElementById('posSizeDialog');
  const sizeOptions=document.getElementById('posSizeOptions');
  const sizeProductName=document.getElementById('posSizeProductName');
  if(!g)return;

  const cart=new Map();
  const lineKey=(id,size)=>`${id}:${(size||'_').toLowerCase()}`;
  const productQty=id=>Array.from(cart.values()).filter(x=>x.id===id).reduce((sum,x)=>sum+x.qty,0);

  function add(tile,size=null){
    const id=tile.dataset.id;
    const stock=parseFloat(tile.dataset.stock);
    if(productQty(id)+1>stock){
      alert(`Only ${stock} in stock across all sizes.`);
      return;
    }
    const key=lineKey(id,size);
    const x=cart.get(key)||{
      id,
      name:tile.dataset.name,
      price:parseFloat(tile.dataset.price),
      stock,
      size:size||'',
      qty:0
    };
    x.qty++;
    cart.set(key,x);
    render();
  }

  function chooseSize(tile){
    let sizes=[];
    try{ sizes=JSON.parse(tile.dataset.sizes||'[]'); }catch(e){ sizes=[]; }
    if(!sizes.length){ add(tile); return; }

    if(!sizeDialog||!sizeOptions){
      const selected=window.prompt(`Select size for ${tile.dataset.name}: ${sizes.join(', ')}`,sizes[0]);
      if(selected&&sizes.some(x=>x.toLowerCase()===selected.trim().toLowerCase())) add(tile,sizes.find(x=>x.toLowerCase()===selected.trim().toLowerCase()));
      return;
    }

    sizeProductName.textContent=tile.dataset.name;
    sizeOptions.innerHTML='';
    sizes.forEach(size=>{
      const b=document.createElement('button');
      b.type='button';
      b.className='pos-size-option';
      b.textContent=size;
      b.onclick=()=>{ add(tile,size); sizeDialog.close(); };
      sizeOptions.appendChild(b);
    });
    sizeDialog.showModal();
  }

  function render(){
    l.innerHTML='';
    inp.innerHTML='';
    let subtotal=0,i=0;
    if(!cart.size) l.innerHTML='<div class="empty-state">Select a product to begin.</div>';

    cart.forEach((x,key)=>{
      subtotal+=x.qty*x.price;
      const r=document.createElement('div');
      r.className='cart-row';
      r.innerHTML=`<div><strong>${x.name}</strong>${x.size?`<small class="cart-size">Size: ${x.size}</small>`:''}<small>${currency}${x.price.toFixed(2)}</small></div><input type="number" min="0.01" max="${x.stock}" step="0.01" value="${x.qty}"><input type="number" min="0" step="0.01" value="${x.price}"><button type="button">×</button>`;
      const q=r.children[1],p=r.children[2],del=r.children[3];
      q.onchange=()=>{
        const other=productQty(x.id)-x.qty;
        x.qty=Math.max(.01,Math.min(x.stock-other,parseFloat(q.value)||1));
        render();
      };
      p.onchange=()=>{x.price=Math.max(0,parseFloat(p.value)||0);render()};
      del.onclick=()=>{cart.delete(key);render()};
      l.appendChild(r);

      [['product_id',x.id],['selected_size',x.size],['quantity',x.qty],['unit_price',x.price]].forEach(([k,v])=>{
        const h=document.createElement('input');
        h.type='hidden';
        h.name=`items[${i}][${k}]`;
        h.value=v;
        inp.appendChild(h);
      });
      i++;
    });

    const total=Math.max(0,subtotal-(parseFloat(disc.value)||0)+(parseFloat(tax.value)||0));
    document.getElementById('subtotalText').textContent=currency+subtotal.toFixed(2);
    document.getElementById('totalText').textContent=currency+total.toFixed(2);
    if(!paid.dataset.touched) paid.value=total.toFixed(2);
  }

  g.querySelectorAll('.product-tile').forEach(t=>t.onclick=()=>chooseSize(t));

  function filter(){
    const q=s.value.trim().toLowerCase();
    let exact=null;
    g.querySelectorAll('.product-tile').forEach(t=>{
      const h=(t.dataset.name+' '+t.dataset.sku+' '+t.dataset.barcode).toLowerCase();
      t.style.display=h.includes(q)?'flex':'none';
      if(q&&t.dataset.barcode.toLowerCase()===q) exact=t;
    });
    if(exact){ chooseSize(exact); s.value=''; filter(); }
  }

  s.addEventListener('input',filter);
  s.addEventListener('keydown',e=>{if(e.key==='Enter'){e.preventDefault();filter()}});
  disc.addEventListener('input',render);
  tax.addEventListener('input',render);
  paid.addEventListener('input',()=>paid.dataset.touched='1');
  document.getElementById('posForm').addEventListener('submit',e=>{
    if(!cart.size){e.preventDefault();alert('Add at least one product.')}
  });
};

window.initProductMagnifier=function(){
  document.querySelectorAll('[data-product-zoom]').forEach(stage=>{
    const img=stage.querySelector('img');
    if(!img)return;
    const move=e=>{
      const rect=stage.getBoundingClientRect();
      const x=((e.clientX-rect.left)/rect.width)*100;
      const y=((e.clientY-rect.top)/rect.height)*100;
      stage.style.setProperty('--zoom-x',`${Math.max(0,Math.min(100,x))}%`);
      stage.style.setProperty('--zoom-y',`${Math.max(0,Math.min(100,y))}%`);
    };
    stage.addEventListener('pointermove',move);
    stage.addEventListener('pointerleave',()=>{
      stage.style.setProperty('--zoom-x','50%');
      stage.style.setProperty('--zoom-y','50%');
    });
  });

  const lightbox=document.getElementById('productImageLightbox');
  const trigger=document.querySelector('[data-open-product-image]');
  if(lightbox&&trigger){
    trigger.addEventListener('click',()=>lightbox.showModal());
    lightbox.addEventListener('click',e=>{if(e.target===lightbox)lightbox.close()});
  }
};

/* ==========================================================================
   Storefront Interactive Cart & Flying Animations (as in video)
   ========================================================================== */
function flyProductImage(sourceImg, targetEl) {
  if (!targetEl) return;
  
  const clone = document.createElement('div');
  clone.className = 'vc-flying-clone';
  
  let startX = window.innerWidth / 2 - 30;
  let startY = window.innerHeight / 2 - 30;
  
  if (sourceImg) {
    const srcRect = sourceImg.getBoundingClientRect();
    if (srcRect.width > 0 && srcRect.height > 0) {
      startX = srcRect.left + (srcRect.width / 2) - 30;
      startY = srcRect.top + (srcRect.height / 2) - 30;
      const img = document.createElement('img');
      img.src = sourceImg.src;
      clone.appendChild(img);
    }
  }
  
  if (!clone.children.length) {
    const placeholder = document.createElement('div');
    placeholder.style.cssText = 'width:100%;height:100%;background:#111;color:#fff;display:grid;place-items:center;font-weight:bold;font-size:18px;border-radius:12px;';
    placeholder.textContent = '🛍️';
    clone.appendChild(placeholder);
  }

  clone.style.left = `${startX}px`;
  clone.style.top = `${startY}px`;
  clone.style.width = '60px';
  clone.style.height = '60px';
  document.body.appendChild(clone);

  // Force reflow
  void clone.offsetWidth;

  const tgtRect = targetEl.getBoundingClientRect();
  const endX = tgtRect.left + (tgtRect.width / 2) - 30;
  const endY = tgtRect.top + (tgtRect.height / 2) - 30;
  const deltaX = endX - startX;
  const deltaY = endY - startY;

  clone.style.transform = `translate(${deltaX}px, ${deltaY}px) scale(0.25)`;
  clone.style.opacity = '0.2';

  setTimeout(() => {
    clone.remove();
    const badge = targetEl.querySelector('.cart-badge-count') || document.querySelector('.cart-badge-count');
    if (badge) {
      badge.classList.remove('vc-badge-pop');
      void badge.offsetWidth;
      badge.classList.add('vc-badge-pop');
    }
  }, 700);
}

function renderMiniCart(data) {
  const currency = '৳';
  
  // Header badge
  document.querySelectorAll('.cart-badge-count').forEach(badge => {
    badge.textContent = data.cart_count || 0;
  });

  // Floating bottom bar
  const floatingBar = document.getElementById('vcFloatingCartBar');
  const floatingCount = document.getElementById('vcFloatingCountText');
  const floatingTotal = document.getElementById('vcFloatingTotalText');
  const floatingThumbs = document.getElementById('vcFloatingThumbs');

  if (floatingCount) {
    floatingCount.textContent = `${data.cart_count} ${data.cart_count === 1 ? 'item' : 'items'}`;
  }
  if (floatingTotal) {
    floatingTotal.textContent = `${currency}${parseFloat(data.total || 0).toFixed(2)}`;
  }
  if (floatingThumbs && Array.isArray(data.items)) {
    floatingThumbs.innerHTML = '';
    data.items.slice(0, 3).forEach(item => {
      const span = document.createElement('span');
      span.className = 'vc-floating-thumb';
      if (item.image) {
        const img = document.createElement('img');
        img.src = item.image;
        img.alt = item.name;
        span.appendChild(img);
      } else {
        span.textContent = (item.name || 'P').charAt(0).toUpperCase();
        span.style.fontSize = '12px';
        span.style.fontWeight = 'bold';
      }
      floatingThumbs.appendChild(span);
    });
  }

  if (floatingBar) {
    if (data.cart_count > 0) {
      floatingBar.classList.add('is-visible');
    } else {
      floatingBar.classList.remove('is-visible');
    }
  }

  // Mini-cart drawer
  const drawerCount = document.getElementById('vcDrawerCountBadge');
  const drawerSubtotal = document.getElementById('vcDrawerSubtotal');
  const drawerCheckoutTotal = document.getElementById('vcDrawerCheckoutTotal');
  const drawerItems = document.getElementById('vcMiniCartItems');

  if (drawerCount) drawerCount.textContent = `(${data.cart_count || 0})`;
  if (drawerSubtotal) drawerSubtotal.textContent = `${currency}${parseFloat(data.subtotal || 0).toFixed(2)}`;
  if (drawerCheckoutTotal) drawerCheckoutTotal.textContent = `${currency}${parseFloat(data.total || 0).toFixed(2)} ›`;

  if (drawerItems) {
    drawerItems.innerHTML = '';
    if (!data.items || !data.items.length) {
      drawerItems.innerHTML = '<div style="text-align:center;padding:36px 16px;color:#9ca3af;font-size:13px;">Your cart is empty.</div>';
    } else {
      data.items.forEach(item => {
        const div = document.createElement('div');
        div.className = 'vc-drawer-item';
        div.innerHTML = `
          <div class="vc-drawer-item-img">
            ${item.image ? `<img src="${item.image}" alt="${item.name}">` : `<strong>${(item.name||'P').charAt(0).toUpperCase()}</strong>`}
          </div>
          <div class="vc-drawer-item-info">
            <strong>${item.name}</strong>
            <small>${item.size ? `Size: ${item.size} · ` : ''}Qty: ${item.quantity}</small>
          </div>
          <div class="vc-drawer-item-price">
            ${currency}${parseFloat(item.line_total || 0).toFixed(2)}
          </div>
        `;
        drawerItems.appendChild(div);
      });
    }
  }
}

window.initStorefrontCart = function() {
  const drawer = document.getElementById('vcMiniCartDrawer');
  const overlay = document.getElementById('vcMiniCartOverlay');
  const closeBtn = document.getElementById('vcMiniCartClose');
  const floatingTrigger = document.getElementById('vcFloatingCartTrigger');

  const openDrawer = (e) => {
    if (e) e.preventDefault();
    drawer?.classList.add('open');
    overlay?.classList.add('open');
    document.body.style.overflow = 'hidden';
  };

  const closeDrawer = () => {
    drawer?.classList.remove('open');
    overlay?.classList.remove('open');
    document.body.style.overflow = '';
  };

  floatingTrigger?.addEventListener('click', openDrawer);
  closeBtn?.addEventListener('click', closeDrawer);
  overlay?.addEventListener('click', closeDrawer);

  document.addEventListener('keydown', e => {
    if (e.key === 'Escape' && drawer?.classList.contains('open')) {
      closeDrawer();
    }
  });

  // Sync cart state on initial load
  if (document.getElementById('vcFloatingCartBar')) {
    fetch('/cart/summary', {
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(r => r.ok ? r.json() : null)
    .then(data => {
      if (data && data.cart_count > 0) {
        renderMiniCart(data);
      }
    })
    .catch(() => {});
  }

  // Intercept add-to-cart forms
  document.addEventListener('submit', function(e) {
    const form = e.target;
    if (!form || !form.action || !form.action.includes('/cart/')) return;
    if (form.id === 'posForm') return; // Do not intercept POS

    const submitter = e.submitter;
    if (submitter && (submitter.name === 'buy_now' || submitter.value === '1')) {
      // Allow regular redirect to checkout on "Buy it now"
      return;
    }

    // Only intercept cart.add
    if (!form.action.match(/\/cart\/\d+/)) return;

    e.preventDefault();

    const btn = submitter || form.querySelector('button[type="submit"]') || form.querySelector('.vc-add-button, .fashion-add-cart');
    const sourceCard = form.closest('.vc-product-card') || form.closest('.fashion-product-detail') || form.closest('.vc-hero-product');
    const sourceImg = sourceCard?.querySelector('img') || form.querySelector('img');
    const targetEl = document.getElementById('headerCartBtn') || document.getElementById('vcFloatingCartBar');

    // Flying animation
    flyProductImage(sourceImg, targetEl);

    // Green button feedback
    if (btn) {
      btn.classList.add('is-added');
      const originalText = btn.innerHTML;
      btn.innerHTML = '✔ Added';
      setTimeout(() => {
        btn.classList.remove('is-added');
        btn.innerHTML = originalText;
      }, 1500);
    }

    const formData = new FormData(form);
    fetch(form.action, {
      method: 'POST',
      body: formData,
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
      }
    })
    .then(async res => {
      const data = await res.json().catch(() => null);
      if (!res.ok) {
        throw new Error((data && data.message) || 'Error adding product to cart.');
      }
      return data;
    })
    .then(data => {
      if (data && data.success) {
        renderMiniCart(data);
      }
    })
    .catch(err => {
      alert(err.message || 'Could not add to cart.');
    });
  });

  // Animated Order Confirm Delivery Truck Button (Coding.stella style)
  const truckBtn = document.getElementById('orderTruckBtn');
  if (truckBtn) {
    truckBtn.addEventListener('click', function(e) {
      const form = this.closest('form');
      if (!form) return;
      if (!form.reportValidity()) return;
      if (this.classList.contains('animate')) return;

      e.preventDefault();
      this.classList.add('animate');

      setTimeout(() => {
        this.classList.add('done');
      }, 1800);

      setTimeout(() => {
        form.submit();
      }, 2400);
    });
  }
};

document.addEventListener('DOMContentLoaded', () => {
  window.initProductMagnifier();
  window.initStorefrontCart();
});


/* ==========================================================================
   TAJ Atelier: Luxury 3D Clothing & Haute Couture Interactive Controls
   ========================================================================== */
window.initLuxuryClothingShowcase = function() {
  const section = document.getElementById('clothingShowcaseSection');
  if (!section) return;

  // Scene tab switcher
  section.querySelectorAll('.atelier-tab-btn').forEach(btn => {
    btn.addEventListener('click', function() {
      const sceneName = this.dataset.scene;
      const nav = this.closest('.atelier-scene-nav');
      nav.querySelectorAll('.atelier-tab-btn').forEach(b => b.classList.remove('active'));
      this.classList.add('active');

      const activeSlide = section.querySelector('.atelier-product-slide.active') || section.querySelector('.atelier-product-slide');
      if (activeSlide) {
        activeSlide.querySelectorAll('.atelier-scene').forEach(sc => sc.classList.remove('active'));
        const targetScene = activeSlide.querySelector(`.atelier-scene-${sceneName}`);
        if (targetScene) targetScene.classList.add('active');
      }
    });
  });

  // Product carousel navigation
  const slides = Array.from(section.querySelectorAll('.atelier-product-slide'));
  let currentSlideIdx = 0;

  function showSlide(index) {
    if (!slides.length) return;
    currentSlideIdx = (index + slides.length) % slides.length;
    slides.forEach((sl, idx) => {
      sl.classList.toggle('active', idx === currentSlideIdx);
    });

    // Sync active scene
    const activeNavTab = section.querySelector('.atelier-tab-btn.active');
    const sceneName = activeNavTab ? activeNavTab.dataset.scene : 'turntable';
    const currSlide = slides[currentSlideIdx];
    if (currSlide) {
      currSlide.querySelectorAll('.atelier-scene').forEach(sc => sc.classList.remove('active'));
      const sc = currSlide.querySelector(`.atelier-scene-${sceneName}`) || currSlide.querySelector('.atelier-scene-turntable');
      if (sc) sc.classList.add('active');
    }
  }

  section.querySelectorAll('.atelier-prev-btn').forEach(btn => {
    btn.addEventListener('click', () => showSlide(currentSlideIdx - 1));
  });
  section.querySelectorAll('.atelier-next-btn').forEach(btn => {
    btn.addEventListener('click', () => showSlide(currentSlideIdx + 1));
  });

  // Interactive 3D Turntable Parallax (PC & Touch)
  section.querySelectorAll('.atelier-stage-container').forEach(container => {
    const turntable = container.querySelector('.atelier-3d-turntable');
    const halo = container.querySelector('.atelier-halo-ring');
    if (!turntable) return;

    let isInteracting = false;
    let startX = 0, currentRotY = 0;

    const onMove = (clientX, clientY) => {
      if (container.dataset.enable3d === '0') return;
      const rect = container.getBoundingClientRect();
      const xRatio = ((clientX - rect.left) / rect.width) - 0.5;
      const yRatio = ((clientY - rect.top) / rect.height) - 0.5;

      const rotY = xRatio * 26;
      const rotX = -yRatio * 14;

      turntable.style.transform = `perspective(1100px) rotateY(${rotY}deg) rotateX(${rotX}deg)`;
      if (halo) {
        halo.style.transform = `translate(${-xRatio * 16}px, ${-yRatio * 12}px) scale(${1 + Math.abs(xRatio) * 0.05})`;
      }
    };

    container.addEventListener('pointermove', e => {
      onMove(e.clientX, e.clientY);
    });

    container.addEventListener('pointerleave', () => {
      turntable.style.transform = 'perspective(1100px) rotateY(0deg) rotateX(0deg)';
      if (halo) halo.style.transform = 'translate(0px, 0px) scale(1)';
    });

    // Touch drag for mobile
    container.addEventListener('touchstart', e => {
      if (e.touches.length === 1) {
        isInteracting = true;
        startX = e.touches[0].clientX;
      }
    }, { passive: true });

    container.addEventListener('touchmove', e => {
      if (!isInteracting || e.touches.length !== 1) return;
      const diff = e.touches[0].clientX - startX;
      currentRotY = Math.max(-30, Math.min(30, diff * 0.35));
      turntable.style.transform = `perspective(1100px) rotateY(${currentRotY}deg) rotateX(0deg)`;
    }, { passive: true });

    container.addEventListener('touchend', () => {
      isInteracting = false;
      turntable.style.transform = 'perspective(1100px) rotateY(0deg) rotateX(0deg)';
    });
  });

  // Hotspots click/tap toggle
  section.querySelectorAll('.atelier-hotspot').forEach(hotspot => {
    hotspot.addEventListener('click', function(e) {
      e.stopPropagation();
      const targetId = this.dataset.target;
      const card = document.getElementById(targetId);
      const isVisible = card?.classList.contains('is-visible');

      // Close all cards
      section.querySelectorAll('.atelier-hotspot-card').forEach(c => c.classList.remove('is-visible'));
      section.querySelectorAll('.atelier-hotspot').forEach(h => h.classList.remove('is-open'));

      if (!isVisible && card) {
        card.classList.add('is-visible');
        this.classList.add('is-open');
      }
    });
  });

  document.addEventListener('click', () => {
    section.querySelectorAll('.atelier-hotspot-card').forEach(c => c.classList.remove('is-visible'));
    section.querySelectorAll('.atelier-hotspot').forEach(h => h.classList.remove('is-open'));
  });

  // Variant morph swatches & color-specific image switching
  const filterMap = {
    rose: { filter: 'drop-shadow(0 20px 30px rgba(0, 0, 0, 0.7))', ring: 'rgba(245, 208, 126, 0.7)', shadow: '0 0 50px rgba(245, 208, 126, 0.35)' },
    sapphire: { filter: 'hue-rotate(185deg) saturate(1.4) drop-shadow(0 20px 30px rgba(0, 0, 0, 0.7))', ring: 'rgba(43, 89, 195, 0.85)', shadow: '0 0 50px rgba(43, 89, 195, 0.5)' },
    noir: { filter: 'grayscale(1) brightness(0.68) contrast(1.25) drop-shadow(0 20px 30px rgba(0, 0, 0, 0.85))', ring: 'rgba(161, 161, 170, 0.6)', shadow: '0 0 40px rgba(0, 0, 0, 0.8)' },
    emerald: { filter: 'hue-rotate(95deg) saturate(1.35) drop-shadow(0 20px 30px rgba(0, 0, 0, 0.7))', ring: 'rgba(26, 127, 83, 0.85)', shadow: '0 0 50px rgba(26, 127, 83, 0.45)' },
    gold: { filter: 'hue-rotate(32deg) saturate(1.85) brightness(1.1) drop-shadow(0 20px 30px rgba(0, 0, 0, 0.7))', ring: 'rgba(212, 175, 55, 0.9)', shadow: '0 0 55px rgba(212, 175, 55, 0.5)' }
  };

  section.querySelectorAll('.swatch-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      const colorKey = this.dataset.color;
      const title = this.dataset.title || colorKey;
      const customImg = this.dataset.image;
      const productId = this.dataset.targetProduct;

      const slide = this.closest('.atelier-product-slide');
      if (!slide) return;

      // Sync active state across all swatch pickers in this slide
      slide.querySelectorAll('.swatch-btn[data-color="' + colorKey + '"]').forEach(b => b.classList.add('active'));
      slide.querySelectorAll('.swatch-btn:not([data-color="' + colorKey + '"])').forEach(b => b.classList.remove('active'));

      const colorLabel = slide.querySelector('#colorLabel-' + productId);
      if (colorLabel) colorLabel.textContent = title;
      const morphLabel = slide.querySelector('#morphLabel-' + productId);
      if (morphLabel) morphLabel.textContent = title + ' (Selected)';

      const garmentImg = slide.querySelector('#garmentImg-' + productId);
      const morphGarment = slide.querySelector('#morphGarment-' + productId);
      const ring = slide.querySelector('.atelier-morph-ring');
      const cfg = filterMap[colorKey] || filterMap.rose;

      if (customImg && customImg.trim() !== '') {
        [garmentImg, morphGarment].forEach(img => {
          if (!img) return;
          img.style.opacity = '0.2';
          setTimeout(() => {
            img.src = customImg;
            img.style.filter = 'none';
            img.style.opacity = '1';
          }, 150);
        });
      } else {
        if (garmentImg) garmentImg.style.filter = cfg.filter;
        if (morphGarment) morphGarment.style.filter = cfg.filter;
      }

      if (ring) {
        ring.style.borderColor = cfg.ring;
        ring.style.boxShadow = cfg.shadow;
      }
    });
  });
};

document.addEventListener('DOMContentLoaded', () => {
  window.initLuxuryClothingShowcase();
});
