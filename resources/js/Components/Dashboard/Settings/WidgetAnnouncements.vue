<template>
	<AccordionTab key="widget_announcements" :class="`icon-${settingIconColor}`" :with-border="withBorder">
		<template #icon>
			<PuzzleIcon />
		</template>

		<template #title>
			{{ featureTranslations['setting-title'] }}
		</template>

		{{ featureTranslations['setting-description'] }}
		<Link
			v-if="featureTranslations['setting-see-mode-link']"
			:href="featureTranslations['setting-see-mode-link']"
			class="mt-1 block w-full font-bold text-brand-base underline">
			{{ featureTranslations['setting-see-mode-content'] }}
		</Link>

		<div class="mt-5 w-full">
			<div
				v-for="announcement in announcements"
				:key="'widget-announcement-' + announcement.id"
				class="w-full justify-between pb-4 pt-2">
				<div class="flex w-full justify-between">
					<div class="pr-20 text-gray-7/60">
						<p class="font-semibold">{{ announcement.start_at }} - {{ announcement.end_at }}</p>
						{{ announcement.content }}
					</div>
					<div class="text-danger-base">
						<Link
							:href="
								route('club.announcements.destroy', {
									announcement: announcement,
								})
							"
							as="button"
							class="flex items-center text-xxs font-semibold"
							method="delete"
							preserve-scroll
							preserve-state
							v-if="hasPermission">
							<p class="capitalize">{{ $t('main.action.delete') }}</p>
							<TrashIcon class="ml-1.5 h-3 w-3" />
						</Link>
					</div>
				</div>
			</div>
		</div>

		<form @submit.prevent="submitForm(form)">
			<div class="mt-5 space-y-4">
				<div class="space-y-2">
					<InputLabel class="text-xs capitalize">{{ capitalize($t('main.date')) }}</InputLabel>
					<RangeDatepicker
						:only-future-dates="true"
						v-model="form.date_range"
						:disabled="!hasPermission"
						input-with-icon
						position="top"
						no-disabled-range
						:disabledDates="
							(date: Date): boolean => {
								const result = props.announcements.some((announcement) => {
									const startAt = new Date(announcement?.start_at || '');
									const endAt = new Date(announcement?.end_at || '');

									if (date >= startAt && date <= endAt) {
										return true;
									}
								});

								return result;
							}
						" />
					<div v-if="!validDataError" class="error">
						{{ $t('announcement.validation.two-announcement-the-same-day') }}
					</div>
					<div v-if="form.errors.start_at || form.errors.end_at" class="error">
						{{ form.errors.start_at }}
						<br />
						{{ form.errors.end_at }}
					</div>
				</div>
				<div class="space-y-2">
					<InputLabel class="text-xs capitalize">
						{{ $t('announcement.announcement-content-top') }}
					</InputLabel>
					<TextareaInput
						v-model="form.content"
						:value="form.content"
						:disabled="!hasPermission"
						:class="{ 'disabled-readable': !hasPermission }" />
					<div v-if="form.errors.content" class="error">
						{{ form.errors.content }}
					</div>
				</div>
			</div>

			<div class="accordion-footer">
				<div class="hidden w-1/2 space-y-2 sm:block"></div>
				<div class="w-full sm:w-1/2">
					<Button
						v-if="!hasPermission"
						v-tippy="{ allowHTML: true }"
						:content="'<p style=\'font-size:12px\'>' + $t('settings.no-permissions') + '</p>'"
						class="lg accordion-footer__submit disabled !h-12">
						{{ $t('main.action.update') }}
					</Button>
					<Button v-else class="lg accordion-footer__submit !h-12" type="submit">
						{{ $t('main.action.update') }}
					</Button>
				</div>
			</div>
		</form>
	</AccordionTab>
</template>
<script lang="ts" setup>
import AccordionTab from '@/Components/Dashboard/AccordionTab.vue';
import TrashIcon from '@/Components/Dashboard/Icons/TrashIcon.vue';
import InputLabel from '@/Components/Dashboard/InputLabel.vue';
import TextareaInput from '@/Components/Dashboard/TextareaInput.vue';
import { InertiaForm, Link, useForm } from '@inertiajs/vue3';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import { Announcement, Game } from '@/Types/models';
import { useString } from '@/Composables/useString';
import PuzzleIcon from '@/Components/Dashboard/Icons/PuzzleIcon.vue';
import { usePanelStore } from '@/Stores/panel';
import RangeDatepicker from '../RangeDatepicker.vue';
import dayjs from 'dayjs';
import { ref } from 'vue';

const props = withDefaults(
	defineProps<{
		game: Game;
		announcements: Announcement[];
		settingIconColor?: string;
		withBorder?: boolean;
	}>(),
	{
		settingIconColor: 'info',
	},
);

const { page } = usePanelStore();
const hasPermission = ['admin', 'manager'].includes(page.props.user.type as string);
const { capitalize } = useString();
let validDataError = ref(true);

const formatDate = (date: String | undefined) => {
	if (date) {
		return dayjs(date.toString()).format('YYYY-MM-DD');
	}
	return null;
};

const form = useForm({
	game_id: props.game.id,
	type: 1,
	content: '',
	date_range: null,
	start_at: null,
	end_at: null,
});

function submitForm(form: InertiaForm<any>) {
	if (form.date_range) {
		form.start_at = formatDate(form.date_range[0]?.toString());
		form.end_at = formatDate(form.date_range[1]?.toString());
	}

	validDataError.value = true;
	for (const announcement of props.announcements) {
		if (
			(form.start_at >= announcement.start_at && form.start_at <= announcement.end_at) ||
			(form.end_at >= announcement.start_at && form.end_at <= announcement.end_at) ||
			(announcement.start_at >= form.start_at && announcement.start_at <= form.end_at) ||
			(announcement.end_at >= form.start_at && announcement.end_at <= form.end_at)
		) {
			validDataError.value = false;
			return;
		}
	}

	form.post(route('club.announcements.store'), {
		preserveState: true,
		preserveScroll: true,
		onSuccess: () => {
			form.content = '';
			form.date_range = null;
		},
	});
}

const featureTranslations = props.game.features.filter(
	(item) => item.type === 'has_widget_announcements_setting',
)[0].translations;
</script>
