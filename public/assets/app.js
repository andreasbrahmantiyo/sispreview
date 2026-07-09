const LABEL = {
  total: 'Total Item',
  fixuser: 'Fix dgn user',
  tiket: 'Sudah tiket',
  dev: 'Development',
  qc: 'QC / UAT',
  ready: 'Ready release',
  released: 'Released',
  overdue: 'Overdue',
  hold: 'Hold kontrak'
};

const STAGES = ['Fix', 'Tiket', 'Dev', 'QC', 'Ready', 'Rel'];

function tracker(c, compact) {
  let segs = '';
  const rank = Number(c.stage_rank);
  const active = c.stage === 'Released' ? -1 : rank;
  const done = [
    rank >= 1,
    Boolean(c.ticket_no),
    rank >= 2,
    rank >= 3,
    rank >= 4,
    c.stage === 'Released'
  ];
  for (let i = 0; i < 6; i++) {
    let cls = 'seg';
    if (done[i]) cls += ' done';
    if (i === active) cls += c.overdue ? ' act-over' : (Number(c.is_hold_contract) ? ' act-hold' : ' act');
    segs += `<div class="${cls}"></div>`;
  }
  if (compact) return `<div class="trk" style="min-width:96px"><div class="segs">${segs}</div></div>`;
  const labels = STAGES.map((label, i) => `<span class="${i === active ? 'cur' : ''}">${label}</span>`).join('');
  return `<div class="trk"><div class="segs">${segs}</div><div class="lbls">${labels}</div></div>`;
}

function penPill(c) {
  if (c.blocker === '-' || c.blocker_type === 'none') return '<span class="pill pen pen-none">-</span>';
  const cls = c.blocker_type === 'external' ? 'pen-ex' : 'pen-in';
  return `<span class="pill pen ${cls}"><span class="pdot"></span>${escapeHtml(c.blocker)}</span>`;
}

function escapeHtml(value) {
  return String(value ?? '').replace(/[&<>"']/g, char => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
  }[char]));
}

function itemLinks(c) {
  const links = [];
  if (c.ticket_url) links.push(`<a href="${escapeHtml(c.ticket_url)}" target="_blank" rel="noopener noreferrer">Tiket</a>`);
  if (c.figma_url) links.push(`<a href="${escapeHtml(c.figma_url)}" target="_blank" rel="noopener noreferrer">Figma</a>`);
  return links.length ? `<div class="dlinks">[${links.join('] [')}]</div>` : '';
}

function drill(key) {
  const list = window.CR_DATA[key] || [];
  document.getElementById('shTitle').textContent = key.startsWith('blocker:') ? `Penahan ${key.slice(8)}` : (LABEL[key] || key);
  document.getElementById('shCount').textContent = `${list.length} CR`;
  document.getElementById('shBody').innerHTML = list.map(c => {
    const late = c.overdue ? ' late' : '';
    const day = c.overdue ? ` - ${escapeHtml(c.day_text)}` : '';
    const baseLabel = c.sub_area ? `${c.form_name} - ${c.sub_area}` : c.form_name;
    const formLabel = c.client_code ? `${c.client_code} - ${baseLabel}` : baseLabel;
    return `<div class="di"><div class="df">${escapeHtml(formLabel)}</div><div class="dc">${escapeHtml(c.cr_title)}</div>${itemLinks(c)}<div class="drow">${tracker(c, true)}${penPill(c)}<span class="dtgt${late}">Target ${escapeHtml(c.target_text)}${day}</span></div></div>`;
  }).join('');
  document.getElementById('overlay').classList.add('show');
}

function closeSheet() {
  document.getElementById('overlay').classList.remove('show');
}

function setMode(mode) {
  const exec = mode === 'exec';
  document.getElementById('viewExec').classList.toggle('hidden', !exec);
  document.getElementById('viewDetail').classList.toggle('hidden', exec);
  document.getElementById('btnExec').classList.toggle('on', exec);
  document.getElementById('btnDetail').classList.toggle('on', !exec);
  closeSheet();
  if (!exec) history.replaceState(null, '', '#detail');
  else if (location.hash) history.replaceState(null, '', location.pathname + location.search);
}

document.addEventListener('keydown', e => {
  if (e.key === 'Escape') closeSheet();
});

if (location.hash === '#detail') {
  setMode('detail');
}
