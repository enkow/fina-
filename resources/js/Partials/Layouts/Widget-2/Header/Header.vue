<template>
	<header class="mb-8 flex shrink-0 items-center justify-between md:relative md:mb-0 md:h-14 md:justify-end">
		<div class="flex h-full items-center gap-x-10 md:absolute md:left-0 md:-translate-y-1/2">
			<HeaderHeading />
			<HeaderStepper />
		</div>
		<div class="z-10 flex items-center gap-x-5 md:translate-y-1/2">
			<HeaderSignOutButton />
			<HeaderLocaleDropdown />
		</div>
	</header>
</template>

<script lang="ts" setup>
import HeaderHeading from './HeaderHeading.vue';
import HeaderStepper from './HeaderStepper.vue';
import HeaderSignOutButton from './HeaderSignOutButton.vue';
import HeaderLocaleDropdown from './HeaderLocaleDropdown.vue';
</script>
