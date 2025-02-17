<template>
	<div class="relative">
		<Menu v-slot="{ open }">
			<slot name="button" :open="open" />
			<MenuItems
				class="absolute left-1/2 top-full z-50 flex min-w-full -translate-x-1/2 translate-y-3 flex-col gap-y-4 rounded-lg bg-white p-2.5 shadow-md shadow-zinc-800/20 focus:outline-none">
				<slot name="items" />
			</MenuItems>
		</Menu>
	</div>
</template>

<script lang="ts" setup>
import { Menu, MenuItems } from '@headlessui/vue';
</script>
