<template>
	<section
		ref="panelRef"
		:class="{
			'vcp--expanded': isExpanded,
			'vcp--expandable': body.hasContent,
		}"
		class="vcp">
		<header class="vcp__header" @click="toggle">
			<div class="vcp__header-title">
				<slot name="title" />
			</div>
			<div v-if="body.hasContent" class="vcp__header-icon">
				<slot name="icon">
					<span v-html="toggleIcon" />
				</slot>
			</div>
		</header>

		<transition
			:data-key="body.dataKey"
			name="slide"
			@enter="expand"
			@leave="collapse"
			@before-enter="collapse"
			@before-leave="expand">
			<div v-if="isExpanded" ref="bodyRef" class="vcp__body">
				<div ref="bodyContentRef" class="vcp__body-content">
					<slot name="content" />
				</div>
			</div>
		</transition>
	</section>
</template>

<script lang="ts">
import { v4 as uuid } from 'uuid';
import { computed, defineComponent, nextTick, onMounted, onUpdated, ref } from 'vue';
import { VNodeArrayChildren } from '@vue/runtime-core';
import { useCollapsiblePanelStore } from '@/Composables/useCollapsiblePanelStore';

const toggleIcon = `
    <svg
        width="24px"
        height="24px"
        viewBox="0 0 24 24"
        fill="currentColor"
        xmlns="http://www.w3.org/2000/svg"
      >
        <path d="M6.34292 7.75734L4.92871 9.17155L11.9998 16.2426L19.0708 9.17158L17.6566 7.75737L11.9998 13.4142L6.34292 7.75734Z"/>
    </svg>
`;

export default defineComponent({
	name: 'VueCollapsiblePanel',
	props: {
		expanded: {
			type: Boolean,
			default: true,
		},
	},
	setup(props, context) {
		const idPanel = `panel-${uuid()}`;
		const panelRef = ref<HTMLElement>();
		const bodyRef = ref<HTMLElement>();
		const bodyContentRef = ref<HTMLElement>();
		const { panelExpanded, togglePanelExpandedStatus, setPanelExpandedStatus } = useCollapsiblePanelStore();

		const body = computed(() => ({
			hasContent:
				context.slots.content && (context.slots.content()[0].children as VNodeArrayChildren).length > 0,
			dataKey: uuid(),
		}));

		const idGroup = computed(() => {
			return panelRef.value?.parentElement?.getAttribute('data-id-group') || '';
		});

		const isExpanded = computed(() => panelExpanded(idGroup.value, idPanel).value && body.value.hasContent);

		const toggle = (): void => {
			if (!body.value.hasContent) return;

			togglePanelExpandedStatus(idGroup.value, idPanel);
		};

		const collapse = (element: HTMLElement): void => {
			element.style.height = '0';
		};

		const expand = (element: HTMLElement): void => {
			element.style.height = `${element.scrollHeight}px`;
		};

		const updateBodyHeight = async (): Promise<void> => {
			await nextTick();

			if (!bodyRef.value || !bodyContentRef.value) return;

			bodyRef.value.style.height = `${Math.min(
				bodyContentRef.value.scrollHeight,
				bodyRef.value.scrollHeight,
			)}px`;
		};

		onMounted(() => {
			setPanelExpandedStatus(idGroup.value, idPanel, props.expanded);
		});

		onUpdated(() => {
			updateBodyHeight();
		});

		context.expose({ updateBodyHeight });

		return {
			body,
			panelRef,
			bodyRef,
			bodyContentRef,
			isExpanded,
			collapse,
			expand,
			toggle,
			toggleIcon,
		};
	},
});
</script>

<style lang="scss" scoped></style>
