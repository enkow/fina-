<template>
	<div class="flex w-full flex-col bg-white px-7 pb-6 pt-6 text-sm text-gray-7 shadow sm:rounded-lg">
		<div class="flex w-full flex-wrap items-center">
			<div class="w-full">
				<slot name="header"></slot>
			</div>
		</div>
		<h2
			v-if="this.$slots.description"
			:class="{ 'mb-6': this.$slots.default }"
			class="mt-3 text-sm font-normal">
			<slot name="description"></slot>
		</h2>
		<slot />
	</div>
</template>
