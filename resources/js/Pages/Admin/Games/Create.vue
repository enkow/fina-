<template>
	<PanelLayout
		:breadcrumbs="[
			{ href: route('admin.games.index'), label: 'Gry' },
			{ href: route('admin.games.create'), label: 'Utwórz nową grę' },
		]">
		<ContentContainer>
			<div class="col-span-12">
				<Card>
					<template #header>
						<h2>Utwórz nową grę</h2>
					</template>

					<form
						class="mt-4 grid grid-cols-4 gap-x-4 gap-y-5"
						@submit.prevent="form.post(route('admin.games.store', { game: game }))">
						<div class="input-group col-span-2">
							<InputLabel :value="capitalize($t('main.name'))" />
							<TextInput v-model="form.name" />
							<div v-if="form.errors.name" class="error">
								{{ form.errors.name }}
							</div>
						</div>

						<div class="col-span-2">
							<InputLabel value="Ikona" />
							<TextInput v-model="form.icon" />
							<div v-if="form.errors.icon" class="error">
								{{ form.errors.icon }}
							</div>
						</div>

						<div class="col-span-2">
							<InputLabel class="mb-2" value="Kolor ikonek w ustawieniach" />
							<select v-model="form.setting_icon_color" class="w-full">
								<option v-for="color in ['info', 'warning', 'danger', 'brand', 'grey']" :value="color">
									{{ color }}
								</option>
							</select>
							<div v-if="form.errors.setting_icon_color" class="error">
								{{ form.errors.setting_icon_color }}
							</div>
						</div>

						<div class="col-span-2">
							<InputLabel value="Zdjęcie" />
							<ImageInput
								v-model="form.photo"
								placeholder="Kliknij, aby wybrać obraz"
								:error="form.errors.photo" />
						</div>

						<div class="col-span-2"></div>

						<div class="col-span-2"></div>

						<div class="col-span-2">
							<div class="flex h-full w-full items-end">
								<Button class="w-full" type="submit">
									{{ capitalize($t('main.action.update')) }}
								</Button>
							</div>
						</div>
					</form>
				</Card>
			</div>
		</ContentContainer>
	</PanelLayout>
</template>

<script lang="ts" setup>
import PanelLayout from '@/Layouts/PanelLayout.vue';
import ContentContainer from '@/Components/Dashboard/ContentContainer.vue';
import { useForm } from '@inertiajs/vue3';
import InputLabel from '@/Components/Auth/InputLabel.vue';
import TextInput from '@/Components/Dashboard/TextInput.vue';
import { useString } from '@/Composables/useString';
import Card from '@/Components/Dashboard/Card.vue';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import { Game } from '@/Types/models';
import ImageInput from '@/Components/Dashboard/ImageInput.vue';

const props = defineProps<{
	games: Game[];
}>();

const { capitalize } = useString();

const form = useForm({
	name: null,
	description: null,
	icon: null,
	setting_icon_color: 'info',
	photo: null,
});
</script>
