<template>
	<PanelLayout
		:breadcrumbs="[
			{
				href: route('club.discount-codes.index'),
				label: $t('discount-code.plural'),
			},
			{
				href: route('club.discount-codes.create'),
				label: $t('discount-code.add-discount-code'),
			},
		]">
		<ContentContainer>
			<div class="col-span-12 xl:col-span-9">
				<Card>
					<template #header>
						<h2>{{ $t('discount-code.add-discount-code') }}</h2>
					</template>

					<form
						class="mt-9 grid grid-cols-4 gap-x-4 gap-y-5"
						@submit.prevent="form.post(route('club.discount-codes.store'))">
						<div class="input-group col-span-4 md:col-span-1">
							<InputLabel :value="capitalize($t('discount-code.code-name'))" required />
							<TextInput v-model="form.code" :placeholder="$t('discount-code.enter-name')" />
							<div v-if="form.errors.code" class="error">
								{{ form.errors.code }}
							</div>
						</div>

						<div class="input-group col-span-4 md:col-span-1">
							<InputLabel :value="capitalize($t('main.game-type'))" required />
							<SimpleSelect v-model="form.game_id" :options="userGameOptions" />
							<div v-if="form.errors.game_id" class="error">
								{{ form.errors.game_id }}
							</div>
						</div>

						<div class="input-group col-span-4 md:col-span-2">
							<InputLabel :value="capitalize($t('main.discount'))" required />
							<div class="flex w-full">
								<div class="input-group flex-grow">
									<TextInput
										v-model="form.value"
										:placeholder="$t('discount-code.enter-discount')"
										class="rounded-r-none" />
									<div v-if="form.errors.value" class="error">
										{{ form.errors.value }}
									</div>
									<div v-if="form.errors.type" class="error">
										{{ form.errors.type }}
									</div>
								</div>
								<div
									:style="{
										width: discountCodeTypeOptionsSelectWidth + 'px',
									}">
									<SimpleSelect v-model="form.type" :options="discountCodeTypeOptions" class="input-append" />
								</div>
							</div>
						</div>

						<div class="input-group col-span-4 md:col-span-2">
							<InputLabel :value="capitalize($t('discount-code.code-quantity'))" />
							<TextInput v-model="form.code_quantity" :placeholder="$t('discount-code.enter-quantity')" />
							<div v-if="form.errors.code_quantity" class="error">
								{{ form.errors.code_quantity }}
							</div>
						</div>

						<div class="input-group col-span-4 md:col-span-2" v-if="shouldShowCodePerUser">
							<InputLabel :value="capitalize($t('discount-code.code-quantity-user'))" />
							<TextInput
								v-model="form.code_quantity_per_customer"
								:placeholder="$t('discount-code.enter-quantity')" />
							<div v-if="form.errors.code_quantity_per_customer" class="error">
								{{ form.errors.code_quantity_per_customer }}
							</div>
						</div>

						<div class="input-group col-span-4 md:col-span-2">
							<InputLabel :value="capitalize($t('main.start'))" />
							<Datetimepicker
								v-model="form.start_at"
								:input-with-icon="true"
								:placeholder="capitalize($t('main.action.choose'))" />
							<div v-if="form.errors.start_at" class="error">
								{{ form.errors.start_at }}
							</div>
						</div>

						<div class="input-group col-span-4 md:col-span-2">
							<InputLabel :value="capitalize($t('discount-code.expires'))" />
							<Datetimepicker
								v-model="form.end_at"
								:input-with-icon="true"
								:placeholder="capitalize($t('main.action.choose'))" />
							<div v-if="form.errors.end_at" class="error">
								{{ form.errors.end_at }}
							</div>
						</div>

						<div class="col-span-4 flex items-center justify-end">
							<Checkbox id="setAsActive" v-model="form.active" :checked="form.active" />
							<InputLabel :value="$t('discount-code.set-as-active')" class="ml-3" for="setAsActive" />
						</div>

						<div class="col-span-4 mt-5 flex justify-end">
							<Button :disabled="form.processing" class="lg !w-64 !px-0" type="submit">
								{{ $t('main.action.save') }}
							</Button>
						</div>
					</form>
				</Card>
			</div>

			<ResponsiveHelper width="3">
				<template #mascot>
					<Mascot :type="19" />
				</template>
				<template #button>
					<Button
						:href="usePage().props.generalTranslations['help-discount-codes-link']"
						class="grey xl:!px-0"
						type="link">
						{{ $t('help.learn-more') }}
					</Button>
				</template>
				{{ usePage().props.generalTranslations['help-discount-codes-content'] }}
			</ResponsiveHelper>
		</ContentContainer>
	</PanelLayout>
</template>

<script lang="ts" setup>
import PanelLayout from '@/Layouts/PanelLayout.vue';
import Card from '@/Components/Dashboard/Card.vue';
import InputLabel from '@/Components/Dashboard/InputLabel.vue';
import TextInput from '@/Components/Dashboard/TextInput.vue';
import SimpleSelect from '@/Components/Dashboard/SimpleSelect.vue';
import Checkbox from '@/Components/Auth/Checkbox.vue';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import ContentContainer from '@/Components/Dashboard/ContentContainer.vue';
import ResponsiveHelper from '@/Components/Dashboard/Help/ResponsiveHelper.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import Datetimepicker from '@/Components/Dashboard/Datetimepicker.vue';
import { useString } from '@/Composables/useString';
import { useSelectOptions } from '@/Composables/useSelectOptions';
import { SelectOption } from '@/Types/models';
import Mascot from '@/Components/Dashboard/Mascot.vue';
import { computed } from 'vue';

const { capitalize } = useString();
const { gameOptions, discountCodeTypeOptions } = useSelectOptions();


const discountCodeTypeOptionsSelectWidth = computed<number>(() => {
	let maxCharacters = 0;
	discountCodeTypeOptions.value.forEach((discountCodeTypeOption) => {
		if (maxCharacters < discountCodeTypeOption.label.length) {
			maxCharacters = discountCodeTypeOption.label.length;
		}
	});
	return maxCharacters * 13 + 50;
});

const userGameOptions: Array<SelectOption> = gameOptions();

const props = defineProps<{
  shouldShowCodePerUser: Boolean;
}>();

const form = useForm({
	active: true,
	game_id: userGameOptions[0].code,
	type: discountCodeTypeOptions.value[0].code,
	code: Math.random().toString(36).substr(2, 6).toUpperCase(),
	value: null,
	code_quantity: null,
	code_quantity_per_customer: null,
	start_at: null,
	end_at: null,
});
</script>
