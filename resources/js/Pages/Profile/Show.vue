<script setup>
import DeleteUserForm from '@/Pages/Profile/Partials/DeleteUserForm.vue';
import LogoutOtherBrowserSessionsForm from '@/Pages/Profile/Partials/LogoutOtherBrowserSessionsForm.vue';
import SectionBorder from '@/Components/Auth/SectionBorder.vue';
import UpdatePasswordForm from '@/Pages/Profile/Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from '@/Pages/Profile/Partials/UpdateProfileInformationForm.vue';
import PanelLayout from '@/Layouts/PanelLayout.vue';
import { onMounted } from 'vue';
import { loadLanguageAsync } from 'laravel-vue-i18n';

defineProps({
	confirmsTwoFactorAuthentication: Boolean,
	sessions: Array,
});
</script>

<template>
	<PanelLayout
		:breadcrumbs="[
			{
				label: $t('settings.account-settings'),
				href: route('profile.show'),
			},
		]">
		<div>
			<div class="mx-auto max-w-7xl py-10 sm:px-6 lg:px-8">
				<div v-if="$page.props.jetstream.canUpdateProfileInformation">
					<UpdateProfileInformationForm :user="$page.props.user" />

					<SectionBorder />
				</div>

				<div v-if="$page.props.jetstream.canUpdatePassword">
					<UpdatePasswordForm class="mt-10 sm:mt-0" />

					<SectionBorder />
				</div>

				<LogoutOtherBrowserSessionsForm :sessions="sessions" class="mt-10 sm:mt-0" />

				<template v-if="$page.props.jetstream.hasAccountDeletionFeatures">
					<SectionBorder />

					<DeleteUserForm class="mt-10 sm:mt-0" />
				</template>
			</div>
		</div>
	</PanelLayout>
</template>
