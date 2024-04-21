<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Document</title>
</head>
<body>
<div id="app">
    <input type="file" multiple @change="selectFiles"> <!-- Исправлено для множественного выбора -->
    <button @click="uploadFiles">Upload</button>
    <div v-if="uploadProgress > 0">
        <progress :value="uploadProgress" max="100"></progress> <!-- Прогресс будет показан, если началась загрузка -->
    </div>
</div>
</body>
</html>

<script src="https://cdn.jsdelivr.net/npm/vue@2"></script>
<script>
    new Vue({
        el: "#app",
        data() {
            return {
                files: [], // Исправлено для хранения массива файлов
                uploadProgress: 0
            };
        },
        methods: {
            selectFiles(event) {
                this.files = event.target.files; // Сохраняем массив выбранных файлов
            },
            async uploadFiles() {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const chunkSize = 1024 * 1024; // Размер чанка в 1MB

                for (let fileIndex = 0; fileIndex < this.files.length; fileIndex++) {
                    const file = this.files[fileIndex];
                    const totalChunks = Math.ceil(file.size / chunkSize);

                    for (let chunkIndex = 0; chunkIndex < totalChunks; chunkIndex++) {
                        const offset = chunkIndex * chunkSize;
                        const chunk = file.slice(offset, offset + chunkSize);
                        const formData = new FormData();
                        formData.append('file', chunk);
                        formData.append('fileName', file.name);
                        formData.append('fileIndex', fileIndex);
                        formData.append('chunkIndex', chunkIndex);
                        formData.append('totalChunks', totalChunks);

                        try {
                            const response = await fetch('/upload-chunk', {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': csrfToken
                                },
                                body: formData
                            });

                            if (!response.ok) {
                                throw new Error('Failed to upload chunk');
                            }

                            const data = await response.json();
                            console.log('Chunk uploaded', data);
                        } catch (error) {
                            console.error('Error uploading chunk:', error);
                            return;
                        }
                    }

                    console.log('File uploaded successfully', file.name);
                }
                this.uploadProgress = 100; // Обновление прогресса после загрузки всех файлов
            }
        }
    });
</script>
