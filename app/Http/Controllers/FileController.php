<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Pion\Laravel\ChunkUpload\Handler\HandlerFactory;
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;

class FileController extends Controller
{
    public function upload(Request $request)
{
    $receiver = new FileReceiver("file", $request, HandlerFactory::classFromRequest($request));

    // Проверяем, был ли файл загружен
    if ($receiver->isUploaded() === false) {
        throw new UploadMissingFileException();
    }

    // Получаем сохраненный файл
    $save = $receiver->receive();

    // Проверяем, завершена ли загрузка
    if ($save->isFinished()) {
        // Сохраняем файл и возвращаем ответ
        return $this->saveFile($save->getFile());
    }

    // Отправляем текущий прогресс загрузки
    $handler = $save->handler();

    return response()->json([
        "done" => $handler->getPercentageDone(),
        'status' => true
    ]);
}
}
