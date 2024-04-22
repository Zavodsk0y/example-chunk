<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Pion\Laravel\ChunkUpload\Exceptions\UploadMissingFileException;
use Pion\Laravel\ChunkUpload\Handler\AbstractHandler;
use Pion\Laravel\ChunkUpload\Handler\HandlerFactory;
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;
use Pion\Laravel\ChunkUpload\Save\ChunkSave;
use Pion\Laravel\ChunkUpload\Save\SingleSave;

class FileController extends Controller
{
    public function uploadChunk(Request $request)
    {
        $file = $request->file('file');
        $fileName = $request->input('fileName');
        $fileIndex = $request->input('fileIndex');
        $chunkIndex = $request->input('chunkIndex');
        $totalChunks = $request->input('totalChunks');

        $tempPath = storage_path("app/temp/file{$fileIndex}/{$fileName}");

        // Создание директории, если необходимо
        if (!file_exists(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0777, true);
        }

        // Сохранение чанка в файл
        $fileStream = fopen($tempPath, 'ab');
        fwrite($fileStream, file_get_contents($file->getPathname()));
        fclose($fileStream);

        // Проверка завершения загрузки всех чанков
        if ((int) $chunkIndex === (int) $totalChunks - 1) {
            $finalPath = storage_path("app/uploads/{$fileName}");
            rename($tempPath, $finalPath);
            return response()->json(['status' => 'complete', 'path' => $finalPath]);
        }

        return response()->json(['status' => 'success', 'chunkIndex' => $chunkIndex]);
    }
}
