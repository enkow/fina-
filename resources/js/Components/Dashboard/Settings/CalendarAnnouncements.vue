<template>
    <AccordionTab
        key="calendar_announcements"
        :class="`icon-${settingIconColor}`"
        :with-border="withBorder"
    >
        <template #icon>
            <CalendarIcon />
        </template>

        <template #title>
            {{ featureTranslations['setting-title'] }}
        </template>

        {{ featureTranslations['setting-description'] }}
        <Link
            v-if="featureTranslations['setting-see-mode-link']"
            :href="route('club.help-sections.index')"
            class="mt-1 block w-full font-bold text-brand-base underline"
        >
            {{ featureTranslations['setting-see-mode-content'] }}
        </Link>

        <div class="mt-5 w-full">
            <div
                v-for="announcement in announcements"
                :key="'comment' + announcement.id"
                class="w-full justify-between pb-4 pt-2"
            >
                <div class="flex w-full justify-between">
                    <div class="pr-20 text-gray-7/60">
                        <p class="font-semibold">
                            {{ announcement.start_at }} -
                            {{ announcement.end_at }}
                        </p>
                        <p>{{ announcement.content_top }}</p>
                        <p>{{ announcement.content_bottom }}</p>
                    </div>
                    <div class="text-danger-base">
                        <Link
                            :href="
                                route('club.announcements.destroy', {
                                    announcement: announcement,
                                })
                            "
                            as="button"
                            class="flex items-center text-xxs font-semibold"
                            method="delete"
                            preserve-scroll
                            preserve-state
                        >
                            <p class="capitalize">
                                {{ $t('main.action.delete') }}
                            </p>
                            <TrashIcon class="ml-1.5 h-3 w-3" />
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <form
            @submit.prevent="
                form.post(route('club.announcements.store'), {
                    preserveScroll: true,
                    preserveState: true,
                })
            "
        >
            <div class="mt-5 space-y-4">
                <div class="space-y-2">
                    <InputLabel class="text-xs capitalize">{{
                        capitalize($t('main.date'))
                    }}</InputLabel>
                    <RangeDatepicker
                        :only-future-dates="true"
                        v-model="form.date_range"
                        :disabled="!hasPermission"
                        input-with-icon
                        position="top"
                        no-disabled-range
                        :disabledDates="
                            (date: Date): boolean => {
                                const result = props.announcements.some(
                                    (announcement) => {
                                        const startAt = new Date(
                                            announcement?.start_at || '',
                                        );
                                        const endAt = new Date(
                                            announcement?.end_at || '',
                                        );

                                        if (date >= startAt && date <= endAt) {
                                            return true;
                                        }
                                    },
                                );

                                return result;
                            }
                        "
                    />
                    <div v-if="!validDataError" class="error">
                        {{
                            $t(
                                'announcement.validation.two-announcement-the-same-day',
                            )
                        }}
                    </div>
                    <div
                        v-if="form.errors.start_at || form.errors.end_at"
                        class="error"
                    >
                        {{ form.errors.start_at }}
                        <br />
                        {{ form.errors.end_at }}
                    </div>
                </div>
                <div class="space-y-2">
                    <InputLabel class="text-xs capitalize">
                        {{ $t('announcement.announcement-content-top') }}
                    </InputLabel>
                    <TextareaInput v-model="form.content_top" />
                    <div v-if="form.errors.content_top" class="error">
                        {{ form.errors.content_top }}
                    </div>
                </div>
                <div class="space-y-2">
                    <InputLabel class="text-xs capitalize">
                        {{ $t('announcement.announcement-content-bottom') }}
                    </InputLabel>
                    <TextareaInput v-model="form.content_bottom" />
                    <div v-if="form.errors.content_bottom" class="error">
                        {{ form.errors.content_bottom }}
                    </div>
                </div>
            </div>

            <div class="accordion-footer">
                <div class="hidden w-1/2 space-y-2 sm:block"></div>
                <div class="w-full sm:w-1/2">
                    <Button
                        class="lg accordion-footer__submit !h-11.5"
                        type="submit"
                    >
                        {{ $t('main.action.update') }}
                    </Button>
                </div>
            </div>
        </form>
    </AccordionTab>
</template>
<script lang="ts" setup>
import AccordionTab from '@/Components/Dashboard/AccordionTab.vue';
import CalendarIcon from '@/Components/Dashboard/Icons/CalendarIcon.vue';
import TrashIcon from '@/Components/Dashboard/Icons/TrashIcon.vue';
import InputLabel from '@/Components/Dashboard/InputLabel.vue';
import TextareaInput from '@/Components/Dashboard/TextareaInput.vue';
import { Link, useForm } from '@inertiajs/vue3';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import SimpleDatepicker from '@/Components/Dashboard/SimpleDatepicker.vue';
import { useString } from '@/Composables/useString';
import { Announcement, Game } from '@/Types/models';
import RangeDatepicker from '@/Components/Dashboard/RangeDatepicker.vue';
import { ref, watch } from 'vue';
import { usePanelStore } from '@/Stores/panel';
import dayjs from 'dayjs';
const { page } = usePanelStore();
const hasPermission = ['admin', 'manager'].includes(
    page.props.user.type as string,
);
let validDataError = ref(true);

const props = withDefaults(
    defineProps<{
        game: Game;
        announcements: Announcement[];
        settingIconColor?: string;
        withBorder?: boolean;
    }>(),
    {
        settingIconColor: 'info',
    },
);

const { capitalize } = useString();

const form = useForm({
    game_id: props.game.id,
    type: 2,
    date_range: null,
    start_at: null,
    end_at: null,
    content_top: null,
    content_bottom: null,
});

const formatDate = (date: String | undefined) => {
    if (date) {
        return dayjs(date.toString()).format('YYYY-MM-DD');
    }
    return null;
};

const featureTranslations = props.game.features.filter(
    (item) => item.type === 'has_calendar_announcements_setting',
)[0].translations;

watch(form, (newValue) => {
    if (form.date_range) {
        form.start_at = formatDate(form.date_range[0]?.toString());
        form.end_at = formatDate(form.date_range[1]?.toString());
    }
});
</script>
