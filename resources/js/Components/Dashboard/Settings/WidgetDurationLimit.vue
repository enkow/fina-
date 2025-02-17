<template>
  <WeekDayNumbers
    :label="setting.translations['setting-label']"
    :setting="setting"
    :setting-icon-color="settingIconColor"
    :name="name"
    :placeholder="$t('slot.all')"
    :with-border="withBorder"
  >
    <template #icon>
      <ClockIcon />
    </template>

    <template #title>{{
      setting.feature.translations["setting-title"]
    }}</template>

    {{ setting.feature.translations["setting-description"] }}
  </WeekDayNumbers>
</template>
<script lang="ts" setup>
import HashIcon from "@/Components/Dashboard/Icons/HashIcon.vue";
import WeekDayNumbers from "@/Components/Dashboard/Settings/Types/WeekDayNumbers.vue";
import { SettingEntity } from "@/Types/responses";
import { useString } from "@/Composables/useString";
import ClockIcon from "@/Components/Dashboard/Icons/ClockIcon.vue";

const props = withDefaults(
  defineProps<{
    setting: SettingEntity;
    scope?: string;
    customRoute?: string;
    settingIconColor?: string;
    withBorder?: boolean;
    name?: string;
  }>(),
  {
    scope: "club",
    settingIconColor: "info",
    name: "widget_duration_limit",
  }
);
const { capitalize } = useString();
</script>
