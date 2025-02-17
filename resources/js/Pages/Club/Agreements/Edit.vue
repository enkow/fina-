<template>
	<PanelLayout
		:breadcrumbs="[
			{ href: route('club.agreements.index'), label: $t('agreement.plural') },
			{
				href: route('club.agreements.edit', { agreement: agreement }),
				label: $t(`agreement.edit-title.${agreement.type}`),
			},
		]">
		<ContentContainer>
			<div class="col-span-12">
				<Card>
					<form
						class="grid grid-cols-2 gap-x-4 gap-y-5"
						@submit.prevent="
							form.post(
								route('club.agreements.update', {
									agreement: props.agreement,
								}),
							)
						">
						<div class="col-span-2 flex">
							<div class="w-1/2">
								<div class="mb-3 flex items-center space-x-2">
									<Radio
										id="content-type__file"
										name="content-type__file"
										v-model="form.content_type"
										:value="0"
										:checked="parseInt(form.content_type) === 0"
										:class="{ '!cursor-default': !panelStore.isUserRole(['admin', 'manager']) }"
										:disabled="!panelStore.isUserRole(['admin', 'manager'])" />
									<InputLabel :value="capitalize($t('main.file'))" for="content-type__file" />
								</div>
							</div>
							<div class="w-1/2">
								<div class="mb-3 flex items-center space-x-2">
									<Radio
										id="content-type__text"
										name="content-type__file"
										v-model="form.content_type"
										:value="1"
										:checked="parseInt(form.content_type) === 1"
										:class="{ '!cursor-default': !panelStore.isUserRole(['admin', 'manager']) }"
										:disabled="!panelStore.isUserRole(['admin', 'manager'])" />
									<InputLabel :value="capitalize($t('main.content'))" for="content-type__text" />
								</div>
							</div>
						</div>
						<div v-if="parseInt(form.content_type) === 0" id="content-type__file_wrapper" class="col-span-2">
							<div class="input-group">
								<InputLabel :value="capitalize($t('main.file'))" />
								<label
									class="flex h-12 w-full cursor-pointer items-center space-x-3 rounded-md border border-gray-3 pl-4"
									:class="{
										'!cursor-default bg-gray-1': !['admin', 'manager'].includes(usePage().props.user.type),
									}"
									for="file-upload">
									<svg
										fill="none"
										height="15"
										viewBox="0 0 13 15"
										width="13"
										xmlns="http://www.w3.org/2000/svg">
										<path
											clip-rule="evenodd"
											d="M0.546875 13.4529C0.546875 13.2275 0.636428 13.0113 0.795834 12.8519C0.95524 12.6925 1.17144 12.6029 1.39688 12.6029H11.5969C11.8223 12.6029 12.0385 12.6925 12.1979 12.8519C12.3573 13.0113 12.4469 13.2275 12.4469 13.4529C12.4469 13.6784 12.3573 13.8946 12.1979 14.054C12.0385 14.2134 11.8223 14.3029 11.5969 14.3029H1.39688C1.17144 14.3029 0.95524 14.2134 0.795834 14.054C0.636428 13.8946 0.546875 13.6784 0.546875 13.4529ZM3.34593 4.70389C3.18657 4.54449 3.09706 4.32833 3.09706 4.10294C3.09706 3.87755 3.18657 3.66139 3.34593 3.50199L5.89593 0.951993C6.05532 0.792643 6.27149 0.703125 6.49688 0.703125C6.72226 0.703125 6.93843 0.792643 7.09783 0.951993L9.64783 3.50199C9.80266 3.66231 9.88833 3.87702 9.8864 4.09988C9.88446 4.32275 9.79507 4.53594 9.63747 4.69354C9.47987 4.85114 9.26668 4.94053 9.04382 4.94247C8.82095 4.9444 8.60624 4.85873 8.44593 4.70389L7.34688 3.60484V10.0529C7.34688 10.2784 7.25732 10.4946 7.09792 10.654C6.93851 10.8134 6.72231 10.9029 6.49688 10.9029C6.27144 10.9029 6.05524 10.8134 5.89583 10.654C5.73643 10.4946 5.64688 10.2784 5.64688 10.0529V3.60484L4.54782 4.70389C4.38843 4.86324 4.17226 4.95276 3.94688 4.95276C3.72149 4.95276 3.50532 4.86324 3.34593 4.70389Z"
											fill="#9AA1B3"
											fill-rule="evenodd" />
									</svg>

									<p
										:class="{
											'text-gray-6': form.file,
											'text-gray-3': !form.file,
										}">
										{{ form.file?.name ?? $t('agreement.add-file-placeholder') }}
									</p>
								</label>
								<input
									id="file-upload"
									class="hidden"
									type="file"
									@input="form.file = $event.target.files[0]"
									:disabled="!panelStore.isUserRole(['admin', 'manager'])" />
								<div v-if="form.errors.file" class="error">
									{{ form.errors.file }}
								</div>
							</div>
							<div class="mt-3">
								<InputLabel :value="capitalize($t('main.current-file'))" />
								<div
									class="mt-2 flex h-12 w-full items-center justify-between rounded-md border border-gray-3 px-4"
									:class="{ 'bg-gray-1 text-gray-3': !panelStore.isUserRole(['admin', 'manager']) }">
									<a :href="`/club-assets/agreements/${agreement.file}`" target="_blank">
										{{ agreement.file ?? $t('agreement.no-file') }}
									</a>
								</div>
							</div>
						</div>
						<div v-else id="content-type__text_wrapper" class="col-span-2">
							<InputLabel
								:value="capitalize($t('main.content'))"
								:disabled="!panelStore.isUserRole(['admin', 'manager'])"
								:class="{ 'disabled-readable': !panelStore.isUserRole(['admin', 'manager']) }" />
							<TextareaInput v-model="form.text" :value="form.text" class="mt-2" />
						</div>

						<div class="col-span-1 space-y-2">
							<div class="flex items-center">
								<Checkbox
									id="active"
									v-model="form.active"
									:checked="form.active"
									:disabled="!panelStore.isUserRole(['admin', 'manager'])" />
								<InputLabel :value="capitalize($t('main.active'))" class="ml-3" for="active" />
							</div>

							<div class="flex items-center">
								<Checkbox
									id="required"
									v-model="form.required"
									:checked="form.required"
									:disabled="!panelStore.isUserRole(['admin', 'manager'])" />
								<InputLabel :value="capitalize($t('main.required'))" class="ml-3" for="required" />
							</div>
						</div>

						<Button
							class="lg col-span-2 md:col-span-1 md:col-start-2"
							v-if="panelStore.isUserRole(['admin', 'manager'])"
							type="submit">
							{{ capitalize($t('main.action.update')) }}
						</Button>
						<Button
							class="lg disabled col-span-2 md:col-span-1 md:col-start-2"
							v-tippy="{ allowHTML: true }"
							:content="'<p style=\'font-size:12px\'>' + $t('settings.no-permissions') + '</p>'"
							v-else>
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
import { useForm, usePage } from '@inertiajs/vue3';
import InputLabel from '@/Components/Dashboard/InputLabel.vue';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import { useString } from '@/Composables/useString';
import Checkbox from '@/Components/Dashboard/Checkbox.vue';
import { Agreement } from '@/Types/models';
import { wTrans } from 'laravel-vue-i18n';
import SimpleSelect from '@/Components/Dashboard/SimpleSelect.vue';
import { ref } from 'vue';
import TextareaInput from '@/Components/Dashboard/TextareaInput.vue';
import Radio from '@/Components/Dashboard/Radio.vue';
import { usePanelStore } from '@/Stores/panel';

const panelStore = usePanelStore();
const { capitalize } = useString();
const page = usePage();

const props = defineProps<{
	agreement: Agreement;
}>();

const form = useForm({
	active: props.agreement.active,
	content_type: props.agreement.content_type.toString(),
	required: props.agreement.required,
	text: props.agreement.text,
	type: props.agreement.type,
	file: null,
});

let typeOptions = ref<SimpleSelect[]>([]);
[0, 1].forEach((typeIndex) => {
	typeOptions.value.push({
		code: typeIndex,
		label: wTrans(`agreement.types.${typeIndex}`),
	});
});

function destroy() {
	form.file = 'delete';
}
</script>
