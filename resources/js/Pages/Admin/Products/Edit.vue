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
						<h2>Edytuj produkt</h2>
					</template>

					<form
						class="grid grid-cols-4 gap-x-4 gap-y-5"
						@submit.prevent="form.put(route('admin.products.update', { product: product }))">
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

						<Button class="col-span-2 md:col-span-1 md:col-start-4" type="submit">
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
import { useForm } from '@inertiajs/vue3';
import Card from '@/Components/Dashboard/Card.vue';
import { useString } from '@/Composables/useString';
import InputLabel from '@/Components/Dashboard/InputLabel.vue';
import TextInput from '@/Components/Dashboard/TextInput.vue';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import { Product } from '@/Types/models';

const { capitalize } = useString();

const props = defineProps<{
	product: Product;
}>();

const form = useForm({
	name_pl: props.product.name_pl,
	name_en: props.product.name_en,
});
</script>
