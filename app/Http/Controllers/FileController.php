<?php

namespace App\Http\Controllers;

class FileController extends
{

    // Проверяем, была ли успешно сохранена часть файла
    if ($save === false) {
        throw new UploadFailedException(); // Возможно, вы захотите создать своё исключение UploadFailedException
    }

}
