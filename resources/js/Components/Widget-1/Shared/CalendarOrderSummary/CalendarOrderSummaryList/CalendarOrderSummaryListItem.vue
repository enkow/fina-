<template>
  <li class="space-y-1">
    <p class="leading-5 text-ui-black/60 capitalize">{{ title }}</p>
    <p class="flex items-center gap-x-2.5 break-words text-lg font-bold text-ui-black">
      <slot name="icon" />
      {{ value }}
    </p>
  </li>
</template>

<script lang="ts" setup>
import { computed } from 'vue';
const props = defineProps<{
  name: string;
  value: string;
  singleTitle?: string;
  isSingular?: boolean;
}>();

const title = computed(() => {
  if (props.isSingular && props.singleTitle) {
    return props.singleTitle;
  }

  return props.name;
});
</script>
