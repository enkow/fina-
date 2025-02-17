<template>
	<div
		:class="{
			reduced: isReduced,
		}"
		class="sidebar">
		<div class="flex h-full flex-col items-center justify-end overflow-y-auto overflow-x-hidden">
			<div class="absolute inset-0 w-72">
				<div class="sidebar-header">
					<BookgameLogo v-if="!isReduced" class="sidebar-header__logo" />
					<DoubleChevronLeft class="sidebar-header__toggler" @click="toggle" />
				</div>

				<div class="max-h-[calc(90vh-10rem)] overflow-y-auto">
					<Nav :is-reduced="isReduced" :items="navigationItems" />

					<div
						v-if="usePage().props.user.type !== 'admin' && !isReduced && usePage().props.helpEnabled"
						class="mt-8 w-full px-6 text-center">
						<Button
							:href="route('club.help-sections.index')"
							class="mx-auto w-full !px-0 !py-3 text-sm !font-medium !text-gray-7"
							type="link">
							<LofebuoyIcon class="my-1 mr-1.5 h-4 w-4" />
							{{ $t('help.title') }}
						</Button>
					</div>
				</div>
			</div>
			<Button class="sidebar-accountButton" @click="$emit('accountModalToggle')">
				{{ initials(usePage().props.user.first_name + ' ' + usePage().props.user.last_name) }}
			</Button>
		</div>
	</div>
</template>

<script lang="ts" setup>
import LofebuoyIcon from '@/Components/Dashboard/Icons/LofebuoyIcon.vue';
import Nav from './Nav.vue';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import BookgameLogo from '@/Components/BookgameLogo.vue';
import DoubleChevronLeft from '@/Components/Dashboard/Icons/DoubleChevronLeft.vue';
import { useString } from '@/Composables/useString';
import { usePage } from '@inertiajs/vue3';

const { initials } = useString();

const props = withDefaults(
	defineProps<{
		isReduced?: boolean;
		navigationItems?: any;
	}>(),
	{
		isReduced: false,
		navigationItems: [],
	},
);

const emit = defineEmits<{
	(e: 'toggle'): void;
	(e: 'accountModalToggle'): void;
}>();
const toggle = () => {
	emit('toggle');
};
</script>

<style scoped>
.sidebar {
	@apply fixed top-0 z-10 hidden h-screen w-72 overflow-hidden bg-gray-7 transition-all duration-300 ease-in-out lg:block;

	.sidebar-header {
		@apply flex h-20 items-center justify-between bg-gray-8 px-6.5 py-6.5;

		.sidebar-header__toggler {
			@apply h-5.5 cursor-pointer;
		}

		.sidebar-header__logo {
			@apply -ml-4 -mt-2 block h-9 transition-none;
		}
	}

	.sidebar-accountButton {
		@apply my-8 h-24 min-h-[6rem] w-24 origin-center scale-100 !rounded-full !px-0 !text-[1.75rem] !font-medium transition-transform duration-300 ease-in-out;
	}
}

.sidebar.reduced {
	@apply w-18;

	.sidebar-header__toggler {
		@apply rotate-180;
	}

	.sidebar-header__logo {
		@apply hidden;
	}

	.sidebar-accountButton {
		@apply scale-50;
	}
}
</style>
