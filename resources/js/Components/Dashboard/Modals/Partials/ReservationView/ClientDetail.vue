<template>
	<div class="flex items-center">
		<div class="icon-wrapper">
			<slot />
		</div>
		<div class="flex items-center space-x-2 text-xs xxs:text-sm xs:text-base sm:text-xl">
			<p>{{ value }}</p>
			<svg
				width="14"
				height="14"
				viewBox="0 0 11 12"
				fill="none"
				xmlns="http://www.w3.org/2000/svg"
				v-if="withCartIcon">
				<path
					fill-rule="evenodd"
					clip-rule="evenodd"
					d="M1.47558 0H0.335938L0.603154 1.10855L2.13849 8.13563H2.14514V8.31411H9.3507V8.17665L10.5662 2.82181L10.7536 2.2171H1.95392L1.63798 0.769886L1.47558 0ZM9.3435 3.32564H2.19614L3.04362 7.20556H8.46276L9.3435 3.32564Z"
					fill="black" />
				<path
					d="M3.80386 11.0843C4.09787 11.0843 4.37983 10.9675 4.58772 10.7596C4.79561 10.5517 4.91241 10.2697 4.91241 9.97573C4.91241 9.68173 4.79561 9.39977 4.58772 9.19187C4.37983 8.98398 4.09787 8.86719 3.80386 8.86719C3.50986 8.86719 3.22789 8.98398 3.02 9.19187C2.81211 9.39977 2.69531 9.68173 2.69531 9.97573C2.69531 10.2697 2.81211 10.5517 3.02 10.7596C3.22789 10.9675 3.50986 11.0843 3.80386 11.0843ZM8.79232 9.97573C8.79232 10.2697 8.67553 10.5517 8.46764 10.7596C8.25975 10.9675 7.97778 11.0843 7.68378 11.0843C7.38977 11.0843 7.10781 10.9675 6.89992 10.7596C6.69202 10.5517 6.57523 10.2697 6.57523 9.97573C6.57523 9.68173 6.69202 9.39977 6.89992 9.19187C7.10781 8.98398 7.38977 8.86719 7.68378 8.86719C7.97778 8.86719 8.25975 8.98398 8.46764 9.19187C8.67553 9.39977 8.79232 9.68173 8.79232 9.97573Z"
					fill="black" />
			</svg>
		</div>
	</div>
</template>

<script lang="ts" setup>
const props = withDefaults(
	defineProps<{
		value?: String;
		withCartIcon?: boolean;
	}>(),
	{
		withCartIcon: false,
	},
);
</script>

<style scoped>
.icon-wrapper:deep(svg) {
	@apply mr-3 h-6 text-brand-base;
}
</style>
