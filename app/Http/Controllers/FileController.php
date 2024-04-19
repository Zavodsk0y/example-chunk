<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Pion\Laravel\ChunkUpload\Handler\HandlerFactory;
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;

class FileController extends Controller
{
    public function uploadChunk(Request $request)
    {
        $receiver = new FileReceiver("file", $request, HandlerFactory::classFromRequest($request));

        // Проверяем, был ли чанк загружен
        if ($receiver->isUploaded()) {
            $save = $receiver->receive();
            // Если все чанки загружены, возвращаем успешный ответ
            if ($save->isFinished()) {
                return response()->json(['success' => true]);
            } else {
                // Если есть еще чанки, возвращаем успешный ответ с данными о чанке
                return response()->json(['success' => true, 'chunk_index' => $save->getChunkFilename()]);
            }
        }

        // Если не был загружен ни один чанк, возвращаем сообщение об ошибке
        return response()->json(['success' => false]);
    }

    public function upload(Request $request)
    {
        // Обработка загрузки файла после того, как все чанки были загружены
        $receiver = new FileReceiver("file", $request, HandlerFactory::classFromRequest($request));
        $save = $receiver->receive();
        if ($save->isFinished()) {
            // Сохраняем файл после того, как все чанки были загружены
            $path = $save->getFile();
            $path->store('uploads'); // Переменные для сохранения в базу данных
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false]);
    }
}
