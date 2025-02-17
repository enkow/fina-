<template>
	<PanelLayout
		:breadcrumbs="[
			{ href: route('club.sets.index'), label: $t('set.plural') },
			{ href: route('club.sets.create'), label: $t('set.add-new-set') },
		]">
		<ContentContainer>
			<div class="col-span-12 xl:col-span-9">
				<Card>
					<template #header>
						<h2>{{ $t('set.add-new-set') }}</h2>
					</template>

					<form
						class="mt-9 grid grid-cols-4 gap-x-4 gap-y-5"
						@submit.prevent="
							form.post(route('club.sets.store'), {
								preserveScroll: true,
								preserveState: true,
							})
						">
						<div class="col-span-4 space-y-2">
							<InputLabel :value="capitalize($t('main.name'))" required />
							<TextInput v-model="form.name" />
							<div v-if="form.errors.name" class="error">
								{{ form.errors.name }}
							</div>
						</div>

						<div class="col-span-4 space-y-2 md:col-span-4">
							<InputLabel :value="capitalize($t('main.price'))" required />
							<AmountInput v-model="form.price" />
							<div v-if="form.errors.price" class="error">
								{{ form.errors.price }}
							</div>
						</div>
						<div class="col-span-4 space-y-2 md:col-span-2">
							<InputLabel :value="$t('set.photo')" required />
						 	<ImageInput
                                preview-width="176px"
                                preview-height="75px"
                                :disabled="
                                    !['admin', 'manager'].includes(
                                        usePage().props.user.type,
                                    )
                                "
                                v-model="form.photo"
                                :placeholder="$t('set.add-photo-placeholder')"
                                :error="form.errors.photo"
                                updated-url=""
                                aspectRatio="3 / 2"
                            />
						</div>
						<div class="col-span-4 space-y-2 md:col-span-2">
							<InputLabel :value="$t('set.mobile-photo')" required />
							 <ImageInput
                                preview-width="95px"
                                preview-height="95px"
                                :disabled="
                                    !['admin', 'manager'].includes(
                                        usePage().props.user.type,
                                    )
                                "
                                v-model="form.mobile_photo"
                                :placeholder="$t('set.add-photo-placeholder')"
                                :error="form.errors.mobile_photo"
                                updated-url=""
                                aspectRatio="3 / 2"
                            />
						</div>

						<div class="col-span-4 space-y-2">
							<InputLabel :value="capitalize($t('main.description'))" />
							<TextInput v-model="form.description" />
							<div v-if="form.errors.description" class="error">
								{{ form.errors.description }}
							</div>
						</div>
						<div class="col-span-4">
							<h2 class="!mb-9.5 !mt-8 text-[24px] font-medium">
								{{ $t('set.set-availability') }}
							</h2>
						</div>

						<div v-for="i in 7" class="col-span-4 mb-6 md:col-span-1">
							<InputLabel :value="$t(`main.week-day.${i}`)" class="mb-1 font-normal capitalize" />
							<TextInput v-model="form.quantity[i - 1]" :placeholder="0" />
							<div v-if="form.errors.quantity && form.errors.quantity[i - 1]" class="error">
								{{ form.errors.quantity[i - 1] }}
							</div>
						</div>

						<div class="col-span-4 mt-2 flex justify-end">
							<Button class="w-full md:w-1/4" type="submit">{{ capitalize($t('main.action.save')) }}</Button>
						</div>
					</form>
				</Card>
			</div>
			<ResponsiveHelper width="3">
				<template #mascot>
					<Mascot :type="16" />
				</template>
				<template #button>
					<Button
						:href="usePage().props.generalTranslations['help-sets-link']"
						class="grey xl:!px-0"
						type="link">
						{{ $t('help.learn-more') }}
					</Button>
				</template>
				{{ usePage().props.generalTranslations['help-sets-content'] }}
			</ResponsiveHelper>
		</ContentContainer>
	</PanelLayout>
</template>

<script lang="ts" setup>
import PanelLayout from '@/Layouts/PanelLayout.vue';
import Card from '@/Components/Dashboard/Card.vue';
import InputLabel from '@/Components/Dashboard/InputLabel.vue';
import TextInput from '@/Components/Dashboard/TextInput.vue';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import ContentContainer from '@/Components/Dashboard/ContentContainer.vue';
import ResponsiveHelper from '@/Components/Dashboard/Help/ResponsiveHelper.vue';
import { User } from '@/Types/models';
import { useForm, usePage } from '@inertiajs/vue3';
import { useString } from '@/Composables/useString';
import Mascot from '@/Components/Dashboard/Mascot.vue';
import dayjs from 'dayjs';
import AmountInput from '@/Components/Dashboard/AmountInput.vue';
import ImageInput from '@/Components/Dashboard/ImageInput.vue';

const { capitalize } = useString();

const props = defineProps<{
	user: User;
}>();

const form = useForm({
	name: null,
	photo: null,
	mobile_photo: null,
	description: null,
	price: null,
	quantity: [null, null, null, null, null, null, null],
});
</script>
