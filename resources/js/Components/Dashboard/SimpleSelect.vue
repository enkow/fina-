<template>
	<VueSelect
		:class="{ disabled: disabled, 'disabled-readable': readonly }"
		:clearable="false"
		:disabled="disabled || readonly"
		:filterable="filterable"
		:searchable="searchable"
		:reduce="(item) => item.code"
		class="select"
		:multiple="multiple">
		<template #no-options>
			{{ $t('main.no-options') }}
		</template>
		<template v-if="header" #list-header>
			<li class="vs__header">{{ header }}</li>
		</template>
		<template #open-indicator="{ attributes }">
			<span v-bind="attributes">
				<ChevronUpIcon />
			</span>
		</template>
		<template #option="{ code, label }">
			<div class="vs__dropdown-option__content">
				{{ label }}
			</div>
		</template>
	</VueSelect>
</template>
<style scoped>
.disabled:not(.disabled-readable) {
	&:deep(.vs__dropdown-toggle) {
		@apply !cursor-default !border !border-gray-3 bg-gray-1;
	}

	&:deep(.vs__selected) {
		@apply !opacity-0;
	}

	&:deep(.vs__actions) {
		@apply !cursor-default;
	}

	&:deep(.vs__open-indicator) {
		@apply hidden;
	}
}

.dropdown-fixed-height {
	&:deep(.vs__dropdown-menu) {
		@apply max-h-[198px];
	}
}

.disabled-readable {
	&:deep(.vs__dropdown-toggle) {
		@apply !cursor-default !bg-gray-1 text-gray-2;
	}

	&:deep(.vs__open-indicator) {
		@apply hidden;
	}

	&:deep(.vs__dropdown-toggle) {
		@apply border !border-gray-2;
	}

	&:deep(.vs__selected) {
		@apply !cursor-default !text-gray-3;
	}

	&:deep(.vs__search) {
		@apply !cursor-default !text-gray-3;
	}
}

.selected-pr-0 {
	&:deep(.vs__selected) {
		@apply !pr-0;
	}
}
</style>
<script lang="ts" setup>
import ChevronUpIcon from '@/Components/Dashboard/Icons/ChevronUpIcon.vue';
import VueSelect from 'vue-select';
import { ComputedRef, ref } from 'vue';

const props = withDefaults(
	defineProps<{
		header?: String | ComputedRef<String>;
		disabled?: boolean;
		readonly?: boolean;
		filterable?: boolean;
		searchable?: boolean;
		multiple?: boolean;
	}>(),
	{
		searchable: true,
		disabled: false,
	},
);
</script>
