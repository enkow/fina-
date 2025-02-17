<template>
    <PanelLayout
        :breadcrumbs="[
            { href: route('club.sets.index'), label: $t('set.plural') },
            {
                href: route('club.sets.edit', { set: set }),
                label: $t('set.edit-set') + ': ' + set.name,
            },
        ]"
    >
        <ContentContainer>
            <div class="col-span-12 xl:col-span-9">
                <Card>
                    <template #header>
                        <h2>{{ $t('set.edit-set') + ': ' + set.name }}</h2>
                    </template>

                    <form
                        class="mt-9 grid grid-cols-4 gap-x-4 gap-y-5"
                        @submit.prevent="
                            form.post(route('club.sets.update', { set: set }), {
                                preserveScroll: true,
                                preserveState: true,
                            })
                        "
                    >
                        <div class="col-span-4 space-y-2">
                            <InputLabel
                                :value="capitalize($t('main.name'))"
                                required
                            />
                            <TextInput
                                v-model="form.name"
                                :disabled="
                                    !['admin', 'manager'].includes(
                                        usePage().props.user.type,
                                    )
                                "
                                :class="{
                                    'disabled-readable': ![
                                        'admin',
                                        'manager',
                                    ].includes(usePage().props.user.type),
                                }"
                            />
                            <div v-if="form.errors.name" class="error">
                                {{ form.errors.name }}
                            </div>
                        </div>

                        <div class="col-span-4 space-y-2 md:col-span-4">
                            <InputLabel
                                :value="capitalize($t('main.price'))"
                                required
                            />
                            <AmountInput
                                v-model="form.price"
                                :disabled="
                                    !['admin', 'manager'].includes(
                                        usePage().props.user.type,
                                    )
                                "
                                :class="{
                                    'disabled-readable': ![
                                        'admin',
                                        'manager',
                                    ].includes(usePage().props.user.type),
                                }"
                            />
                            <div v-if="form.errors.price" class="error">
                                {{ form.errors.price }}
                            </div>
                        </div>
                        <div class="col-span-4 space-y-2 md:col-span-2">
                            <InputLabel :value="$t('set.photo')" />
                            <ImageInput
                                aspectRatio="3 / 2"
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
                                :updated-url="
                                    set.photo
                                        ? `/images/set-images/${set.photo}`
                                        : ''
                                "
                            />
                        </div>
                        <div class="col-span-4 space-y-2 md:col-span-2">
                            <InputLabel :value="$t('set.mobile-photo')" />
                            <ImageInput
                                aspectRatio="3 / 2"
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
                                :updated-url="
                                    set.mobile_photo
                                        ? `/images/set-images/${set.mobile_photo}`
                                        : ''
                                "
                            />
                        </div>
                        <div class="col-span-4 space-y-2">
                            <InputLabel
                                :value="capitalize($t('main.description'))"
                            />
                            <TextInput
                                v-model="form.description"
                                :disabled="
                                    !['admin', 'manager'].includes(
                                        usePage().props.user.type,
                                    )
                                "
                                :class="{
                                    'disabled-readable': ![
                                        'admin',
                                        'manager',
                                    ].includes(usePage().props.user.type),
                                }"
                            />
                            <div v-if="form.errors.description" class="error">
                                {{ form.errors.description }}
                            </div>
                        </div>
                        <div class="col-span-4">
                            <h2 class="!mb-9.5 !mt-8 text-[24px] font-medium">
                                {{ $t('set.set-availability') }}
                            </h2>
                        </div>

                        <div
                            v-for="i in 7"
                            class="col-span-4 mb-6 md:col-span-1"
                        >
                            <InputLabel
                                :value="$t(`main.week-day.${i}`)"
                                class="mb-1 font-normal capitalize"
                            />
                            <TextInput
                                v-model="form.quantity[i - 1]"
                                :placeholder="0"
                                :disabled="
                                    !['admin', 'manager'].includes(
                                        usePage().props.user.type,
                                    )
                                "
                                :class="{
                                    'disabled-readable': ![
                                        'admin',
                                        'manager',
                                    ].includes(usePage().props.user.type),
                                }"
                            />
                            <div
                                v-if="
                                    form.errors.quantity &&
                                    form.errors.quantity[i - 1]
                                "
                                class="error"
                            >
                                {{ form.errors.quantity[i - 1] }}
                            </div>
                        </div>

                        <div class="col-span-4 mt-2 flex justify-end">
                            <Button
                                class="w-full md:w-1/4"
                                type="submit"
                                v-if="
                                    ['admin', 'manager'].includes(
                                        usePage().props.user.type,
                                    )
                                "
                            >
                                {{ $t('set.save-set') }}
                            </Button>
                            <Button
                                class="disabled w-full md:w-1/4"
                                v-tippy="{ allowHTML: true }"
                                :content="
                                    '<p style=\'font-size:12px\'>' +
                                    $t('settings.no-permissions') +
                                    '</p>'
                                "
                                v-else
                            >
                                {{ $t('set.save-set') }}
                            </Button>
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
                        :href="
                            usePage().props.generalTranslations[
                                'help-sets-link'
                            ]
                        "
                        class="grey xl:!px-0"
                        type="link"
                    >
                        {{ $t('help.learn-more') }}
                    </Button>
                </template>
                {{ usePage().props.generalTranslations['help-sets-content'] }}
            </ResponsiveHelper>
        </ContentContainer>
    </PanelLayout>
</template>

<script lang="ts" setup>
import { useForm, usePage } from '@inertiajs/vue3';
import Card from '@/Components/Dashboard/Card.vue';
import ContentContainer from '@/Components/Dashboard/ContentContainer.vue';
import PanelLayout from '@/Layouts/PanelLayout.vue';
import InputLabel from '@/Components/Dashboard/InputLabel.vue';
import ImageInput from '@/Components/Dashboard/ImageInput.vue';
import TextInput from '@/Components/Dashboard/TextInput.vue';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import ResponsiveHelper from '@/Components/Dashboard/Help/ResponsiveHelper.vue';
import { useString } from '@/Composables/useString';
import { SetModel } from '@/Types/models';
import Mascot from '@/Components/Dashboard/Mascot.vue';
import AmountInput from '@/Components/Dashboard/AmountInput.vue';

const props = defineProps<{
    set: SetModel;
}>();
const { capitalize } = useString();

const form = useForm({
    name: props.set.name,
    photo: null,
    mobile_photo: null,
    description: props.set.description,
    price: (props.set.price / 100).toFixed(2),
    quantity: props.set.quantity,
});
</script>
