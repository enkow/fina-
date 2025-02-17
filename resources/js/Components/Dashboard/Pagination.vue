<template>
	<div
		v-if="meta.links && (route().current('club.*') || meta.links.length > 3)"
		class="mt-4 flex w-full flex-wrap items-center justify-between space-y-3 px-2 pb-3 sm:px-8 md:flex-nowrap md:space-y-0">
		<div class="md:w-fix w-full text-center text-xs text-gray-5 md:text-left">
			{{ $t('pagination.showing') }} {{ `${meta.from ?? 0}-${meta.to ?? 0}` }} z {{ meta.total }}
			{{ $t('pagination.results') }}
		</div>
		<div class="md:w-fix -mb-1 flex w-full items-center justify-center space-x-4 md:justify-end">
			<template v-for="(link, key) in meta.links">
				<div v-if="key === 0 || key === meta.links.length - 1">
					<span
						v-if="link.label.includes(nextLabel)"
						:class="{ hidden: meta.current_page === meta.links.length - 2 }"
						class="mx-0.5 flex items-center text-xs text-gray-5">
						<Link
							v-if="link.url !== null"
							:key="'key' + '_link'"
							:class="{ 'bg-info-base !text-white': link.active }"
							:href="paginatonLink(link.url)"
							class="flex h-5 w-5 items-center justify-center rounded-md bg-gray-2/30 font-semibold text-gray-5"
							preserve-scroll>
							>
						</Link>
						<div
							v-else
							:key="'key' + '_div'"
							:class="{ 'bg-info-base !text-white': link.active }"
							class="flex h-5 w-5 items-center justify-center rounded-md bg-gray-2/30 font-semibold text-gray-5"
							preserve-scroll>
							>
						</div>
						<span class="mx-4 hidden md:block">{{ $t('pagination.next') }}</span>
					</span>
					<span
						v-else
						:class="{ hidden: meta.current_page === 1 }"
						class="mx-0.5 flex items-center text-xs text-gray-5">
						<span class="mx-4 hidden md:block">{{ $t('pagination.previous') }}</span>
						<Link
							v-if="link.url !== null"
							:key="'key' + '_link'"
							:class="{ 'bg-info-base !text-white': link.active }"
							:href="paginatonLink(link.url)"
							class="flex h-5 w-5 items-center justify-center rounded-md bg-gray-2/30 font-semibold text-gray-5"
							preserve-scroll>
							&lt;
						</Link>
						<div
							v-else
							:key="'key' + '_div'"
							:class="{ 'bg-info-base !text-white': link.active }"
							class="flex h-5 w-5 items-center justify-center rounded-md bg-gray-2/30 font-semibold text-gray-5"
							preserve-scroll>
							&lt;
						</div>
					</span>
				</div>
				<div v-else>
					<div v-if="link.url === null" :key="'key' + '_div'" class="" v-html="link.label" />
					<Link
						v-else
						:key="'key' + '_link'"
						:class="{ 'bg-info-base !text-white': link.active }"
						:href="paginatonLink(link.url)"
						class="flex h-5.5 min-w-[1.375rem] max-w-[1.875rem] items-center justify-center overflow-hidden rounded-md font-semibold text-gray-5"
						preserve-scroll
						v-html="link.label" />
				</div>
			</template>
			<TableRecordSelector v-if="route().current('club.*')" :table-name="tableName" />
		</div>
	</div>
</template>

<script lang="ts" setup>
import { Link } from '@inertiajs/vue3';
import { useTableQueryString } from '@/Composables/useTableQueryString';
import TableRecordSelector from './TableRecordSelector.vue';
import { wTrans } from 'laravel-vue-i18n';
import { computed } from 'vue';

const { paginatonLink } = useTableQueryString();
const props = defineProps<{
	meta: Object;
	tableName: string;
}>();

const nextLabel = computed<string>(() => {
	return wTrans('pagination.next').value;
});
</script>
