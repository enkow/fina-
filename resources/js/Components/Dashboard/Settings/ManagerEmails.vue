<template>
	<AccordionTab key="manager_mails" :class="`icon-${settingIconColor}`" :with-border="withBorder">
		<template #icon>
			<EnvelopeIcon />
		</template>

		<template #title>
			{{ featureTranslations['setting-title'] }}
		</template>

		<p class="block w-full">
			{{ featureTranslations['setting-description'] }}
		</p>

		<div
			:class="{
				'mb-8': managerEmails.length,
			}"
			class="mt-5 w-full space-y-4 text-xs">
			<p v-if="managerEmails.length" class="font-semibold">
				{{ $t('settings.your-mail-notification-addresses') }}
			</p>
			<div v-for="managerEmail in managerEmails" class="flex w-full items-center justify-between">
				<div class="font-light">
					{{ managerEmail.email }}
				</div>
				<Link
					:href="route('club.manager-mails.destroy', { manager_mail: managerEmail })"
					as="button"
					class="flex cursor-pointer items-center space-x-1.5 font-semibold"
					method="delete"
					preserve-scroll
					preserve-state>
					<p class="text-danger-base">
						{{ capitalize($t('main.action.delete')) }}
					</p>
					<TrashIcon class="cursor-pointer text-danger-base" />
				</Link>
			</div>
		</div>

		<form
			class="accordion-footer flex-wrap space-x-0 xl:flex-nowrap xl:space-x-6"
			@submit.prevent="
				form.post(route('club.manager-mails.store'), {
					preserveScroll: true,
					preserveState: true,
					onSuccess: () => form.reset(),
				})
			">
			<div class="w-full space-y-2 xl:w-1/2">
				<InputLabel :value="$t('settings.add-new-email-address')" class="text-xs" />
				<TextInput
					v-model="form.email"
					:disabled="!hasPermission"
					:class="{ 'disabled-readable': !hasPermission }"
					:placeholder="$t('main.type-here')"
					class="w-full" />
				<div v-if="form.errors.email" class="error">
					{{ form.errors.email }}
				</div>
			</div>
			<div class="ml-0 w-full space-y-2 xl:ml-3 xl:w-1/2">
				<InputLabel :value="'&nbsp'" class="text-xs" />
				<Button
					v-if="!hasPermission"
					class="lg accordion-footer__submit disabled !h-11 2xl:!h-12"
					v-tippy="{ allowHTML: true }"
					:content="'<p style=\'font-size:12px\'>' + $t('settings.no-permissions') + '</p>'">
					{{ $t('main.action.add') }}
				</Button>
				<Button v-else class="lg accordion-footer__submit !h-12" type="submit">
					{{ $t('main.action.add') }}
				</Button>
			</div>
		</form>
	</AccordionTab>
</template>

<script lang="ts" setup>
import AccordionTab from '@/Components/Dashboard/AccordionTab.vue';
import EnvelopeIcon from '@/Components/Dashboard/Icons/EnvelopeIcon.vue';
import TrashIcon from '@/Components/Dashboard/Icons/TrashIcon.vue';
import InputLabel from '@/Components/Dashboard/InputLabel.vue';
import TextInput from '@/Components/Dashboard/TextInput.vue';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import { useString } from '@/Composables/useString';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { Game, ManagerEmail } from '@/Types/models';
import { usePanelStore } from '@/Stores/panel';

const { capitalize } = useString();

const props = withDefaults(
	defineProps<{
		managerEmails: ManagerEmail[];
		game: Game;
		settingIconColor?: string;
		withBorder?: boolean;
	}>(),
	{
		settingIconColor: 'info',
	},
);

const { page } = usePanelStore();
const hasPermission = ['admin', 'manager'].includes(page.props.user.type as string);

const form = useForm({
	game_id: props.game.id,
	email: '',
});

function submit(): void {
	form.post(route('club.manager-mails.store'), {
		preserveScroll: true,
		preserveState: true,
		onSuccess: () => form.reset(),
	});
}

const featureTranslations = props.game.features.filter(
	(item) => item.type === 'has_manager_emails_setting',
)[0].translations;
</script>
