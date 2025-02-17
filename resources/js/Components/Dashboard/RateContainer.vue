<template>
	<div class="relative h-23.5 w-full overflow-hidden rounded-md pl-6 pt-4">
		<div class="absolute -right-2 -top-3 opacity-50">
			<svg
				fill="currentColor"
				height="118"
				viewBox="0 0 115 118"
				width="115"
				xmlns="http://www.w3.org/2000/svg">
				<circle cx="70.5801" cy="58.3926" fill="url(#paint0_linear_1481_5522)" r="69.9629" />
				<defs>
					<linearGradient
						id="paint0_linear_1481_5522"
						gradientUnits="userSpaceOnUse"
						x1="70.5801"
						x2="70.5801"
						y1="-11.5703"
						y2="128.356">
						<stop stop-color="white" />
						<stop offset="1" stop-color="white" stop-opacity="0" />
					</linearGradient>
				</defs>
			</svg>
		</div>
		<div class="flex w-full justify-between space-x-3.5 leading-5 text-white">
			<div class="font-medium">
				<h3 class="text-base font-semibold">
					<slot name="name"></slot>
				</h3>
				<div class="flex">
					<h2 class="-mt-1 text-3xl font-semibold">
						<slot name="value"></slot>
					</h2>
					<p class="ml-2 mt-2 text-sm">/ 5.00</p>
				</div>
				<slot></slot>
			</div>
			<div class="pr-8 pt-3 text-white">
				<slot name="icon"></slot>
			</div>
		</div>
	</div>
</template>

<style scoped>
.brand {
	@apply bg-brand-base;
}

.info {
	@apply bg-info-base;
}

.grey {
	@apply bg-gray-3;
}

.warning {
	@apply bg-warning-base;
}
</style>
