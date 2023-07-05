<!-- Campo de entrada para la URL del archivo -->
<input type="text" id="selectedFileUrl" class="form-control">

<!-- Botón para abrir el modal -->
<button type="button" class="btn btn-primary" id="openFileManagerButton">
  Select
</button>

<!-- Modal -->
<div class="modal fade" id="fileManagerModal" tabindex="-1" role="dialog" aria-labelledby="fileManagerModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="fileManagerModalLabel">File Manager</h5>
        <button type="button" class="btn-close" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Aquí va el iframe que contendrá el File Manager -->
        <iframe id="fileManagerIframe" src="/fmanager-modal" width="100%" height="600px"></iframe>
      </div>
    </div>
  </div>
</div>

<script>
$(document).ready(function() {
  $('#openFileManagerButton').click(function() {
    // Abre el modal
    $('#fileManagerModal').modal('show');
  });

  $('#fileManagerModal .btn-close').click(function() {
    // Cierra el modal al hacer clic en la "x"
    $('#fileManagerModal').modal('hide');
  });

  window.addEventListener('message', function(event) {
    // Asegúrate de que el mensaje es el que esperas
    if (event.data.fileUrl) {
      console.log('URL del archivo seleccionado:', event.data.fileUrl);
      // Coloca la URL del archivo en el campo de entrada
      document.getElementById('selectedFileUrl').value = event.data.fileUrl;
      // Cierra el modal
      $('#fileManagerModal').modal('hide');
    }
  });
});
</script>
