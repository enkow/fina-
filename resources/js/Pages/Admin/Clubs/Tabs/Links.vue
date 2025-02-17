<template>
	<div class="grid w-full grid-cols-6 gap-x-4 gap-y-5">
		<div class="input-group col-span-6">
			<InputLabel value="Link do kalendarza" />
			<div class="flex">
				<TextInput :value="linkToCalendar" class="!rounded-r-none" readonly />
				<Button
					v-clipboard:copy="linkToCalendar"
					class="info-light !rounded-l-none !border-r border-b border-t border-info-base/70 !px-7 uppercase"
					@click="
						toast($t('main.copied-to-the-clipboard'), {
							type: TYPE.INFO,
							position: POSITION.BOTTOM_RIGHT,
						})
					">
					<DocumentDuplicateIcon class="-mx-0.5 -mt-0.5" />
				</Button>
			</div>
			<InputLabel value="Link do widgetu" />
			<div class="flex">
				<TextInput :value="linkToWidget" class="!rounded-r-none" readonly />
				<Button
					v-clipboard:copy="linkToWidget"
					class="info-light !rounded-l-none !border-r border-b border-t border-info-base/70 !px-7 uppercase"
					@click="
						toast($t('main.copied-to-the-clipboard'), {
							type: TYPE.INFO,
							position: POSITION.BOTTOM_RIGHT,
						})
					">
					<DocumentDuplicateIcon class="-mx-0.5 -mt-0.5" />
				</Button>
			</div>
			<InputLabel value="Link do nowego widgetu" />
			<div class="flex">
				<TextInput :value="linkToNewWidget" class="!rounded-r-none" readonly />
				<Button
					v-clipboard:copy="linkToNewWidget"
					class="info-light !rounded-l-none !border-r border-b border-t border-info-base/70 !px-7 uppercase"
					@click="
						toast($t('main.copied-to-the-clipboard'), {
							type: TYPE.INFO,
							position: POSITION.BOTTOM_RIGHT,
						})
					">
					<DocumentDuplicateIcon class="-mx-0.5 -mt-0.5" />
				</Button>
			</div>
			<InputLabel value="Kod umieszczenia widgetu" />
			<div class="flex">
				<TextareaInput class="rounded-r-none" :value="iframeString" />
				<Button
					v-clipboard:copy="iframeString"
					class="info-light !h-20 !rounded-l-none !border-r border-b border-t border-info-base/70 !px-7 uppercase"
					@click="
						toast($t('main.copied-to-the-clipboard'), {
							type: TYPE.INFO,
							position: POSITION.BOTTOM_RIGHT,
						})
					">
					<DocumentDuplicateIcon class="-mx-0.5 -mt-0.5" />
				</Button>
			</div>
		</div>
	</div>
</template>

<script lang="ts" setup>
import { Club } from '@/Types/models';
import TextInput from '@/Components/Dashboard/TextInput.vue';
import InputLabel from '@/Components/Dashboard/InputLabel.vue';
import DocumentDuplicateIcon from '@/Components/Dashboard/Icons/DocumentDuplicateIcon.vue';
import { POSITION, TYPE, useToast } from 'vue-toastification';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import TextareaInput from '@/Components/Dashboard/TextareaInput.vue';

const props = defineProps<{
	club: Club;
}>();

const linkToWidget = route('widget.index', { slug: props.club.slug });
const linkToCalendar = route('widget.calendar', { slug: props.club.slug });
const linkToNewWidget = route('widget.new-widget', { slug: props.club.slug });

const iframeString =
	'<div style="position: relative; height: 670px; overflow: hidden; max-width: 100%;"><iframe src="' +
	linkToWidget +
	'" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0; outline: 0; overflow: hidden" scrolling="no" title="Bookgame widget" allowtransparency="true"></iframe> </div>';

const toast = useToast();
</script>
