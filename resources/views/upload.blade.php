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
                uploadProgress: null
            };
        },
        methods: {
            selectFile(event) {
                this.file = event.target.files[0];
            },
            async uploadFile() {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                const chunkSize = 1024 * 1024; // 1MB
                const fileSize = this.file.size;
                let offset = 0;

                while (offset < fileSize) {
                    const chunk = this.file.slice(offset, offset + chunkSize);
                    const formData = new FormData();
                    formData.append('file', chunk);
                    formData.append('offset', offset);
                    formData.append('totalSize', fileSize);

                    try {
                        const response = await fetch('/upload', {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': csrfToken
                            }
                        });

                        const data = await response.json();
                        if (!response.ok) {
                            console.error('Upload failed');
                            return;
                        }

                        offset += chunkSize;
                        this.uploadProgress = Math.min(Math.round((offset / fileSize) * 100), 100);
                    } catch (error) {
                        console.error('Error uploading file:', error);
                        return;
                    }
                }

                console.log('File uploaded successfully');
            }
        }
    });
</script>
