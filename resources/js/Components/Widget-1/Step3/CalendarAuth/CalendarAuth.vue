<template>
  <CalendarSectionTitle>
    <slot name="title" :selectedTabIndex="selectedTabIndex">
      {{ $t("calendar.sign-in-to-pay") }}
    </slot>
  </CalendarSectionTitle>
  <CalendarTabs
    v-model="selectedTabIndex"
    :tabs="[$t('calendar.sign-in'), $t('calendar.registration')]"
  >
    <TabPanel>
      <CalendarSignInForm
        input-color="white"
        @forgot-password-click="isForgotPasswordModalOpen = true"
      />
      <CalendarForgotPasswordModal
        :is-open="isForgotPasswordModalOpen"
        @close="isForgotPasswordModalOpen = false"
      />
    </TabPanel>
    <TabPanel>
      <CalendarSignUpForm />
    </TabPanel>
  </CalendarTabs>
</template>

<script lang="ts" setup>
import { TabPanel } from "@headlessui/vue";
import CalendarTabs from "@/Components/Widget-1/Shared/CalendarTabs.vue";
import CalendarSignInForm from "@/Components/Widget-1/Shared/CalendarSignInForm.vue";
import CalendarSignUpForm from "./CalendarSignUpForm.vue";
import CalendarSectionTitle from "../CalendarSectionTitle.vue";
import CalendarForgotPasswordModal from "@/Components/Widget-1/Shared/CalendarForgotPasswordModal.vue";
import { ref } from "vue";

const isForgotPasswordModalOpen = ref(false);
const selectedTabIndex = ref(0);
</script>
