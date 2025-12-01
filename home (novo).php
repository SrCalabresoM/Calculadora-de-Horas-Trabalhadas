<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calculadora</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <style>
    /* Botão Sair */
    #btnSair {
        color: red;
        font-weight: bold;
    }

    /* Campo Nome do Funcionário */
    #nomeFuncionario {
        font-weight: bold;
        border: 1px solid #777; /* borda cinza */
    }

     /* Estilo do texto "Entrada:" */
    .label-entrada {
        color: blue;
        font-weight: bold;
    }

    /* Estilo do texto "Saída:" */
    .label-saida {
        color: #ff6666; /* vermelho claro */
        font-weight: bold;
    }

     /* Botão Calcular */
    #butt {
        font-weight: bold;
        border: 2px solid #777; /* borda cinza */
    }

    /* Botão "+" */
    #add {
        color: #66b3ff; /* azul claro */
        font-weight: bold;
    }

    /* Tabela de Registros */
    #tableRegistros {
        width: 22rem;
        border-collapse: collapse; /* tira espaço entre bordas */
        border: 1px solid #ccc; /* borda externa */
    }

    #tableRegistros th,
    #tableRegistros td {
        border: 1px solid #ccc; /* bordas internas */
        padding: 8px;
        text-align: left;
    }

    #tableRegistros thead {
        background-color: #f2f2f2; /* fundo leve para cabeçalho */
        font-weight: bold;
    }

    #logoTopo {
    position: absolute;
    top: 15px;
    right: 20px;
    width: 90px;      /* ajuste o tamanho */
    height: auto;
    z-index: 999;
}

</style>

</head>
<body>
    <img id="logoTopo" src="logo.jpg" alt="Logo Jalin Contei">
    <div class="m-4">
<h1>Bem-vindo, <?php echo $_SESSION['usuario']; ?>!</h1>
    <button id="btnSair" onclick="window.location.href = 'logout.php'">Sair</button> 

<div class="mt-3">
  <label for="nomeFuncionario" class="form-label">Nome do funcionário</label>
  <input type="text"style='width: 22rem;' id="nomeFuncionario" class="form-control mb-2" placeholder="Nome do funcionário">
</div>
    <div class=" card shadow "style='width: 24rem;'>
        <div class="container m-4" >
            <div id="container">
            <span class="label-entrada">Entrada: </span><input type="time" class="entrada" style="margin:5px;"> <span class="label-saida">Saída: </span><input type="time" class="saida" style="margin:5px;">
</div>
    <button id="butt" class="btn">Calcular</button> <button id="add" class="btn"> +</button>
</div>
</div>
<br>
<h1 id="result"></h1>
<br>
<br>
<br>
<h3 class="mt-4">Registros anteriores</h3>

<div class="filters mb-2">
    <input id="searchFuncionario" placeholder="Pesquisar funcionário..." style="width:22rem;">
    <button id="btnFilterReg">Filtrar</button>
</div>

<div id="resultsRegistros">
    <table id="tableRegistros" style="width:22rem; border-collapse:collapse; border:1px solid #ddd;">
        <thead>
            <tr>
                <th>Funcionário</th>
                <th>Horas</th>
                <th>Data</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
    <div id="paginationReg" style="margin-top:12px;"></div>
</div>
</div>
<script>
    function calcularDiferencaMinutos(horaInicio, horaFim) {
    let diferenca = 0;
    for (let i = 0; i < horaInicio.length; i += 1) {
        const [h1, m1] = horaInicio[i].split(':').map(Number);
        const [h2, m2] = horaFim[i].split(':').map(Number);

        const minutos1 = h1 * 60 + m1;
        const minutos2 = h2 * 60 + m2;

        diferenca += Math.abs(minutos2 - minutos1);
    }
    return diferenca;
}

    function formatarHorario(totalMinutos) {

            const novaHora = Math.floor(totalMinutos / 60)
            const novoMinuto = totalMinutos % 60;

            const horaFormatada = String(novaHora).padStart(2, '0');
            const minutoFormatado = String(novoMinuto).padStart(2, '0');

            return `${horaFormatada}:${minutoFormatado}`;
    }

    document.getElementById("add").addEventListener('click', () => {
  const inputsContainer = document.getElementById("container");

  const wrapper = document.createElement('div');
  wrapper.style.marginBottom = '6px';
  wrapper.classList.add('linha-horario');

  const spanIn = document.createElement('span');
  spanIn.textContent = 'Entrada: ';
  spanIn.classList.add('label-entrada');

  const entrada = document.createElement('input');
  entrada.type = 'time';
  entrada.style.margin = '5px';
  entrada.classList.add('entrada');

  const spanOut = document.createElement('span');
  spanOut.textContent = 'Saída: ';
  spanOut.classList.add('label-saida');

  const saida = document.createElement('input');
  saida.type = 'time';
  saida.style.margin = '5px';
  saida.classList.add('saida');

  const btnRemover = document.createElement('button');
  btnRemover.type = 'button';
  btnRemover.className = 'btn-remover-linha btn';
  btnRemover.textContent = '🗑️';
  btnRemover.style.marginLeft = '6px';
  btnRemover.addEventListener('click', () => { wrapper.remove(); });


  wrapper.appendChild(spanIn);
  wrapper.appendChild(entrada);
  wrapper.appendChild(spanOut);
  wrapper.appendChild(saida);
  wrapper.appendChild(btnRemover);

  inputsContainer.appendChild(wrapper);
});


    document.getElementById("butt").addEventListener("click", async function(){

        const result = document.getElementById("result");
        const inputsInicio = Array.from(document.querySelectorAll('.entrada')).map(input => input.value);
        const inputsFim = Array.from(document.querySelectorAll('.saida')).map(input => input.value);

  const nome = document.getElementById('nomeFuncionario').value.trim();
  if (!nome) return alert('Digite o nome do funcionário!');

  if (inputsInicio.length === 0 || inputsFim.length === 0) return alert('Adicione pelo menos uma entrada/saída.');
  if (inputsInicio.includes('') || inputsFim.includes('')) return alert('Preencha todos os horários!');

  const totalMin = calcularDiferencaMinutos(inputsInicio, inputsFim);
  const totalFmt = formatarHorario(totalMin);

  try {
    const res = await fetch('salvar_registro.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({ nome: nome, horas: totalFmt })
    });

    const text = await res.text();
    let json;
    try { json = JSON.parse(text); } catch(e) { throw new Error('Resposta inválida do servidor: ' + text); }

    if (!res.ok) throw new Error(json.erro ?? ('Status: ' + res.status));
    if (json.sucesso) {
      result.innerText = `Horas salvas: ${totalFmt}`;
      fetchRegistros(1);
    } else {
      throw new Error(json.erro || 'Erro desconhecido');
    }
  } catch (err) {
    console.error(err);

    result.innerText = `Falha ao salvar: ${err.message}`;
  }
});

//................................................................

const perPageReg = 8;
let currentPageReg = 1;

async function fetchRegistros(page=1){
    currentPageReg = page;
    const search = document.getElementById('searchFuncionario').value.trim();
    const params = new URLSearchParams({
        search,
        page,
        perPage: perPageReg
    });

    try {
        const res = await fetch('fetch_registros.php?' + params.toString());
        if (!res.ok) throw new Error('Erro na requisição');
        const data = await res.json();
        renderTableRegistros(data.registros);
        renderPaginationRegistros(data.total, data.page, data.perPage);
    } catch(err){
        console.error(err);
        alert('Erro ao buscar registros.');
    }
}

function renderTableRegistros(registros){
    const tbody = document.querySelector('#tableRegistros tbody');
    tbody.innerHTML = '';
    for(const r of registros){
        const tr = document.createElement('tr');

        const tdNome = document.createElement('td'); tdNome.textContent = r.funcionario_nome;
        const tdHoras = document.createElement('td'); tdHoras.textContent = r.horas_trabalhadas;
        const tdData = document.createElement('td'); tdData.textContent = r.data_registro;

        const tdAction = document.createElement('td');
        const btnTrash = document.createElement('button');
        btnTrash.type = 'button';
        btnTrash.textContent = '🗑️';
        btnTrash.style.cursor = 'pointer';
        btnTrash.addEventListener('click', async () => {
            if (!confirm('Excluir este registro?')) return; 
            try {
                const res = await fetch('excluir_registro.php', {
                    method: 'POST',
                    headers: {'Content-Type':'application/json'},
                    body: JSON.stringify({ id: r.id })
                });
                const txt = await res.text();
                let j;
                try { j = JSON.parse(txt); } catch(e) { throw new Error('Resposta inválida: ' + txt); }
                if (!res.ok) throw new Error(j.erro || 'Falha');
                await fetchRegistros(currentPageReg); 
            } catch (err) {
                console.error(err);
                alert('Erro ao excluir: ' + err.message);
            }
        });

        tdAction.appendChild(btnTrash);

        tr.appendChild(tdNome);
        tr.appendChild(tdHoras);
        tr.appendChild(tdData);
        tr.appendChild(tdAction);

        tbody.appendChild(tr);
    }
}

function renderPaginationRegistros(total, page, perPage){
    const container = document.getElementById('paginationReg');
    container.innerHTML = '';
    const pages = Math.ceil(total / perPage) || 1;
    for(let i=1;i<=pages;i++){
        const btn = document.createElement('button');
        btn.textContent = i;
        if(i === page) btn.disabled = true;
        btn.addEventListener('click',()=>fetchRegistros(i));
        container.appendChild(btn);
    }
}


function escapeHtml(text){
    if(text === null || text === undefined) return '';
    return text.toString().replaceAll('&','&amp;')
                          .replaceAll('<','&lt;')
                          .replaceAll('>','&gt;')
                          .replaceAll('"','&quot;')
                          .replaceAll("'","&#039;");
}

document.getElementById('btnFilterReg').addEventListener('click',()=>fetchRegistros(1));

fetchRegistros(1);

</script>
</body>
</html>