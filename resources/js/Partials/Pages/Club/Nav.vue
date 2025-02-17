<template>
	<ul
		:class="{
			reduced: isReduced,
		}"
		class="nav">
		<li
			v-for="item in menu"
			:class="{
				active: item.active,
				'!hidden': (item.showOnlyIfSidebarReduced && !isReduced) || item.disabled,
			}"
			class="nav-item">
			<component
				:is="!isReduced && item.dropdownItems.length ? 'div' : Link"
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
				enter-to-class="max-h-96 !mb-1.5 !mt-1.5 opacity-100"
				leave-active-class="transition-all duration-300"
				leave-from-class="max-h-96 !mb-3 !mt-3.5 opacity-100"
				leave-to-class="max-h-0 !my-0 opacity-0">
				<ul v-show="item.expanded" class="dropdown w-full overflow-hidden">
					<li
						v-for="dropdownItem in item.dropdownItems"
						v-show="!dropdownItem.disabled"
						:class="{ active: dropdownItem.active }"
						class="dropdown-item">
						<Link :href="dropdownItem.href" class="dropdown-item__label">
							{{ dropdownItem.label }}
						</Link>
					</li>
				</ul>
			</Transition>
		</li>
	</ul>
</template>

<script lang="ts" setup>
import { Link, usePage } from '@inertiajs/vue3';
import ChevronRightIcon from '@/Components/Dashboard/Icons/ChevronRightIcon.vue';
import { User } from '@/Types/models';
import { ref } from 'vue';
import { NavigationItem } from '@/Composables/useNavigation';

const props = withDefaults(
	defineProps<{
		isReduced: boolean;
		items: NavigationItem[];
	}>(),
	{
		isReduced: true,
	},
);
const page = usePage<{
	user: User;
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
</script>

<style scoped>
.nav {
	@apply mt-4.5;

	&.reduced {
		.nav-item__label {
			@apply !hidden;
		}

		.nav-item__icon {
			@apply mr-0 h-5 w-5;
		}

		.nav-item__toggler {
			@apply !hidden;
		}

		.dropdown {
			@apply !hidden;
		}
	}

	.nav-item {
		@apply mb-1 flex flex-wrap items-center leading-6 text-gray-3;

		.nav-item__link {
			@apply flex w-full items-center py-3 pl-6.5 hover:text-brand-base;
		}

		.nav-item__icon {
			@apply my-2 mr-3.5 h-4 w-4;
		}

		.nav-item__toggler {
			@apply ml-auto mr-7 transition-transform duration-300;
		}

		&.active .nav-item__link {
			@apply bg-brand-base text-white;
		}

		.dropdown {
			@apply mb-1.5 ml-10.5 mt-1.5;

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
