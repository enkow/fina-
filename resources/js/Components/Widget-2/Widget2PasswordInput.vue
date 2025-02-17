<template>
	<Widget2Input :type="showPassword ? 'text' : 'password'" :placeholder="placeholder" :error="error">
		<template v-if="!error" #rightSection>
			<button type="button" class="text-ui-green-950" @click="showPassword = !showPassword">
				<component :is="showPassword ? EyeSlashIcon : EyeIcon" />
			</button>
		</template>
	</Widget2Input>
</template>

<script lang="ts" setup>
import Widget2Input from './Widget2Input.vue';
import EyeIcon from './Icons/EyeIcon.vue';
import EyeSlashIcon from './Icons/EyeSlashIcon.vue';
import { ref } from 'vue';

const showPassword = ref(false);

defineProps<{
	error?: boolean;
	placeholder?: string;
}>();
</script>
