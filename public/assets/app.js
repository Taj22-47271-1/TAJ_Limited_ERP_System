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

document.addEventListener('DOMContentLoaded',()=>window.initProductMagnifier());
