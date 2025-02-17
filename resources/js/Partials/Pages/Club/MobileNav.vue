<template>
	<div class="flex h-14 w-full justify-between bg-gray-8 text-white lg:hidden">
		<div class="flex h-14 items-center pl-5">
			<BookgameLogo />
		</div>
		<div class="flex h-14 cursor-pointer items-center pr-3" @click="expandedStatus = !expandedStatus">
			<HamburgerIcon class="h-8 w-8" />
		</div>
	</div>
	<ul v-show="expandedStatus" class="nav">
		<li
			v-for="item in menu"
			:class="{
				active: item.active,
				'!hidden': item.disabled,
			}"
			class="nav-item">
			<component
				:is="item.dropdownItems.length ? 'div' : Link"
				:class="{ 'cursor-pointer': item.dropdownItems.length }"
				:href="item.href"
				class="nav-item__link"
				@click="itemClicked(item)">
				<component :is="item.icon" class="nav-item__icon" />
				<span class="nav-item__label">{{ item.label }}</span>
				<ChevronRightIcon
					v-if="item.dropdownItems.length"
					:class="{ 'rotate-90 transform': item.expanded }"
					class="nav-item__toggler" />
			</component>
			<Transition
				enter-active-class="transition-all duration-300"
				enter-from-class="max-h-0 !my-0 opacity-0"
				enter-to-class="max-h-96 !mb-3 !mt-3.5 opacity-100"
				leave-active-class="transition-all duration-300"
				leave-from-class="max-h-96 !mb-3 !mt-3.5 opacity-100"
				leave-to-class="max-h-0 !my-0 opacity-0">
				<ul v-show="item.expanded" class="dropdown w-full">
					<li
						v-for="dropdownItem in item.dropdownItems"
						:class="{
							active: dropdownItem.active,
						}"
						class="dropdown-item">
						<Link :href="dropdownItem.href" class="dropdown-item__label">
							{{ dropdownItem.label }}
						</Link>
					</li>
				</ul>
			</Transition>
		</li>

		<li class="nav-item">
			<Link :href="route('logout')" as="button" class="nav-item__link" method="post">
				<svg
					class="nav-item__icon"
					fill="none"
					stroke="currentColor"
					stroke-width="1.5"
					viewBox="0 0 24 24"
					xmlns="http://www.w3.org/2000/svg">
					<path
						d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"
						stroke-linecap="round"
						stroke-linejoin="round" />
				</svg>

				{{ $t('auth.logout') }}
			</Link>
		</li>
	</ul>
</template>

<script lang="ts" setup>
import ChevronRightIcon from '@/Components/Dashboard/Icons/ChevronRightIcon.vue';
import HamburgerIcon from '@/Components/Dashboard/Icons/HamburgerIcon.vue';
import { Link } from '@inertiajs/vue3';
import BookgameLogo from '@/Components/BookgameLogo.vue';
import { ref } from 'vue';
import { NavigationItem } from '@/Composables/useNavigation';

const props = defineProps<{
	isExpanded: boolean;
	items: NavigationItem[];
}>();

const menu = ref(props.items);
const itemClicked = (item: any) => {
	if (item.dropdownItems.length) {
		menu.value.forEach((i) => {
			if (i === item) {
				i.expanded = !i.expanded;
			} else {
				i.expanded = false;
			}
		});
	}
};

const expandedStatus = ref(props.isExpanded);
</script>

<style scoped>
.nav {
	@apply absolute inset-x-0 z-10 block border-b border-gray-2 bg-gray-7 pb-1.5 pt-1.5 shadow-sm lg:hidden;

	.nav-item {
		@apply mb-1 flex flex-wrap items-center leading-6 text-gray-3;

		.nav-item__link {
			@apply flex w-full items-center py-3 pl-5 hover:text-brand-base;
		}

		.nav-item__icon {
			@apply mr-3.5 h-4 w-4;
		}

		.nav-item__toggler {
			@apply ml-auto mr-7 transition-transform duration-300;
		}

		&.active .nav-item__link {
			@apply bg-brand-base text-white;
		}

		.dropdown {
			@apply mb-3 ml-11 mt-3.5;

			.dropdown-item {
				@apply my-1 block py-1 text-sm transition transition-all;

				&:before {
					@apply mr-2.5 inline-block align-middle text-2xl leading-5 content-['·'];
				}

				&.active,
				&:hover {
					@apply list-item py-1 text-brand-base;
				}
			}
		}
	}
}
</style>
