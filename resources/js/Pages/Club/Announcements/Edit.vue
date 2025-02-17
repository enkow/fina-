<template>
	<PanelLayout
		:breadcrumbs="[
			{
				href: route('club.announcements.index'),
				label: $t('announcement.announcements-for-employees'),
			},
			{
				href: route('club.announcements.edit', { announcement: announcement }),
				label: $t('announcement.announcement') + ' ' + announcement.start_at,
			},
		]">
		<ContentContainer>
			<div class="col-span-12">
				<Card>
					<h2>{{ capitalize($t('announcement.edit-announcement')) }}</h2>

					<form
						class="mt-9 grid grid-cols-4 gap-x-4 gap-y-5"
						@submit.prevent="
							form.put(
								route('club.announcements.update', {
									announcement: announcement,
								}),
							)
						">
						<div class="input-group col-span-4">
							<InputLabel :value="capitalize($t('main.date'))" required />
							<SimpleDatepicker
								v-model="form.start_at"
								input-with-icon
								:disabled="!['admin', 'manager'].includes(usePage().props.user.type)" />
							<div v-if="form.errors.start_at" class="error">
								{{ form.errors.start_at }}
							</div>
						</div>

						<div class="input-group col-span-4">
							<InputLabel :value="capitalize($t('announcement.content'))" required />
							<TextareaInput
								v-model="form.content"
								:placeholder="$t('main.type-here')"
								:value="form.content"
								:disabled="!['admin', 'manager'].includes(usePage().props.user.type)" />
							<div v-if="form.errors.content" class="error">
								{{ form.errors.content }}
							</div>
						</div>

						<Button
							class="lg"
							type="submit"
							:disabled="!['admin', 'manager'].includes(usePage().props.user.type)">
							{{ capitalize($t('main.action.update')) }}
						</Button>
					</form>
				</Card>
			</div>
		</ContentContainer>
	</PanelLayout>
</template>

<script lang="ts" setup>
import { Announcement, User } from '@/Types/models';
import PanelLayout from '@/Layouts/PanelLayout.vue';
import ContentContainer from '@/Components/Dashboard/ContentContainer.vue';
import Card from '@/Components/Dashboard/Card.vue';
import InputLabel from '@/Components/Dashboard/InputLabel.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import TextareaInput from '@/Components/Dashboard/TextareaInput.vue';
import SimpleDatepicker from '@/Components/Dashboard/SimpleDatepicker.vue';
import { useString } from '@/Composables/useString';
import { usePanelStore } from '@/Stores/panel';

const { capitalize } = useString();
const panelStore = usePanelStore();

const props = defineProps<{
	flash: Object;
	user: User;
	announcement: Announcement;
}>();

const form = useForm({
	type: props.announcement.type,
	start_at: props.announcement.start_at,
	content: props.announcement.content,
});
</script>
