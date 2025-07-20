<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    exit('Usuário não autenticado');
}
?>
<form id="formNovaCategoria">
  <div class="mb-3">
    <label for="nome_categoria" class="form-label">Nome da Categoria</label>
    <input type="text" class="form-control" id="nome_categoria" name="nome_categoria" required>
  </div>
  <div id="msgCategoria" class="mb-2"></div>
  <div class="d-flex justify-content-end">
    <button type="submit" class="btn btn-primary">Salvar</button>
  </div>
</form>

<script>
document.getElementById('formNovaCategoria').addEventListener('submit', function(e) {
  e.preventDefault();

  const nome = document.getElementById('nome_categoria').value;
  const msg = document.getElementById('msgCategoria');

  fetch('salvar_categoria.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: 'nome_categoria=' + encodeURIComponent(nome)
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      msg.innerHTML = '<div class="alert alert-success">Categoria adicionada!</div>';
      
      const select = document.getElementById('categoria_lancamento');
      const option = document.createElement('option');
      option.value = data.id_categoria;
      option.text = data.nome_categoria;
      option.selected = true;
      select.appendChild(option);

      setTimeout(() => {
        const modal = bootstrap.Modal.getInstance(document.getElementById('modalCategoria'));
        modal.hide();
      }, 1000);
    } else {
      msg.innerHTML = '<div class="alert alert-danger">' + data.message + '</div>';
    }
  })
  .catch(() => {
    msg.innerHTML = '<div class="alert alert-danger">Erro na requisição.</div>';
  });
});
</script>
