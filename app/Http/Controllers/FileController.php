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
        $chunkIndex = $request->input('chunkIndex');
        $totalChunks = $request->input('totalChunks');

        $tempPath = storage_path("app/temp/{$fileName}");

        // Открываем файл в режиме добавления
        $fileStream = fopen($tempPath, 'ab');
        fwrite($fileStream, file_get_contents($file->getPathname()));
        fclose($fileStream);

        // Проверяем, последний ли это чанк
        if ((int) $chunkIndex === (int) $totalChunks - 1) {
            // Перемещаем файл из временной папки в окончательное место хранения
            $finalPath = storage_path("app/uploads/{$fileName}");
            rename($tempPath, $finalPath);
            return response()->json(['status' => 'complete', 'path' => $finalPath]);
        }

        return response()->json(['status' => 'success', 'chunkIndex' => $chunkIndex]);
    }



    public function upload(Request $request)
    {
        // create the file receiver
        $receiver = new FileReceiver("file", $request, HandlerFactory::classFromRequest($request));

        // check if the upload is success, throw exception or return response you need
        if ($receiver->isUploaded() === false) {
            throw new UploadMissingFileException();
        }

        // receive the file
        $save = $receiver->receive();

        // check if the upload has finished (in chunk mode it will send smaller files)
        if ($save->isFinished()) {
            // Log the assembled file before saving
            Log::info('Assembled file:', ['path' => $save->getFile()->getPathname()]);

            // save the file and return any response you need
            $this->saveFile($save);
            return response()->json('success');
        }

        // we are in chunk mode, lets send the current progress
        /** @var AbstractHandler $handler */
        $handler = $save->handler();

        return response()->json([
            "done" => $handler->getPercentageDone(),
            'status' => true
        ]);
    }


    protected function saveFile($save)
    {
        $originalName = $save->getFile()->getClientOriginalName() ?? 'default_name.png';

        $tempPath = storage_path("app/temp/{$originalName}");

        // Ensure directory exists
        if (!file_exists(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0777, true);
        }

        // Open file in append binary mode
        $file = fopen($tempPath, 'ab');
        if (!$file) {
            Log::error("Unable to open file: {$tempPath}");
            return;
        }

        // Get chunk data
        $chunkData = file_get_contents($save->getFile()->getPathname());
        if ($chunkData === false) {
            Log::error("Failed to read chunk data");
            fclose($file);
            return;
        }

        // Write the chunk to the file
        fwrite($file, $chunkData);
        fclose($file);

        Log::info("Chunk appended to {$tempPath}");

        // After all chunks are processed
        if ($save->isFinished()) {
            $finalPath = storage_path("app/uploads/{$originalName}");

            // Ensure final directory exists
            if (!file_exists(dirname($finalPath))) {
                mkdir(dirname($finalPath), 0777, true);
            }

            // Move the file to the final location
            if (!rename($tempPath, $finalPath)) {
                Log::error("Failed to move file from {$tempPath} to {$finalPath}");
            } else {
                Log::info("File moved to final location: {$finalPath}");
            }
        }

        return $tempPath;
    }
}
