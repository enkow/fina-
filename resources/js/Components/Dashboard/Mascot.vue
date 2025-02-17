<template>
	<component :is="mascotComponent" />
</template>

<script lang="ts" setup>
import { shallowRef, watchEffect } from 'vue';

const props = defineProps<{
	type: number;
}>();

const mascotComponent = shallowRef(null);

watchEffect(() => {
	importMascot(props.type);
});

async function importMascot(mascotNumber: number) {
	const { default: mascot } = await import(`./Mascots/Mascot${mascotNumber}.vue`);

	mascotComponent.value = mascot;
}
</script>
