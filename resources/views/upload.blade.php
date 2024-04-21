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
    <input type="file" @change="selectFile">
    <button @click="uploadFile">Upload</button>
    <div v-if="uploadProgress !== null">
        <progress :value="uploadProgress" max="100"></progress>
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
                file: null,
                uploadProgress: 0
            };
        },
        methods: {
            selectFile(event) {
                this.file = event.target.files[0];
            },
            async uploadFile() {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const chunkSize = 1024 * 1024; // Размер чанка в 1MB
                const totalChunks = Math.ceil(this.file.size / chunkSize);

                for (let chunkIndex = 0; chunkIndex < totalChunks; chunkIndex++) {
                    const offset = chunkIndex * chunkSize;
                    const chunk = this.file.slice(offset, offset + chunkSize);
                    const formData = new FormData();
                    formData.append('file', chunk);
                    formData.append('fileName', this.file.name);
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

                        if (response.ok) {
                            const data = await response.json();
                            this.uploadProgress = Math.round(((chunkIndex + 1) / totalChunks) * 100);
                            console.log('Chunk uploaded', data);
                        } else {
                            throw new Error('Failed to upload chunk');
                        }
                    } catch (error) {
                        console.error('Error uploading chunk:', error);
                        return;
                    }
                }

                console.log('File uploaded successfully');
            }
        }
    });
</script>
