<template>
    <div class="image-upload">
        <label v-if="label" class="block text-sm font-medium text-slate-700 mb-2">
            {{ label }}
            <span v-if="required" class="text-red-500">*</span>
        </label>

        <div class="upload-area">
            <!-- Preview Area -->
            <div v-if="previewUrl || modelValue" class="preview-container">
                <img :src="previewUrl || modelValue" :alt="label || 'Preview'" class="preview-image" />
                <div class="preview-overlay">
                    <div class="preview-actions">
                        <button
                            type="button"
                            @click="triggerFileInput"
                            class="action-btn"
                            :disabled="uploading"
                            title="Change image"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                />
                            </svg>
                        </button>
                        <button
                            v-if="allowDelete"
                            type="button"
                            @click="handleDelete"
                            class="action-btn delete-btn"
                            :disabled="uploading"
                            title="Remove image"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
                <div v-if="uploading" class="upload-progress">
                    <div class="progress-spinner"></div>
                    <span class="text-xs text-white font-medium">Uploading...</span>
                </div>
            </div>

            <!-- Upload Button Area -->
            <div v-else class="upload-button-area" @click="triggerFileInput" @dragover.prevent @drop.prevent="handleDrop">
                <div class="upload-icon">
                    <svg class="w-12 h-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                        />
                    </svg>
                </div>
                <div class="upload-text">
                    <p class="text-sm font-medium text-slate-600">Click to upload or drag and drop</p>
                    <p class="text-xs text-slate-500 mt-1">{{ acceptText }}</p>
                </div>
            </div>

            <!-- Hidden File Input -->
            <input
                ref="fileInput"
                type="file"
                :accept="accept"
                class="hidden"
                @change="handleFileSelect"
                :disabled="uploading"
            />
        </div>

        <!-- Error Message -->
        <p v-if="error" class="mt-2 text-sm text-red-600">{{ error }}</p>

        <!-- Help Text -->
        <p v-if="helpText && !error" class="mt-2 text-xs text-slate-500">{{ helpText }}</p>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    modelValue: {
        type: String,
        default: null,
    },
    label: {
        type: String,
        default: '',
    },
    required: {
        type: Boolean,
        default: false,
    },
    accept: {
        type: String,
        default: 'image/jpeg,image/jpg,image/png,image/gif,image/svg+xml,image/webp',
    },
    acceptText: {
        type: String,
        default: 'PNG, JPG, GIF, SVG, WEBP up to 2MB',
    },
    helpText: {
        type: String,
        default: '',
    },
    allowDelete: {
        type: Boolean,
        default: true,
    },
    maxSize: {
        type: Number,
        default: 2048, // KB
    },
});

const emit = defineEmits(['update:modelValue', 'upload', 'delete', 'error']);

const fileInput = ref(null);
const previewUrl = ref(null);
const uploading = ref(false);
const error = ref(null);

const triggerFileInput = () => {
    if (!uploading.value) {
        fileInput.value?.click();
    }
};

const validateFile = (file) => {
    error.value = null;

    // Check file type
    const acceptedTypes = props.accept.split(',').map((type) => type.trim());
    if (!acceptedTypes.includes(file.type)) {
        error.value = 'Invalid file type. Please upload an image file.';
        return false;
    }

    // Check file size
    const fileSizeKB = file.size / 1024;
    if (fileSizeKB > props.maxSize) {
        error.value = `File size must not exceed ${props.maxSize / 1024}MB.`;
        return false;
    }

    return true;
};

const handleFileSelect = (event) => {
    const file = event.target.files?.[0];
    if (file) {
        processFile(file);
    }
};

const handleDrop = (event) => {
    const file = event.dataTransfer.files?.[0];
    if (file) {
        processFile(file);
    }
};

const processFile = (file) => {
    if (!validateFile(file)) {
        return;
    }

    // Create preview
    const reader = new FileReader();
    reader.onload = (e) => {
        previewUrl.value = e.target.result;
    };
    reader.readAsDataURL(file);

    // Emit upload event with file
    emit('upload', file);
};

const handleDelete = () => {
    if (confirm('Are you sure you want to remove this image?')) {
        previewUrl.value = null;
        error.value = null;
        if (fileInput.value) {
            fileInput.value.value = '';
        }
        emit('delete');
        emit('update:modelValue', null);
    }
};

defineExpose({
    setUploading: (value) => {
        uploading.value = value;
    },
    setError: (value) => {
        error.value = value;
    },
    clearPreview: () => {
        previewUrl.value = null;
    },
});
</script>

<style scoped>
.image-upload {
    width: 100%;
}

.upload-area {
    position: relative;
    width: 100%;
}

.preview-container {
    position: relative;
    width: 100%;
    max-width: 400px;
    aspect-ratio: 16 / 9;
    border-radius: 0.75rem;
    overflow: hidden;
    background: #f1f5f9;
    border: 2px solid #e2e8f0;
}

.preview-image {
    width: 100%;
    height: 100%;
    object-fit: contain;
    background: white;
}

.preview-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0);
    transition: background 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.preview-container:hover .preview-overlay {
    background: rgba(0, 0, 0, 0.5);
}

.preview-actions {
    display: flex;
    gap: 0.75rem;
    opacity: 0;
    transform: translateY(10px);
    transition: all 0.2s ease;
}

.preview-container:hover .preview-actions {
    opacity: 1;
    transform: translateY(0);
}

.action-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
    background: white;
    border-radius: 0.75rem;
    color: #334155;
    transition: all 0.2s ease;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.action-btn:hover:not(:disabled) {
    background: #f8fafc;
    transform: scale(1.05);
    color: #0ea5e9;
}

.action-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.delete-btn:hover:not(:disabled) {
    color: #ef4444;
}

.upload-progress {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.75);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
}

.progress-spinner {
    width: 48px;
    height: 48px;
    border: 4px solid rgba(255, 255, 255, 0.2);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.upload-button-area {
    width: 100%;
    max-width: 400px;
    aspect-ratio: 16 / 9;
    border: 2px dashed #cbd5e1;
    border-radius: 0.75rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 1rem;
    padding: 2rem;
    background: #f8fafc;
    cursor: pointer;
    transition: all 0.2s ease;
}

.upload-button-area:hover {
    border-color: #0ea5e9;
    background: #f0f9ff;
}

.upload-icon {
    display: flex;
    align-items: center;
    justify-content: center;
}

.upload-text {
    text-align: center;
}
</style>

