<template>
	<PanelLayout
		:breadcrumbs="[
			{ href: route('admin.help-sections.index'), label: 'Sekcje pomocy' },
			{
				href: route('admin.help-sections.edit', { help_section: helpSection }),
				label: helpSection.title,
			},
			{
				href: route('admin.help-sections.help-items.index', {
					help_section: helpSection,
				}),
				label: 'Wpisy',
			},
			{
				href: route('admin.help-sections.help-items.create', {
					help_section: helpSection,
				}),
				label: 'Nowy wpis',
			},
		]">
		<ContentContainer>
			<div class="col-span-12">
				<Card>
					<template #header>
						<h2>Sekcja {{ helpSection.name }} - dodaj wpis</h2>
					</template>

					<form
						class="mt-4 grid grid-cols-4 gap-x-4 gap-y-5"
						@submit.prevent="
							form.post(
								route('admin.help-sections.help-items.store', {
									help_section: helpSection,
								}),
								{ preserveState: true, preserveScroll: true },
							)
						">
						<div class="input-group col-span-4">
							<InputLabel value="Tytuł" />
							<TextInput v-model="form.title" />
							<div v-if="form.errors.title" class="error">
								{{ form.errors.title }}
							</div>
						</div>

						<div class="input-group col-span-4">
							<InputLabel value="Link do filmu" />
							<TextInput v-model="form.video_url" />
							<div v-if="form.errors.video_url" class="error">
								{{ form.errors.video_url }}
							</div>
						</div>

						<div class="input-group col-span-4">
							<InputLabel value="Miniaturka" />
							<input type="file" @input="form.thumbnail = $event.target.files[0]" />
							<div v-if="form.errors.thumbnail" class="error">
								{{ form.errors.thumbnail }}
							</div>
						</div>

						<div class="input-group col-span-4">
							<InputLabel value="Opis" />
							<TextInput v-model="form.description" />
							<div v-if="form.errors.description" class="error">
								{{ form.errors.description }}
							</div>
						</div>

						<div class="input-group col-span-4">
							<InputLabel value="Treść" />
							<TextareaInput v-model="form.content" :value="form.content" rows="6" />
							<div v-if="form.errors.content" class="error">
								{{ form.errors.content }}
							</div>
						</div>

						<div class="input-group col-span-4">
							<InputLabel value="Waga wyświetlania" />
							<TextInput v-model="form.weight" />
							<div v-if="form.errors.weight" class="error">
								{{ form.errors.weight }}
							</div>
						</div>

						<Button class="col-span-4 md:col-span-2 md:col-start-3" type="submit">
							{{ capitalize($t('main.action.update')) }}
						</Button>
					</form>
				</Card>
			</div>
		</ContentContainer>
	</PanelLayout>
</template>

<script lang="ts" setup>
import { HelpSection } from '@/Types/models';
import PanelLayout from '@/Layouts/PanelLayout.vue';
import ContentContainer from '@/Components/Dashboard/ContentContainer.vue';
import Card from '@/Components/Dashboard/Card.vue';
import InputLabel from '@/Components/Dashboard/InputLabel.vue';
import TextInput from '@/Components/Dashboard/TextInput.vue';
import { useString } from '@/Composables/useString';
import TextareaInput from '@/Components/Dashboard/TextareaInput.vue';
import { useForm } from '@inertiajs/vue3';
import Button from '@/Components/Dashboard/Buttons/Button.vue';

const props = defineProps<{
	helpSection: HelpSection;
}>();

const { capitalize } = useString();

const form = useForm({
	video_url: null,
	thumbnail: null,
	title: null,
	description: null,
	content: null,
	weight: null,
});
</script>
