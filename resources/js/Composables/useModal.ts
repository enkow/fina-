import { Ref, ref, watch } from 'vue';

export function useModal(status: Ref<boolean> | null = null) {
	if (status) {
		watch(
			() => status.value,
			() => {
				if (status.value) {
					let scrollTop: number = document.documentElement.scrollTop;
					document.body.classList.add('modal-open');
					document.body.style.top = -scrollTop + 'px';
				} else {
					document.body.classList.remove('modal-open');
					document.documentElement.scrollTop = parseInt(
						document.body.style.top.replace('-', '').replace('px', ''),
					);
				}
			},
		);
	}

	function saveScrollTop() {
		let scrollTop: number = document.documentElement.scrollTop;
		document.body.classList.add('modal-open');
		document.body.style.top = -scrollTop + 'px';
	}

	function passScrollTop() {
		document.body.classList.remove('modal-open');
		document.documentElement.scrollTop = parseInt(document.body.style.top.replace('-', '').replace('px', ''));
	}

	return {
		saveScrollTop,
		passScrollTop,
	};
}
