<template>
	<div class="ml-4 flex items-center text-xs text-gray-5">
		<SimpleSelect
			class="toggle-info-base toggle-bold toggle-text-sm toggle-center toggle-border-none dropdown-info-base margins-none select-perPageSelector"
			:options="perPageOptions"
			v-model="selectedPerPage" />
		<div class="ml-4 min-w-[100px]">{{ $t('pagination.records-per-page') }}</div>
	</div>
</template>

<script lang="ts" setup>
import { SelectOption } from '@/Types/models';
import { onMounted, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { useQueryString } from '@/Composables/useQueryString';
import SimpleSelect from '@/Components/Dashboard/SimpleSelect.vue';

const props = defineProps<{
	tableName: string;
}>();

const perPageOptions: Array<SelectOption> = [
	{ code: 10, label: '10' },
	{ code: 25, label: '25' },
	{ code: 50, label: '50' },
	{ code: 100, label: '100' },
];
const { queryArray, queryUrl } = useQueryString();
const selectedPerPage = ref<number | null>(null);
watch(selectedPerPage, async (newPerPage, oldPerPage) => {
	if (oldPerPage !== null) {
		let newData = queryArray(window.location.search);
		newData[`perPage[${props.tableName}]`] = String(selectedPerPage.value);
		newData[props.tableName] = '1';
		newData['page'] = '1';
		router.visit(queryUrl(newData), {
			preserveScroll: true,
		});
	}
});
onMounted(() => {
	if (queryArray(window.location.search)[`perPage[${props.tableName}]`]) {
		selectedPerPage.value = Number(queryArray(window.location.search)[`perPage[${props.tableName}]`]);
	} else {
		selectedPerPage.value = perPageOptions[0];
	}
});
</script>

<style scoped></style>
