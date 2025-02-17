<template>
	<PanelLayout
		:breadcrumbs="[
			{ href: route('club.customers.index'), label: $t('customer.plural') },
			{ href: route('club.customers.create'), label: $t('customer.add-new') },
		]">
		<ContentContainer>
			<div class="col-span-12">
				<Card>
					<form
						class="grid grid-cols-2 gap-x-4 gap-y-5"
						@submit.prevent="form.post(route('club.customers.store'))">
						<div class="input-group col-span-2 md:col-span-1">
							<InputLabel :value="capitalize($t('main.first-name'))" required />
							<TextInput v-model="form.first_name" />
							<div v-if="form.errors.first_name" class="error">
								{{ form.errors.first_name }}
							</div>
						</div>

						<div class="input-group col-span-2 md:col-span-1">
							<InputLabel :value="capitalize($t('main.last-name'))" />
							<TextInput v-model="form.last_name" />
							<div v-if="form.errors.last_name" class="error">
								{{ form.errors.last_name }}
							</div>
						</div>

						<div class="input-group col-span-2 md:col-span-1">
							<InputLabel :value="capitalize($t('main.email'))" />
							<TextInput v-model="form.email" />
							<div v-if="form.errors.email" class="error">
								{{ form.errors.email }}
							</div>
						</div>

						<div class="input-group col-span-2 md:col-span-1">
							<InputLabel :value="capitalize($t('main.phone'))" required />
							<TextInput v-model="form.phone" />
							<div v-if="form.errors.phone" class="error">
								{{ form.errors.phone }}
							</div>
						</div>

						<div class="input-group col-span-2 md:col-span-1">
							<InputLabel :value="capitalize($t('tag.plural'))" />
							<Tagify
								:onChange="updateTags"
								:settings="{
									whitelist: availableTags.map(function (value, index) {
										return value['name'];
									}),
									maxTags: 15,
									dropdown: {
										maxItems: 20,
										classname: 'customer-tags',
										enabled: 0,
										closeOnSelect: true,
									},
								}"
								:value="form.tags"
								mode="text" />
							<div v-if="form.errors.tags" class="error">
								{{ form.errors.tags }}
							</div>
						</div>
						<div class="col-span-1 mb-0.5 flex flex-col justify-end space-y-1">
							<div v-for="agreement in agreements" class="flex space-x-3">
								<Checkbox
									v-model="form.agreements[agreement.type]"
									:checked="form.agreements[agreement.type]"
									:id="'agreement-' + agreement.type" />
								<InputLabel
									:value="$t('agreement.types.' + agreement.type)"
									:for="'agreement-' + agreement.type" />
							</div>
						</div>

						<Button class="lg col-span-2 md:col-span-1 md:col-start-2" type="submit">
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
import Card from '@/Components/Dashboard/Card.vue';
import { router, useForm } from '@inertiajs/vue3';
import InputLabel from '@/Components/Dashboard/InputLabel.vue';
import TextInput from '@/Components/Dashboard/TextInput.vue';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import { useString } from '@/Composables/useString';
import { Agreement, Tag } from '@/Types/models';
import Checkbox from '@/Components/Dashboard/Checkbox.vue';
import Tagify from '@/Components/Dashboard/Tagify.vue';

const { capitalize } = useString();

const props = defineProps<{
	agreements: Agreement[];
	availableTags: Tag[];
}>();

const formDictionary = {
	email: null,
	first_name: null,
	last_name: null,
	phone: null,
	agreements: {},
	tags: null,
};

function updateTags(e: any): void {
	form.tags = e.target?.value;
}

props.agreements.forEach((agreement) => {
	formDictionary.agreements[agreement.type] = false;
});

const form = useForm(formDictionary);
</script>
