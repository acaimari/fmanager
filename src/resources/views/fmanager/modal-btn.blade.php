
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FManager</title>
    
    <meta name="csrf-token" content="{{ csrf_token() }}">


    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.2.1/themes/default/style.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.2.1/jstree.min.js"></script>


    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <!-- Line Icons -->
    <link rel="stylesheet" href="https://cdn.lineicons.com/4.0/lineicons.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.2.96/css/materialdesignicons.min.css" />
    
 

    <!-- Reduccion de imagenes y barra progreso -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/blueimp-load-image/2.23.0/load-image.all.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    

    
<style>

.blue-icon {
    font-size: 24px;
    color: black;
}

</style>


</head>
<body>

<div class="container">
    <div class="modal-header">
        File Manager <div id="currentPathContainer">Home directory > <span id="currentPath"></span></div>
        <div class="progress">
            <div class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
    </div>
</div>

<!-------------------  Content ----------------->

<div class="container">
        <div class="row">
            <div class="col-md-3">

                    <div id="directoryContainer"> <!-- Start directoryContainer -->
                    <div id="directories" class="directories">
                    <ul>
                        @foreach ($directories as $directory)
                            @include('fmanager::fmanager.directoryItem', ['directory' => $directory])
                        @endforeach
                    </ul>
                    </div>
        
            </div>
    <!-- End directoryContainer -->

    </div> <!-- End MD3 -->

        <div class="col-md-9">
            
            <table id="fileManagerContainer" class="fileManager">
                                
                        <thead>
                            
                            <tr>
                                <th><input type="checkbox" id="selectAllCheckbox" class="item-checkbox"></th>
                                <th id="nameHeader">Name</th>
                                <th>Size</th>
                            </tr>
                            
                        </thead>
                    <tbody>     
                        @if (count($files) > 0)
                            @foreach ($files as $file)
                                <tr class="file nav-item" data-path="{{ $file['path'] }}">
                                    
                                    <td><input type="checkbox" class="item-checkbox"></td>
                                    <td>
                                        <i class="fas fa-file"></i>
                                        {{ $file['name'] }}
                                    </td>
                                    <td>{{ $file['size'] }}</td>
                                </tr>
                                
                            @endforeach
                        @else
                            <tr>
                                <td colspan="3">No hay archivos para mostrar.</td>
                            </tr>
                        @endif
                    </tbody>
            </table>

                    
            </div>    
        </div>
            <p></p>
                       
</div>




<!------- Footer in modal ----------->
<div class="container">
    <div class="modal-footer sticky-footer" style="background-color: #f8f9fa;">

            <!------------ Buttons file manager --------->
            <button id="deleteSelected"><i class="mdi mdi-trash-can-outline blue-icon"></i></button>

            <!-- Btn add Dir -->
            <button id="createDirButton">
            <i class="mdi mdi-folder-plus-outline blue-icon"></i>
            </button>

            <div class="modal fade" id="createDirModal" tabindex="-1" aria-labelledby="createDirModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createDirModalLabel">Crear carpeta</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="overflow-y: auto;">
                    <form id="createDirForm">
                    <div class="form-group">
                        <label for="dirName">Nombre de carpeta:</label>
                        <input type="text" class="form-control" id="dirName" name="dirName" required>
                    </div>
                    <input type="submit" class="btn btn-primary" value="Crear">
                    </form>
                </div>
                </div>
            </div>
            </div>

            <!-- Form Upload file -->
            <form id="uploadFileForm" action="{{ route('upload.files.store') }}" method="post" style="display: none;">
                @csrf
                <input id="fileInput" type="file" name="file[]" multiple>
                <input type="hidden" id="subdir" name="subdir" value="">
            </form>

            <!-- Btn Upload file -->
            <button id="uploadFileButton"><i class="mdi mdi-upload-outline blue-icon"></i></button>
            <!-- End form and btn Upload file -->

            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
    </div> <!------- End Footer in modal ----------->
</div>
   
               
<!-------------------- JS --------------------------------->

<!--- Arbol de directorios directoryContainer   ----------->


<script>

    
$('#directories').jstree({
    'core': {
        'data': function(node, callback) {
            var path = node.id === '#' ? '/' : node.li_attr['data-path'];
            $.post('{{ route('fmanager.navigate') }}', {
                _token: '{{ csrf_token() }}',
                path: path
            }, function(data) {
                var directories = data.directories.map(function(directory) {
                    return {
                        'text': directory.path.split('/').pop(), // Obtén solo el nombre del directorio, no toda la ruta.
                        'li_attr': {
                            'data-path': directory.path
                        },
                        'children': directory.subdirectories.length > 0 // Si tiene subdirectorios, jstree debe cargarlos de forma asíncrona.
                    };
                });
                callback(directories);
            });
        },
        'check_callback' : true,
        'expand_selected_onload': true,
        'themes': {
            'dots': false,
            'icons': true
        }
    },
    'plugins': ['state'],
    'state': {
        'key': 'directoryTreeState',
        'filter': function(k,v) {
            return v;
        },
        'events': 'open_node.jstree'
    }
});


$('#directories').on('select_node.jstree', function (e, data) {
    var path = data.node.li_attr['data-path'];

    // Aquí es donde navegamos al directorio...
    $.post('{{ route('fmanager.navigate') }}', {
        _token: '{{ csrf_token() }}',
        path: path
    }, function(data) {
        updateFileManagerContainer(data, path === '/');
        localStorage.setItem('lastPath', path);
    });
});

$('#directories').on('select_node.jstree', function(e, data) {
    var path = data.node.li_attr['data-path'];
    localStorage.setItem('selectedNodePath', path);
});


var treeBuilt = false; // Bandera para verificar si el árbol ya se ha construido una vez

$('#directories').jstree({
    // Configuración del árbol
});

$('#directories').on('select_node.jstree', function (e, data) {
    // Lógica para navegar al directorio seleccionado
});

$('#directories').on('select_node.jstree', function(e, data) {
    // Lógica para guardar el path del nodo seleccionado
});

$(document).ready(function() {
    var selectedNodePath = localStorage.getItem('selectedNodePath');
    if (selectedNodePath) {
        $.post('{{ route('fmanager.navigate') }}', {
            _token: '{{ csrf_token() }}',
            path: selectedNodePath
        }, function(data) {
            if (!treeBuilt) {
                // Construir el árbol solo si aún no se ha construido
                $('#directories').jstree('select_node', '[data-path="' + selectedNodePath + '"]');
                treeBuilt = true;
            }
            // Actualizar el árbol después de seleccionar el nodo
            $('#directories').jstree(true).refresh();
        });
    }
});
</script>


<!--------------- File Manager carpetas y archivos JS <table id="fileManagerContainer" class="fileManager">  -------------------------->
<script>
$('#fileManagerModal').on('hidden.bs.modal', function (e) {
    $('#selectAllCheckbox').prop('checked', false);
});

// Cuando se navega a un directorio, guarda la ruta en localStorage
$(document).on('click', '.directory:not(.parentDirectory)', function() {
    var directory = $(this);
    var path = directory.data('path');
    navigateToDirectory(path);
});

$(document).on('click', '.parentDirectory', function() {
    var currentPath = $('#fileManagerContainer').data('path');
    if (currentPath === '/') {
        return;
    }
    var parentPath = currentPath.substring(0, currentPath.lastIndexOf('/'));
    if (parentPath === '') {
        parentPath = '/';
    }
    navigateToDirectory(parentPath);
});

$(document).on('click', '.item-checkbox', function(event) {
    event.stopPropagation();
});

$(document).on('dblclick', '.file', function() {
    var filePath = $(this).data('path');
    var fileDirectory = filePath.substring(0, filePath.lastIndexOf('/'));
    localStorage.setItem('lastPath', fileDirectory);
    window.parent.postMessage({ fileUrl: filePath }, '*');
    $('#fileManagerModal').modal('hide');
});

function getLastPath() {
    var lastPath = localStorage.getItem('lastPath');
    if (!lastPath || lastPath === '') {
        lastPath = '/';
    }
    return lastPath;
}

function navigateToDirectory(path) {
    if (!path) {
        path = getLastPath();
    }
    $.post('{{ route('fmanager.navigate') }}', {
        _token: '{{ csrf_token() }}',
        path: path
    }, function(data) {
        var isRoot = path === '/';
        updateFileManagerContainer(data, isRoot);
        localStorage.setItem('lastPath', path);
    });
}

function updateFileManagerContainer(data, isRoot) {
    $('#currentPath').text(data.currentPath);
    var itemsHtml = '';

    if (!isRoot) {
        itemsHtml += '<tr><td></td><td class="directory parentDirectory" data-path=".."><i class="fas fa-arrow-left"></i> ..</td><td></td></tr>';
    }

    $.each(data.directories, function(i, directory) {
        var directoryName = directory.path.split('/').pop();
        itemsHtml += '<tr class="directory" data-path="' + directory.path + '"><td><input type="checkbox" class="item-checkbox"></td><td><i class="fas fa-folder"></i> ' + directoryName + '</td><td></td></tr>';
    });

    $.each(data.files, function(i, file) {
        itemsHtml += '<tr class="file" data-path="' + file.path + '"><td><input type="checkbox" class="item-checkbox"></td><td><i class="fas fa-file"></i> ' + file.name + '</td><td class="file-size">' + file.size + '</td></tr>';
    });

    $('#fileManagerContainer tbody').html(itemsHtml);
    $('#fileManagerContainer').data('path', data.currentPath);
}

$(document).on('change', '#selectAllCheckbox', function() {
    $('#fileManagerContainer .item-checkbox').prop('checked', this.checked);
});

var sortAsc = true;

$('#nameHeader').on('click', function() {
    var rows = $('#fileManagerContainer tbody tr.file:not(.nav-item)').get();

    rows.sort(function(a, b) {
        var nameA = $(a).find('td:nth-child(2)').text();
        var nameB = $(b).find('td:nth-child(2)').text();

        if (sortAsc) {
            if(nameA < nameB) {
                return -1;
            }
            if(nameA > nameB) {
                return 1;
            }
        } else {
            if(nameA > nameB) {
                return -1;
            }
            if(nameA < nameB) {
                return 1;
            }
        }
        return 0;
    });

    $.each(rows, function(index, row) {
        $('#fileManagerContainer tbody').append(row);
    });

    sortAsc = !sortAsc; // Cambia la dirección de ordenación para la próxima vez.
});
</script>


<!----  Botones File-Manager JS ----------->

<!-- Boton creacion de carpetas JS-->

<script>
$('#createDirButton').on('click', function() {
  // Cuando se hace clic en el botón, mostramos el modal
  $('#createDirModal').modal('show');
});

$('#createDirForm').on('submit', function(event) {
  event.preventDefault();

  var directoryName = $('#dirName').val();
  var currentPath = $('#fileManagerContainer').data('path');

  // Enviamos una solicitud para crear el directorio
  $.post('{{ route('fmanager.createdir') }}', {
    _token: '{{ csrf_token() }}',
    path: currentPath,
    name: directoryName
  }, function(data) {
    if (data.success) {
      // Actualizamos el administrador de archivos luego de que se crea el directorio
      navigateToDirectory(currentPath);
      $('#createDirModal').modal('hide');
      $('#dirName').val(''); // Limpiamos el campo del nombre del directorio
    } else {
      alert('Hubo un error al crear el directorio.');
    }
  });
});
</script>

<!-- Boton de borrado -->
<script>
$('#deleteSelected').on('click', function() {
    var selectedFiles = [];
    $('#fileManagerContainer .item-checkbox:checked').each(function() {
        var filePath = $(this).closest('tr').data('path');
        selectedFiles.push(filePath);
    });

    console.log("Rutas seleccionadas para eliminación:", selectedFiles); // Aquí está el console log para rastrear las rutas seleccionadas

    // Si no hay archivos seleccionados, salimos de la función.
    if (selectedFiles.length === 0) {
        alert("No hay archivos seleccionados para eliminar");
        return;
    }

    // Confirmación de eliminación
    var confirmDelete = confirm("¿Está seguro de que desea eliminar los archivos seleccionados?");
    if (!confirmDelete) {
        return;
    }

    // Aquí es donde enviamos la solicitud HTTP para eliminar los elementos.
    $.post({
        url: '{{ route('fmanager.delete') }}',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            paths: selectedFiles // Enviamos el array de rutas.
        },
        success: function(response) {
            if (response.success) {
                // Aquí puedes actualizar el árbol de directorios y el contenido del directorio actual
                // después de que los archivos se hayan eliminado con éxito.
                var currentDirectory = localStorage.getItem('lastPath') || '/';
                navigateToDirectory(currentDirectory);
            } else {
                console.error(response.message);
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error("Error de eliminación:", textStatus, errorThrown);
            console.error("Respuesta del servidor:", jqXHR.responseText); // Mostramos la respuesta del servidor
        }
    });
});
</script>


<!--- Boton de subida de archivos y fileimput JS --------->
<script>
// Asumiendo que tienes una variable "currentDirectory" que almacena el directorio actual.
$('#uploadFileButton').on('click', function() {
    var currentDirectory = localStorage.getItem('lastPath') || '/'; // Utiliza la ruta almacenada en localStorage.
    $('#subdir').val(currentDirectory); // Actualiza el valor de 'subdir' antes de iniciar la subida.
    $('#fileInput').click();
});
</script>

<script>

$('#fileInput').on('change', function() {
    var formData = new FormData($('#uploadFileForm')[0]);
    var totalFiles = this.files.length;
    var uploadedFiles = 0;
    var files = this.files;

    function uploadAndResizeImage(file) {
        if (isImageFile(file)) {
            loadImage(
                file,
                function(canvas) {
                    canvas.toBlob(
                        function(blob) {
                            var resizedFile = new File([blob], file.name, { type: blob.type });
                            formData.set('file[]', resizedFile);
                            uploadFile(formData);
                        },
                        'image/jpeg',
                        0.7
                    );
                },
                {
                    maxWidth: 800,
                    maxHeight: 800,
                    canvas: true
                }
            );
        } else {
            // Si no es un archivo de imagen, simplemente subirlo sin reducir su tamaño
            formData.set('file[]', file);
            uploadFile(formData);
        }
    }

    function isImageFile(file) {
        return file.type.startsWith('image/');
    }

    function uploadFile(formData) {
        $.ajax({
            url: '{{ route('upload.files.store') }}',
            method: 'post',
            data: formData,
            processData: false,
            contentType: false,
            xhr: function() {
                var xhr = new window.XMLHttpRequest();

                xhr.upload.addEventListener('progress', function(e) {
                    if (e.lengthComputable) {
                        var percent = Math.round((e.loaded / e.total) * 100);
                        $('.progress-bar').css('width', percent + '%').text(percent + '%');
                    }
                });

                return xhr;
            },
            success: function() {
                uploadedFiles++;

                var percent = Math.round((uploadedFiles / totalFiles) * 100);
                $('.progress-bar').css('width', percent + '%').text(percent + '%');

                if (uploadedFiles === totalFiles) {
                    var currentDirectory = localStorage.getItem('lastPath') || '/';
                    navigateToDirectory(currentDirectory);

                    $('.progress-bar').css('width', '0%').text('0%');
                } else {
                    uploadAndResizeImage(files[uploadedFiles]);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error(textStatus, errorThrown);
                console.log(formData);
            }
        });
    }

    uploadAndResizeImage(files[uploadedFiles]);
});


</script>

</body>
</html>






