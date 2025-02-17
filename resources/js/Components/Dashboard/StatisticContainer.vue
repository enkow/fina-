<template>
	<div class="relative h-28 w-full overflow-hidden rounded-md bg-brand-base pl-3 2xl:pl-6">
		<div class="absolute left-0 top-0">
			<svg
				class="-ml-2 opacity-40"
				fill="none"
				height="135"
				viewBox="0 0 115 135"
				width="115"
				xmlns="http://www.w3.org/2000/svg">
				<ellipse
					cx="49.9657"
					cy="59.5"
					fill="url(#paint0_linear_1630_15505)"
					rx="64.587"
					ry="82.5"
					transform="rotate(-180 49.9657 59.5)" />
				<defs>
					<linearGradient
						id="paint0_linear_1630_15505"
						gradientUnits="userSpaceOnUse"
						x1="49.9657"
						x2="49.9657"
						y1="-23"
						y2="142">
						<stop stop-color="white" />
						<stop offset="1" stop-color="white" stop-opacity="0" />
					</linearGradient>
				</defs>
			</svg>
		</div>
		<div class="flex h-full w-full space-x-3 leading-5 text-white 2xl:space-x-3.5">
			<div class="flex h-full items-center text-white">
				<slot name="icon"></slot>
			</div>
			<div class="font-medium">
				<slot />
			</div>
		</div>
	</div>
</template>
