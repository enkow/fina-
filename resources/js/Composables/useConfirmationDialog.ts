import { useModal } from '@/Composables/useModal';
import { Ref, ref } from 'vue';

export function useConfirmationDialog() {
	const confirmationDialogShowing = ref<boolean>(false);
	useModal(confirmationDialogShowing);

	const dialogContent: Ref<string> = ref('');

	let successCallback: (() => void) | null = null;
	let cancelCallback: (() => void) | null = null;

	function showDialog(
		content: string,
		successCallbackAttr: (() => void) | null,
		cancelCallbackAttr: (() => void) | null = null,
	) {
		successCallback = successCallbackAttr;
		cancelCallback = cancelCallbackAttr;
		dialogContent.value = content;
		confirmationDialogShowing.value = true;
	}

	function confirmDialog() {
		if (successCallback) {
			successCallback();
		}
		confirmationDialogShowing.value = false;
	}

	function cancelDialog() {
		if (cancelCallback) {
			cancelCallback();
		}
		confirmationDialogShowing.value = false;
	}

	return {
		confirmationDialogShowing,
		dialogContent,
		showDialog,
		confirmDialog,
		cancelDialog,
	};
}
