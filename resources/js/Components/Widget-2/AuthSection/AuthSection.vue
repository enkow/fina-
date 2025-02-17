<template>
	<section class="flex h-full flex-col md:grid md:grid-cols-3 md:gap-x-5">
		<TabGroup v-slot="{ selectedIndex }">
			<div>
				<SectionTitle class="hidden md:block">{{ $t('widget-2.sign-in-to-pay') }}</SectionTitle>
				<TabList class="flex md:flex-col">
					<Tab as="template" v-for="tab in tabs" v-slot="{ selected }">
						<AuthSectionTab :name="tab.value" :selected="selected" />
					</Tab>
				</TabList>
			</div>
			<div class="widget-2-scrollbar flex-1 overflow-auto md:col-span-2 md:overflow-visible md:!pr-0">
				<div
					:class="
						twMerge(
							'rounded-2xl bg-ui-green-100 p-5 md:h-full',
							selectedIndex === 0 && 'rounded-tl-none md:rounded-tl-2xl',
						)
					">
					<AuthSectionForgotPasswordContent
						v-if="showForgotPassword"
						@back-click="showForgotPassword = false" />
					<TabPanels class="h-full" v-else>
						<TabPanel class="h-full" v-for="panel in panels">
							<component :is="panel" @forgot-password-click="showForgotPassword = true" />
						</TabPanel>
					</TabPanels>
				</div>
			</div>
		</TabGroup>
	</section>
</template>

<script lang="ts" setup>
import SectionTitle from '@/Components/Widget-2/SectionTitle.vue';
import AuthSectionTab from './AuthSectionTab.vue';
import AuthSectionSignInContent from './AuthSectionSignInContent.vue';
import AuthSectionSignUpContent from './AuthSectionSignUpContent.vue';
import AuthSectionForgotPasswordContent from './AuthSectionForgotPasswordContent.vue';
import { TabGroup, TabList, Tab, TabPanels, TabPanel } from '@headlessui/vue';
import { computed, ref } from 'vue';
import { twMerge } from 'tailwind-merge';
import { wTrans } from 'laravel-vue-i18n';

const tabs = computed(() => [wTrans('widget-2.sign-in'), wTrans('widget-2.sign-up')]);
const panels = [AuthSectionSignInContent, AuthSectionSignUpContent];

const showForgotPassword = ref(false);
</script>
