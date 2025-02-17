<template>
	<div class="relative">
		<template v-if="navigation">
			<button
				:id="navigation.prevButtonId"
				type="button"
				class="right-full -translate-x-7"
				:class="BUTTON_STYLES">
				<PrevIcon />
			</button>
			<button
				:id="navigation.nextButtonId"
				type="button"
				class="left-full translate-x-7"
				:class="BUTTON_STYLES">
				<NextIcon />
			</button>
		</template>
		<slot />
	</div>
</template>

<script lang="ts" setup>
import PrevIcon from '@/Components/Widget-1/Icons/PrevIcon.vue';
import NextIcon from '@/Components/Widget-1/Icons/NextIcon.vue';

const BUTTON_STYLES =
	'absolute bg-white text-ui-green disabled:text-ui-black/30 w-12 h-12 items-center justify-center rounded-full top-1/2 -translate-y-1/2 z-10 shadow-calendar-carousel-button [&>svg]:w-2.5 hidden xl:flex';

defineProps<{
	navigation?: {
		prevButtonId: string;
		nextButtonId: string;
	};
}>();
</script>

<style>
.swiper-pagination {
	@apply mt-9 flex justify-center gap-x-2.5 xl:hidden;
}

.swiper-pagination-bullet {
	@apply h-5 w-5 rounded-full bg-ui-green/40 transition-all duration-300;
}

.swiper-pagination-bullet-active {
	@apply w-14 bg-ui-green;
}
</style>
