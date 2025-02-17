<template>
	<PanelLayout
		:breadcrumbs="[
			{ href: route('admin.help-sections.index'), label: 'Sekcje pomocy' },
			{
				href: route('admin.help-sections.create'),
				label: 'Utwórz sekcje pomocy',
			},
		]">
		<ContentContainer>
			<div class="col-span-12">
				<Card>
					<template #header>
						<h2>Utwórz sekcje pomocy</h2>
					</template>

					<form
						class="mt-4 grid grid-cols-4 gap-x-4 gap-y-5"
						@submit.prevent="
							form.post(route('admin.help-sections.store'), {
								preserveState: true,
								preserveScroll: true,
							})
						">
						<div class="input-group col-span-4">
							<InputLabel value="Tytuł" />
							<TextInput v-model="form.title" />
							<div v-if="form.errors.title" class="error">
								{{ form.errors.title }}
							</div>
						</div>

						<div class="input-group col-span-4">
							<InputLabel :value="capitalize($t('main.description'))" />
							<TextareaInput v-model="form.description" :value="form.description" rows="6" />
							<div v-if="form.errors.description" class="error">
								{{ form.errors.description }}
							</div>
						</div>

						<div class="input-group col-span-4">
							<InputLabel value="Waga wyświetlania" />
							<TextInput v-model="form.weight" rows="6" />
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
import PanelLayout from '@/Layouts/PanelLayout.vue';
import ContentContainer from '@/Components/Dashboard/ContentContainer.vue';
import Card from '@/Components/Dashboard/Card.vue';
import { useForm } from '@inertiajs/vue3';
import { useString } from '@/Composables/useString';
import InputLabel from '@/Components/Dashboard/InputLabel.vue';
import TextInput from '@/Components/Dashboard/TextInput.vue';
import TextareaInput from '@/Components/Dashboard/TextareaInput.vue';
import Button from '@/Components/Dashboard/Buttons/Button.vue';

const { capitalize } = useString();

const form = useForm({
	title: '',
	description: '',
	weight: 1,
});
</script>
