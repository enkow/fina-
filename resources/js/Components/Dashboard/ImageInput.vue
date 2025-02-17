<template>
    <div class="relative">
        <label
            class="flex h-12 w-full cursor-pointer items-center space-x-3 rounded-md border border-gray-3 pl-4"
            :class="{ '!cursor-default bg-gray-1': disabled }"
            for="photo-upload"
        >
            <svg
                fill="none"
                height="15"
                viewBox="0 0 13 15"
                width="13"
                xmlns="http://www.w3.org/2000/svg"
            >
                <path
                    clip-rule="evenodd"
                    d="M0.546875 13.4529C0.546875 13.2275 0.636428 13.0113 0.795834 12.8519C0.95524 12.6925 1.17144 12.6029 1.39688 12.6029H11.5969C11.8223 12.6029 12.0385 12.6925 12.1979 12.8519C12.3573 13.0113 12.4469 13.2275 12.4469 13.4529C12.4469 13.6784 12.3573 13.8946 12.1979 14.054C12.0385 14.2134 11.8223 14.3029 11.5969 14.3029H1.39688C1.17144 14.3029 0.95524 14.2134 0.795834 14.054C0.636428 13.8946 0.546875 13.6784 0.546875 13.4529ZM3.34593 4.70389C3.18657 4.54449 3.09706 4.32833 3.09706 4.10294C3.09706 3.87755 3.18657 3.66139 3.34593 3.50199L5.89593 0.951993C6.05532 0.792643 6.27149 0.703125 6.49688 0.703125C6.72226 0.703125 6.93843 0.792643 7.09783 0.951993L9.64783 3.50199C9.80266 3.66231 9.88833 3.87702 9.8864 4.09988C9.88446 4.32275 9.79507 4.53594 9.63747 4.69354C9.47987 4.85114 9.26668 4.94053 9.04382 4.94247C8.82095 4.9444 8.60624 4.85873 8.44593 4.70389L7.34688 3.60484V10.0529C7.34688 10.2784 7.25732 10.4946 7.09792 10.654C6.93851 10.8134 6.72231 10.9029 6.49688 10.9029C6.27144 10.9029 6.05524 10.8134 5.89583 10.654C5.73643 10.4946 5.64688 10.2784 5.64688 10.0529V3.60484L4.54782 4.70389C4.38843 4.86324 4.17226 4.95276 3.94688 4.95276C3.72149 4.95276 3.50532 4.86324 3.34593 4.70389Z"
                    fill="#9AA1B3"
                    fill-rule="evenodd"
                />
            </svg>

            <p
                :class="{
                    'text-gray-6': data.image,
                    'text-gray-3': !data.image,
                }"
            >
                {{ data.image?.name ?? placeholder }}
            </p>
        </label>
        <input
            id="photo-upload"
            class="absolute top-0 h-12 w-full opacity-0"
            type="file"
            :disabled="disabled"
            :class="{ 'disabled-readable': disabled }"
            :accept="accept || 'image/*'"
            @input="update"
        />
    </div>
    <div v-if="error" class="error">
        {{ error }}
    </div>

    <img
        v-if="!previewHidden && data.preview"
        :src="data.preview"
        class="w-full"
        :style="{
            width: previewWidth ?? '100%',
            height: !aspectRatio ? previewHeight : 'unset',
            aspectRatio,
        }"
    />
</template>

<script lang="ts" setup>
import { reactive } from 'vue';

const props = defineProps<{
    disabled?: boolean;
    placeholder?: string;
    modelValue?: object | null;
    accept?: string;
    error?: string | null;
    updatedUrl?: string | null;
    previewHidden?: boolean;
    previewWidth: string;
    previewHeight: string;
    aspectRatio: string;
}>();

const data = reactive({
    image: props.modelValue,
    preview: props.updatedUrl,
});

const emit = defineEmits<{
    (e: 'update:modelValue', value: object): void;
}>();

function update(event: InputEvent) {
    const files = (event.target as HTMLInputElement).files;

    if (!files) return;

    data.image = files[0];
    data.preview = URL.createObjectURL(files[0]);

    emit('update:modelValue', files[0]);
}
</script>
