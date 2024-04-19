<template>
  <div>
    <input type="file" @change="selectFile">
    <button @click="uploadFile">Upload</button>
    <div v-if="uploadProgress !== null">
      Upload progress: {{ uploadProgress }}%
    </div>
  </div>
</template>

<script>
export default {
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
      const formData = new FormData();
      formData.append('file', this.file);

      try {
        const response = await fetch('/upload', {
          method: 'POST',
          body: formData,
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        });

        const data = await response.json();
        if (data.success) {
          console.log('File uploaded successfully');
        } else {
          console.error('Upload failed');
        }
      } catch (error) {
        console.error('Error uploading file:', error);
      }
    }
  }
};
</script>
