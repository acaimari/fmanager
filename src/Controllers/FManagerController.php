<?php

namespace Caimari\FManager\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

use Caimari\FManager\Models\FManagerFile;



class FManagerController extends Controller
{


    public function index()
{
    // Obtiene una lista de los directorios en el directorio 'root'.
    $directories = $this->getDirectories();

    // Obtiene una lista de los archivos en el directorio 'root'.
    $files = array_map(function($filePath) {
        // Toma solo el nombre del archivo de la ruta.
        $fileName = basename($filePath);
        // Obtiene el tamaño del archivo.
        $fileSize = Storage::size($filePath);
        // Devuelve un array con los detalles del archivo.
        return [
            'path' => $filePath,
            'name' => $fileName,
            'size' => $this->formatBytes($fileSize)
        ];
    }, Storage::files('/'));

    // Ordena los archivos por nombre.
    usort($files, function ($a, $b) {
        return strcmp($a['name'], $b['name']);
    });

    // Retorna la vista del administrador de archivos con los directorios y los archivos.
    return view('fmanager::fmanager.index', ['directories' => $directories, 'files' => $files]);
}


public function indexModal(Request $request)
{   
    // Obtiene una lista de los directorios en el directorio 'root'.
    $directories = $this->getDirectories();

    // Obtiene una lista de los archivos en el directorio 'root'.
    $files = array_map(function($filePath) {
        // Toma solo el nombre del archivo de la ruta.
        $fileName = basename($filePath);
        // Obtiene el tamaño del archivo.
        $fileSize = Storage::size($filePath);
        // Devuelve un array con los detalles del archivo.
        return [
            'path' => $filePath,
            'name' => $fileName,
            'size' => $this->formatBytes($fileSize)
        ];
    }, Storage::files('/'));

    // Ordena los archivos por nombre.
    usort($files, function ($a, $b) {
        return strcmp($a['name'], $b['name']);
    });

    // Retorna la vista del administrador de archivos con los directorios y los archivos.
    return view('fmanager::fmanager.modal-btn', ['directories' => $directories, 'files' => $files]);
}

    

public function indexModalAdv(Request $request)
{   
    // Obtiene una lista de los directorios en el directorio 'root'.
    $directories = $this->getDirectories();

    // Obtiene una lista de los archivos en el directorio 'root'.
    $files = array_map(function($filePath) {
        // Toma solo el nombre del archivo de la ruta.
        $fileName = basename($filePath);
        // Obtiene el tamaño del archivo.
        $fileSize = Storage::size($filePath);
        // Devuelve un array con los detalles del archivo.
        return [
            'path' => $filePath,
            'name' => $fileName,
            'size' => $this->formatBytes($fileSize)
        ];
    }, Storage::files('/'));

    // Ordena los archivos por nombre.
    usort($files, function ($a, $b) {
        return strcmp($a['name'], $b['name']);
    });

    // Retorna la vista del administrador de archivos con los directorios y los archivos.
    return view('fmanager::fmanager.modal-adv', ['directories' => $directories, 'files' => $files]);
}


    
    
    private function formatBytes($bytes, $precision = 2) 
{ 
    $units = array('B', 'KB', 'MB', 'GB', 'TB'); 

    $bytes = max($bytes, 0); 
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
    $pow = min($pow, count($units) - 1); 

    $bytes /= pow(1024, $pow);

    return round($bytes, $precision) . ' ' . $units[$pow]; 
}


public function navigate(Request $request)
{
    $path = $request->get('path');

    // Si el path es "..", retrocedemos al directorio padre
    if ($path === '..') {
        $currentPath = $request->session()->get('currentPath');
        // Si ya estamos en el directorio raíz, permanecemos ahí
        if ($currentPath === '/') {
            $path = '/';
        } else {
            // Retrocedemos al directorio padre
            $path = dirname($currentPath);
        }
    }

    $request->session()->put('currentPath', $path);

    // $directories = Storage::directories($path);
    $directories = $this->getDirectories($path);

    $files = array_map(function($filePath) {
        // Toma solo el nombre del archivo de la ruta
        $fileName = basename($filePath);
        // Obtiene el tamaño del archivo
        $fileSize = Storage::size($filePath);
        // Convierte el tamaño del archivo
        $fileSizeFormatted = $this->formatBytes($fileSize);
        // Devuelve un array con los detalles del archivo
        return [
            'path' => $filePath,
            'name' => $fileName,
            'size' => $fileSizeFormatted
        ];
    }, Storage::files($path));

    // Devolvemos los directorios y archivos como una respuesta JSON
    return response()->json(['directories' => $directories, 'files' => $files, 'currentPath' => $path]);
}


public function getDirectories($path = '/') {
    $directories = Storage::directories($path);

    $results = [];

    foreach ($directories as $directory) {
        $subdirectories = $this->getDirectories($directory);
        $results[] = [
            'path' => $directory,
            'subdirectories' => $subdirectories
        ];
    }

    return $results;
}

////////////////////////////// BTNS ///////////////////////////////////

// Upload Files

public function uploadFileStore(Request $request)
{
    $validator = Validator::make($request->all(), [
        'file.*' => 'required|file|max:2048', // valida que cada archivo no sea mayor a 2MB
        'subdir' => 'sometimes|string', // valida que subdir es una cadena, pero no es requerida
    ]);

    if ($validator->fails()) {
        return response()->json($validator->errors(), 422);
    }

    if ($request->hasFile('file')) {
        $subdir = $request->get('subdir', '');
        $subdir = ltrim($subdir, '/'); // Elimina el primer slash si existe
        $directory = !empty($subdir) ? $subdir : 'uploads'; // Si subdir es vacío, se utiliza 'uploads' como carpeta por defecto

        foreach ($request->file('file') as $file) {
            // Almacena el archivo y obtiene el nombre del archivo guardado
            $storedFile = $file->store($directory);

            // Crea una nueva entrada en la base de datos
            $fmanagerFile = new FManagerFile();
            $fmanagerFile->name = $file->getClientOriginalName(); // Obtiene el nombre original del archivo
            $fmanagerFile->ext = $file->extension(); // Obtiene la extensión del archivo
            $fmanagerFile->size = $file->getSize(); // Obtiene el tamaño del archivo
            $fmanagerFile->user_id = Auth::id();
            $fmanagerFile->url = Storage::url($storedFile); // Obtiene la URL del archivo guardado
            $fmanagerFile->save();
        }

        return response()->json(['success' => 'Los archivos se han subido correctamente.'], 200);
    } else {
        return response()->json(['error' => 'No se ha subido ningún archivo.'], 400);
    }
}

public function createDirectory(Request $request)
{
    $directoryPath = $request->input('path');
    $directoryName = $request->input('name');
    
    // Aquí puedes agregar validación para los inputs si quieres

    // Creamos el directorio
    Storage::makeDirectory($directoryPath . '/' . $directoryName);

    return response()->json(['success' => true]);
}

public function destroyFileStore(Request $request)
{
    $paths = $request->get('paths');

    if (!is_array($paths)) {
        return response()->json([
            'success' => false,
            'message' => 'No se proporcionaron rutas para eliminar.'
        ], 400);
    }

    foreach ($paths as $path) {
        try {
            // Revisamos si el path es un directorio
            $directoryPath = dirname($path);
            $directories = Storage::directories($directoryPath);

            if (in_array($path, $directories)) {
                // Si es un directorio, lo borramos
                Storage::deleteDirectory($path);
            } else if (Storage::exists($path)) {
                // Si no es un directorio, asumimos que es un archivo y lo borramos
                Storage::delete($path);

                // Busca y elimina el registro correspondiente en la base de datos
                $url = Storage::url($path);
                $file = FManagerFile::where('url', $url)->first();

                if ($file) {
                    $file->delete();
                }
            }
        } catch (\Exception $e) {
            Log::error('Error al eliminar el archivo o directorio: ' . $e->getMessage());
        }
    }

    return response()->json([
        'success' => true,
        'message' => 'Los archivos/directorios seleccionados se han eliminado con éxito.'
    ]);
}






}
