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
    <div>
        <input type="file" @change="select">
        <button @click="upload">Upload</button>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/vue@2"></script>
<script>
    new Vue({
        el: '#app',
        data() {
            return {
                file: null
            };
        },
        methods: {
            handleFileChange(event) {
                this.file = event.target.files[0];
            },
            async upload() {
                const chunkSize = 1024 * 1024; // 1MB
                const totalChunks = Math.ceil(this.file.size / chunkSize);
                let start = 0;

                // Получаем CSRF-токен из мета-тега
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                for (let i = 0; i < totalChunks; i++) {
                    const chunk = this.file.slice(start, start + chunkSize);
                    const formData = new FormData();
                    formData.append('file', chunk);

                    try {
                        const response = await fetch('/upload-chunk', {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': csrfToken // Передаем CSRF-токен в заголовке запроса
                            }
                        });

                        const data = await response.json();
                        if (!data.success) {
                            console.error('Chunk upload failed');
                            return;
                        }
                        start += chunkSize;
                    } catch (error) {
                        console.error('Error uploading chunk:', error);
                        return;
                    }
                }

                // All chunks uploaded successfully
                const finalFormData = new FormData();
                finalFormData.append('file', this.file);

                try {
                    const finalResponse = await fetch('/upload', {
                        method: 'POST',
                        body: finalFormData,
                        headers: {
                            'X-CSRF-TOKEN': csrfToken // Передаем CSRF-токен в заголовке запроса
                        }
                    });

                    const finalData = await finalResponse.json();
                    if (finalData.success) {
                        console.log('File uploaded successfully');
                    } else {
                        console.error('Final upload failed');
                    }
                } catch (error) {
                    console.error('Error uploading final file:', error);
                }
            }
        }
    });
</script>
</body>
</html>

