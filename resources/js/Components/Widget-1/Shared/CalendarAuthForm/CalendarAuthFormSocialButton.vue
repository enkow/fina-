<template>
	<button
		type="button"
		:aria-label="label"
		class="flex h-13 w-13 justify-center rounded-full bg-ui-green text-white"
		:class="buttonStyles[social] ?? 'items-center'">
		<component :is="socialIcons[social]" />
	</button>
</template>

<script lang="ts" setup>
import AppleIcon from '@/Components/Widget-1/Icons/AppleIcon.vue';
import FacebookIcon from '@/Components/Widget-1/Icons/FacebookIcon.vue';
import GoogleIcon from '@/Components/Widget-1/Icons/GoogleIcon.vue';

type Social = 'apple' | 'facebook' | 'google';

defineProps<{
	label: string;
	social: Social;
}>();

const buttonStyles: Partial<Record<Social, string>> = {
	facebook: 'items-end',
};

const socialIcons: Record<Social, any> = {
	facebook: FacebookIcon,
	apple: AppleIcon,
	google: GoogleIcon,
};
</script>
