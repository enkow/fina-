<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ApplicationMark from '@/Components/Auth/ApplicationMark.vue';
import Banner from '@/Components/Auth/Banner.vue';
import Dropdown from '@/Components/Auth/Dropdown.vue';
import DropdownLink from '@/Components/Auth/DropdownLink.vue';
import NavLink from '@/Components/Auth/NavLink.vue';
import ResponsiveNavLink from '@/Components/Auth/ResponsiveNavLink.vue';

defineProps({
	title: String,
});

const showingNavigationDropdown = ref(false);

const switchToTeam = (team) => {
	router.put(
		route('current-team.update'),
		{
			team_id: team.id,
		},
		{
			preserveState: false,
		},
	);
};

const logout = () => {
	Inertia.post(route('logout'));
};
</script>

<template>
	<div>
		<Head :title="title" />

		<Banner />

		<div class="min-h-screen bg-gray-100">
			<nav class="border-b border-gray-100 bg-white">
				<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
					<div class="flex h-16 justify-between">
						<div class="flex">
							<!-- Logo -->
							<div class="flex shrink-0 items-center">
								<Link :href="route('dashboard')">
									<ApplicationMark class="block h-9 w-auto" />
								</Link>
							</div>

							<!-- Navigation Links -->
							<div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
								<NavLink :active="route().current('user.dashboard')" :href="route('user.dashboard')">
									Panel
								</NavLink>
								<NavLink :active="route().current('user.calendar')" :href="route('user.calendar')">
									Kalendarz
								</NavLink>
								<NavLink :active="route().current('user.tables')" :href="route('user.tables')">
									Stoliki
								</NavLink>
								<NavLink :active="route().current('user.statistics')" :href="route('user.statistics')">
									Statystyki
								</NavLink>
								<NavLink :active="route().current('user.videos')" :href="route('user.videos')">Pomoc</NavLink>
								<NavLink :active="route().current('user.pagination')" :href="route('user.pagination')">
									Paginacja
								</NavLink>
								<NavLink :active="route().current('user.help-boxes')" :href="route('user.help-boxes')">
									Help boxy
								</NavLink>
								<NavLink :active="route().current('user.settlements')" :href="route('user.settlements')">
									Rozliczenia
								</NavLink>
							</div>
						</div>

						<div class="hidden sm:ml-6 sm:flex sm:items-center">
							<!-- Settings Dropdown -->
							<div class="relative ml-3">
								<Dropdown align="right" width="48">
									<template #trigger>
										<span class="inline-flex rounded-md">
											<button
												class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition hover:text-gray-700 focus:outline-none"
												type="button">
												{{ $page.props.user.name }}

												<svg
													class="-mr-0.5 ml-2 h-4 w-4"
													fill="currentColor"
													viewBox="0 0 20 20"
													xmlns="http://www.w3.org/2000/svg">
													<path
														clip-rule="evenodd"
														d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
														fill-rule="evenodd" />
												</svg>
											</button>
										</span>
									</template>

									<template #content>
										<!-- Account Management -->
										<div class="block px-4 py-2 text-xs capitalize text-gray-400">
											{{ $t('account') }}
										</div>

										<DropdownLink :href="route('profile.show')">
											{{ $t('nav.settings') }}
										</DropdownLink>

										<div class="border-t border-gray-100" />

										<!-- Authentication -->
										<form @submit.prevent="logout">
											<DropdownLink as="button">
												{{ $t('auth.logout') }}
											</DropdownLink>
										</form>
									</template>
								</Dropdown>
							</div>
						</div>

						<!-- Hamburger -->
						<div class="-mr-2 flex items-center sm:hidden">
							<button
								class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-500 focus:bg-gray-100 focus:text-gray-500 focus:outline-none"
								@click="showingNavigationDropdown = !showingNavigationDropdown">
								<svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
									<path
										:class="{
											hidden: showingNavigationDropdown,
											'inline-flex': !showingNavigationDropdown,
										}"
										d="M4 6h16M4 12h16M4 18h16"
										stroke-linecap="round"
										stroke-linejoin="round"
										stroke-width="2" />
									<path
										:class="{ hidden: !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }"
										d="M6 18L18 6M6 6l12 12"
										stroke-linecap="round"
										stroke-linejoin="round"
										stroke-width="2" />
								</svg>
							</button>
						</div>
					</div>
				</div>

				<!-- Responsive Navigation Menu -->
				<div
					:class="{
						block: showingNavigationDropdown,
						hidden: !showingNavigationDropdown,
					}"
					class="sm:hidden">
					<div class="space-y-1 pb-3 pt-2">
						<ResponsiveNavLink :active="route().current('user.dashboard')" :href="route('user.dashboard')">
							{{ $t('nav.dashboard') }}
						</ResponsiveNavLink>
						<ResponsiveNavLink :active="route().current('user.calendar')" :href="route('user.calendar')">
							{{ $t('nav.club-calendar') }}
						</ResponsiveNavLink>
					</div>

					<!-- Responsive Settings Options -->
					<div class="border-t border-gray-200 pb-1 pt-4">
						<div class="flex items-center px-4">
							<div v-if="$page.props.jetstream.managesProfilePhotos" class="mr-3 shrink-0">
								<img
									:alt="$page.props.user.name"
									:src="$page.props.user.profile_photo_url"
									class="h-10 w-10 rounded-full object-cover" />
							</div>

							<div>
								<div class="text-base font-medium text-gray-800">
									{{ $page.props.user.name }}
								</div>
								<div class="text-sm font-medium text-gray-500">
									{{ $page.props.user.email }}
								</div>
							</div>
						</div>

						<div class="mt-3 space-y-1">
							<ResponsiveNavLink :active="route().current('profile.show')" :href="route('profile.show')">
								{{ $t('nav.settings') }}
							</ResponsiveNavLink>

							<!-- Authentication -->
							<form method="POST" @submit.prevent="logout">
								<ResponsiveNavLink as="button">
									{{ $t('auth.logout') }}
								</ResponsiveNavLink>
							</form>
						</div>
					</div>
				</div>
			</nav>

			<!-- Page Heading -->
			<header v-if="$slots.header" class="bg-white shadow">
				<div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
					<slot name="header" />
				</div>
			</header>

			<!-- Page Content -->
			<main>
				<slot />
			</main>
		</div>
	</div>
</template>
