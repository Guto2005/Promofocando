let layoutData = [];

const COLS = 6;
const ROWS = 4;

function isColliding(test, index) {
  return layoutData.some((b, i) => {
    if (i === index) return false;

    return !(
      test.x + test.w <= b.layout.x ||
      test.x >= b.layout.x + b.layout.w ||
      test.y + test.h <= b.layout.y ||
      test.y >= b.layout.y + b.layout.h
    );
  });
}

function render() {
  const root = document.getElementById('layout');
  root.innerHTML = '';

  layoutData.forEach((item, i) => {
    const el = document.createElement('div');
    el.className = 'bloco';

    el.style.gridColumn = `${item.layout.x + 1} / span ${item.layout.w}`;
    el.style.gridRow = `${item.layout.y + 1} / span ${item.layout.h}`;

    el.innerHTML = `
      <strong>${item.tipo}</strong>
      <input value="${item.content.title || ''}" oninput="layoutData[${i}].content.title=this.value">
      <textarea oninput="layoutData[${i}].content.text=this.value}">${item.content.text || ''}</textarea>
      <div class="bloco-footer">
        <button onclick="move(${i}, -1, 0)">⬅</button>
        <button onclick="move(${i}, 1, 0)">➡</button>
        <button onclick="move(${i}, 0, -1)">⬆</button>
        <button onclick="move(${i}, 0, 1)">⬇</button>
        <button onclick="resize(${i}, 1, 0)">➕ Col</button>
        <button onclick="resize(${i}, -1, 0)">➖ Col</button>
        <button onclick="resize(${i}, 0, 1)">➕ Row</button>
        <button onclick="resize(${i}, 0, -1)">➖ Row</button>
        <button onclick="removeBloco(${i})">🗑</button>
      </div>
    `;

    root.appendChild(el);
  });
}

function addBloco(tipo) {
  const bloco = {
    id: crypto.randomUUID(),
    tipo,
    layout: { x: 0, y: 0, w: 1, h: 1 },
    content: { title: 'Novo ' + tipo, text: '' }
  };

  if (!isColliding(bloco.layout, -1)) {
    layoutData.push(bloco);
  }
  render();
}

function move(i, dx, dy) {
  const b = layoutData[i];
  const test = {
    x: Math.max(0, Math.min(COLS - b.layout.w, b.layout.x + dx)),
    y: Math.max(0, Math.min(ROWS - b.layout.h, b.layout.y + dy)),
    w: b.layout.w,
    h: b.layout.h
  };

  if (!isColliding(test, i)) {
    b.layout = test;
  }
  render();
}

function resize(i, dw, dh) {
  const b = layoutData[i];
  const test = {
    x: b.layout.x,
    y: b.layout.y,
    w: Math.max(1, Math.min(COLS - b.layout.x, b.layout.w + dw)),
    h: Math.max(1, Math.min(ROWS - b.layout.y, b.layout.h + dh))
  };

  if (!isColliding(test, i)) {
    b.layout = test;
  }
  render();
}

function removeBloco(i) {
  layoutData.splice(i, 1);
  render();
}

function salvar() {
  fetch('salvar.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify(layoutData)
  }).then(() => alert('Layout salvo!'));
}

fetch('carregar.php')
  .then(r => r.json())
  .then(data => {
    layoutData = data || [];
    render();
  });
