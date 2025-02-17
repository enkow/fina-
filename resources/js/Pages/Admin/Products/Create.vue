<template>
	<PanelLayout
		:breadcrumbs="[
			{ href: route('admin.products.index'), label: 'Produkty' },
			{ href: route('admin.products.create'), label: 'Utwórz nowy produkt' },
		]">
		<ContentContainer>
			<div class="col-span-12">
				<Card>
					<template #header>
						<h2>Utwórz nowy produkt</h2>
					</template>
					<form
						class="grid grid-cols-4 gap-x-4 gap-y-5"
						@submit.prevent="form.post(route('admin.products.store'))">
						<div class="input-group col-span-4 md:col-span-2">
							<InputLabel value="Nazwa polska" />
							<TextInput v-model="form.name_pl" />
							<div v-if="form.errors.name_pl" class="error">
								{{ form.errors.name_pl }}
							</div>
						</div>

						<div class="input-group col-span-4 md:col-span-2">
							<InputLabel value="Nazwa angielska" />
							<TextInput v-model="form.name_en" />
							<div v-if="form.errors.name_en" class="error">
								{{ form.errors.name_en }}
							</div>
						</div>

						<Button type="submit">
							{{ capitalize($t('main.action.save')) }}
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
import { useForm } from '@inertiajs/vue3';
import Card from '@/Components/Dashboard/Card.vue';
import { useString } from '@/Composables/useString';
import InputLabel from '@/Components/Dashboard/InputLabel.vue';
import TextInput from '@/Components/Dashboard/TextInput.vue';
import Button from '@/Components/Dashboard/Buttons/Button.vue';

const { capitalize } = useString();

const form = useForm({
	name_pl: null,
	name_en: null,
});
</script>
