function fmt(n){return n<10?"0"+n:""+n}
function renderDiff(el, target){
const t = new Date(target).getTime();
const now = Date.now();
let diff = Math.floor((t - now)/1000);
const past = diff < 0;
diff = Math.abs(diff);
const days = Math.floor(diff/86400); diff -= days*86400;
const h = Math.floor(diff/3600); diff -= h*3600;
const m = Math.floor(diff/60); const s = diff - m*60;
const text = (past?"+":"-") + days + "日 " + fmt(h)+":"+fmt(m)+":"+fmt(s);
el.textContent = text;
}
function boot(){
document.querySelectorAll('[data-countto]').forEach(el=>{
const target = el.getAttribute('data-countto');
renderDiff(el, target);
setInterval(()=>renderDiff(el, target), 1000);
});
}
if(document.readyState!=='loading') boot();
else document.addEventListener('DOMContentLoaded', boot);


(function(){
  const modal = document.getElementById('dayModal');
  const body  = document.getElementById('modalBody');
  const title = document.getElementById('modalTitle');

  function openModal(dayStr){
    // JSON を取得
    const script = document.getElementById('ev-' + dayStr);
    if (!script) return;
    const events = JSON.parse(script.textContent || '[]');

    // タイトル
    title.textContent = dayStr + ' の予定';

    // 中身を組み立て
    body.innerHTML = '';
    if (events.length === 0){
      body.innerHTML = '<p>この日の予定はありません。</p>';
    } else {
      events.forEach(ev => {
        const d = document.createElement('div');
        d.className = 'modal__item';

        const dot = document.createElement('span');
        dot.className = 'modal__dot';
        dot.style.background = ev.color || '#6c5ce7';

        const wrap = document.createElement('div');
        const title = document.createElement('div');
        title.className = 'modal__title';
        title.innerHTML =
          `<a href="countdown.php?id=${ev.id}">${escapeHtml(ev.title)}</a>` +
          (ev.category === 'special' ? ` <span class="badge-mini">SPECIAL</span>` : '');

        const meta = document.createElement('div');
        meta.className = 'modal__meta';
        meta.textContent = ev.target_at;

        wrap.appendChild(title);
        wrap.appendChild(meta);
        d.appendChild(dot);
        d.appendChild(wrap);
        body.appendChild(d);
      });
    }

    modal.hidden = false;
    document.body.style.overflow = 'hidden';
  }

  function closeModal(){
    modal.hidden = true;
    document.body.style.overflow = '';
  }

  // ドットクリックで開く
  document.addEventListener('click', (e)=>{
    const el = e.target.closest('.event-dots');
    if (!el) return;
    const day = el.getAttribute('data-day');
    if (day) openModal(day);
  });

  // 閉じる
  modal?.addEventListener('click', (e)=>{
    if (e.target.classList.contains('modal__backdrop') ||
        e.target.classList.contains('modal__close')) {
      closeModal();
    }
  });
  document.addEventListener('keydown', (e)=>{
    if (e.key === 'Escape' && !modal.hidden) closeModal();
  });

  // xss-safe
  function escapeHtml(str){
    return String(str).replace(/[&<>"']/g, s=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[s]));
  }
})();
