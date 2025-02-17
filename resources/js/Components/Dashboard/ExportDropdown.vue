<template>
	<SimpleSelect
		:options="options"
		class="toggle-info-base toggle-border-none toggle-bold toggle-text-sm toggle-icon-download dropdown-info-base dropdown-separate open-indicator-whit h-12"
		:style="{
			width: selectWidth + 'px',
		}"
		:placeholder="capitalize($t('main.action.download'))" />
</template>

<script lang="ts" setup>
import SimpleSelect from '@/Components/Dashboard/SimpleSelect.vue';
import { useSelectOptions } from '@/Composables/useSelectOptions';
import { SelectOption } from '@/Types/models';
import { computed, onMounted, ref } from 'vue';
import { capitalize } from 'vue';
import { wTrans } from 'laravel-vue-i18n';

const props = defineProps<{
	customDownloadOptions?: SelectOption[];
}>();

let selectWidth = computed<number>(() => {
	return wTrans('main.action.download').value.length * 15 + 35;
});

let { downloadOptions } = useSelectOptions();

let options = ref(downloadOptions);
if (props.customDownloadOptions) {
	options.value = props.customDownloadOptions;
}
</script>
