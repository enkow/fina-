<template>
  <button
    type="button"
    @click="isSignInModalOpen = true"
    class="flex gap-x-3 text-ui-black hover:text-ui-green sm:w-33 transition-colors"
  >
    <span class="hidden sm:inline">{{ $t("calendar.sign-in") }}</span>
    <LoginIcon />
  </button>
  <SignInModal
    :is-open="isSignInModalOpen"
    @close="isSignInModalOpen = false"
    @forgot-password-click="
      () => {
        isSignInModalOpen = false;
        isForgotModalPasswordOpen = true;
      }
    "
  />
  <CalendarForgotPasswordModal
    :is-open="isForgotModalPasswordOpen"
    @close="
      () => {
        isForgotModalPasswordOpen = false;
        isSignInModalOpen = true;
      }
    "
  />
</template>

<script lang="ts" setup>
import { ref } from "vue";
import SignInModal from "../SignInModal/SignInModal.vue";
import LoginIcon from "@/Components/Widget-1/Icons/LoginIcon.vue";
import CalendarForgotPasswordModal from "@/Components/Widget-1/Shared/CalendarForgotPasswordModal.vue";

const isSignInModalOpen = ref(false);
const isForgotModalPasswordOpen = ref(false);
</script>
