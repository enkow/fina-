<template>
	<div
		class="flex min-h-screen flex-col items-center justify-center bg-gray-100 md:pt-[calc((50%-400px)/2)] lg:justify-start">
		<div>
			<slot name="logo" />
		</div>

		<div
			class="mt-12.25 w-full overflow-hidden bg-white px-16.5 py-4 pb-8.5 shadow-[8px_8px_0px_#F0F1F4] sm:max-w-auth sm:rounded-3xl">
			<slot />
		</div>
	</div>
</template>
