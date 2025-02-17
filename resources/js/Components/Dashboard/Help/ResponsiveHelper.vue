<template>
	<div class="col-span-12 xl:col-span-3">
		<MascotHelper class="hidden xl:block">
			<template #mascot>
				<slot name="mascot" />
			</template>
			<template #button>
				<slot name="button" />
			</template>
			<slot />
		</MascotHelper>
		<WideHelper class="min-h-[130px] xl:hidden">
			<template #mascot>
				<slot name="mascot" />
			</template>
			<template #button>
				<slot name="button" />
			</template>
			<slot />
		</WideHelper>
	</div>
</template>
<script lang="ts" setup>
import WideHelper from '@/Components/Dashboard/Help/WideHelper.vue';
import MascotHelper from '@/Components/Dashboard/Help/MascotHelper.vue';

const props = defineProps<{
	width: String;
}>();
</script>
