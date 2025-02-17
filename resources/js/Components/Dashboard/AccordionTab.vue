<template>
	<div class="accordion" :class="{ border: withBorder }">
		<div class="accordion-header" @click.prevent="isExpandedLocal = !isExpandedLocal">
			<div class="flex items-center space-x-4 pr-2">
				<div v-if="slots.icon" class="accordion-header__tabIcon">
					<slot name="icon"></slot>
				</div>
				<span :class="{ 'text-danger-dark': danger }"><slot name="title" /></span>
			</div>
			<div class="flex items-center space-x-4">
				<DangerCircleIcon v-if="danger" class="w-5 text-danger-dark" />
				<div class="accordion-header__toggleIcon">
					<ChevronUpIcon v-if="isExpandedLocal" class="w-5" />
					<ChevronDownIcon v-if="!isExpandedLocal" class="w-5" />
				</div>
			</div>
		</div>
		<Collapse :when="isExpandedLocal" class="accordion-body v-collapse">
			<div class="accordion-content">
				<slot></slot>
			</div>
		</Collapse>
	</div>
</template>

<script lang="ts" setup>
import { onMounted, Ref, ref, useSlots } from 'vue';
import ChevronUpIcon from '@/Components/Dashboard/Icons/ChevronUpIcon.vue';
import ChevronDownIcon from '@/Components/Dashboard/Icons/ChevronDownIcon.vue';
import { Collapse } from 'vue-collapsed';
import DangerCircleIcon from '@/Components/Dashboard/Icons/DangerCircleIcon.vue';

const props = withDefaults(
	defineProps<{
		isExpanded?: boolean;
		withBorder?: boolean;
		danger: boolean;
	}>(),
	{
		isExpanded: false,
		withBorder: false,
		danger: false,
	},
);
let isExpandedLocal = ref<boolean | null>(null);

onMounted(() => {
	isExpandedLocal.value = props.isExpanded;
});

const slots = useSlots();
</script>

<style scoped>
.bolder {
	&.accordion {
		@apply rounded-md shadow-[0px_1px_9px_-2px] shadow-black/20;
	}

	.accordion-header {
		@apply text-lg font-semibold;
	}
}

.headerDarkWhenActive .expanded {
	&.accordion-header {
		@apply bg-gray-7 text-white;

		svg {
			@apply !text-white;
		}
	}
}

.plain {
	&.accordion {
		@apply shadow-none;
	}
}

.accordion {
	@apply relative block h-auto w-full bg-white;
	box-shadow: 0px 4px 20px -8px rgba(0, 0, 0, 0.1);

	.accordion-header,
	.accordion-content {
		@apply py-4 pl-6 pr-5 text-gray-7;
	}

	.accordion-header {
		@apply flex cursor-pointer items-center justify-between rounded-t-md text-sm transition-all sm:text-base;

		.accordion-header__tabIcon {
			@apply flex h-9 w-9 flex-none items-center justify-center rounded-md bg-gray-7/5 text-gray-7;

			&:deep(svg) {
				@apply h-5 w-5;
			}
		}

		.accordion-header__toggleIcon {
			@apply flex h-9 w-9 flex-none items-center justify-center rounded-md bg-gray-3/20 text-gray-3 transition-all hover:bg-gray-3/30 active:bg-gray-4/30;
		}
	}

	.accordion-body {
		@apply text-xs leading-5;
	}
}

.accordion.icon-brand .accordion-header__tabIcon {
	@apply bg-brand-light text-brand-base;
}

.accordion.icon-danger .accordion-header__tabIcon {
	@apply bg-danger-light text-danger-base;
}

.accordion.icon-warning .accordion-header__tabIcon {
	@apply bg-warning-light text-warning-base;
}

.accordion.icon-info .accordion-header__tabIcon {
	@apply bg-info-light text-info-base;
}

.accordion.icon-gray .accordion-header__tabIcon {
	@apply bg-gray-1 text-gray-7;
}

.v-collapse {
	transition: height 300ms;
}

/* STATISTICS ACCORDIONS */

.statistics-details__container.accordion {
	@apply bg-transparent shadow-none;

	.accordion-header {
		@apply flex items-center justify-center rounded-md bg-brand-base text-2xl font-bold capitalize text-white;

		.accordion-header__toggleIcon {
			@apply absolute right-0 mr-6 bg-transparent text-white;

			&:deep(svg) {
				@apply h-6 w-6;
			}
		}
	}

	.accordion-content {
		@apply bg-transparent;
	}
}

.statistics-details__single.accordion {
	@apply bg-white;

	.accordion-header {
		@apply flex items-center justify-between rounded-md bg-white text-base font-medium capitalize text-info-base;

		.accordion-header__toggleIcon {
			@apply static bg-transparent text-info-base;

			&:deep(svg) {
				@apply h-4 w-4;
			}
		}
	}

	.accordion-content {
		@apply bg-transparent;
	}

	.accordion-header,
	.accordion-content {
		@apply px-9.5;
	}
}
</style>
