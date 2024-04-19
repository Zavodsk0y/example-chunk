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
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content')

                const formData = new FormData();
                formData.append('file', this.file);

                try {
                    const response = await fetch('/upload', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        }
                    });

                    const data = await response.json();
                    if (response.ok) {
                        console.log('File uploaded successfully');
                    } else {
                        console.error('Upload failed');
                    }
                } catch (error) {
                    console.error('Error uploading file:', error);
                }
            }
        }
    });
</script>
