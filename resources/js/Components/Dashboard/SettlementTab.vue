<template>
	<div :class="{ expanded: isExpand }" class="accordion">
		<div class="flex cursor-pointer items-center px-6 pl-6" @click.prevent="toggleExpand()">
			<div class="flex flex-1 justify-between">
				<span><slot name="title" /></span>
				<span><slot name="amount" /></span>
			</div>
			<div class="w-40 pt-3">
				<Button v-if="!isExpand" class="info sm mb-3 ml-auto w-36 uppercase">
					<ChevronDownIcon class="mr-2" />
					<div class="w-14">
						{{ $t('main.action.expand') }}
					</div>
				</Button>
				<Button v-if="isExpand" class="info sm mb-3 ml-auto w-36 uppercase">
					<ChevronUpIcon class="mr-2" />
					<div class="w-14">
						{{ $t('main.action.collapse') }}
					</div>
				</Button>
			</div>
		</div>
		<hr v-if="isExpand" />
		<div class="flex w-full justify-between">
			<div
				ref="item"
				:class="{ 'mt-5': isExpand }"
				:style="{ height: isExpand ? item.computedHeight : '' }"
				class="h-0 flex-1 overflow-hidden pl-6 text-xs leading-5 transition-all">
				<div class="accordion-content">
					<slot></slot>
				</div>
			</div>
			<div class="w-46"></div>
		</div>
	</div>
</template>

<script lang="ts" setup>
import { onMounted, Ref, ref } from 'vue';
import ChevronUpIcon from '@/Components/Dashboard/Icons/ChevronUpIcon.vue';
import ChevronDownIcon from '@/Components/Dashboard/Icons/ChevronDownIcon.vue';
import Button from '@/Components/Dashboard/Buttons/Button.vue';

const isExpand: Ref<Boolean> = ref(false);
const item: Ref<HTMLElement | null> = ref(null);

function toggleExpand() {
	isExpand.value = !isExpand.value;
}

function getComputedHeight() {
	if (item.value !== null) {
		item.value.style.height = 'auto';
		item.value.style.position = 'absolute';
		item.value.style.visibility = 'hidden';
		item.value.style.display = 'block';

		let height = getComputedStyle(item.value).height;
		item.value.computedHeight = parseInt(height) + 10 + 'px';

		item.value.style.height = '0';
		item.value.style.position = 'static';
		item.value.style.visibility = 'visible';
		item.value.style.display = 'block';
	}
}

onMounted(() => {
	item.value.focus();
	getComputedHeight();
});
</script>
