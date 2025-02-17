<template>
    <header class="mb-13 flex h-10 items-center justify-between">
        <ClubNameButton />
        <div class="flex gap-x-3 sm:gap-x-7">
            <LocaleDropdown />

            <template v-if="isRegistrationRequired">
                <SignOutButton v-if="isAuth" />
                <SignInButton v-else />
            </template>
        </div>
    </header>
</template>

<script lang="ts" setup>
import LocaleDropdown from './LocaleDropdown.vue';
import SignInButton from './SignInButton.vue';
import SignOutButton from './SignOutButton.vue';
import ClubNameButton from './ClubNameButton.vue';
import { useAuth } from '@/Composables/useAuth';
import { useWidgetRegistrationRequired } from '@/Composables/widget/useWidgetRegistrationRequired';

const { isAuth } = useAuth();
const { isRegistrationRequired } = useWidgetRegistrationRequired();
</script>
