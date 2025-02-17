import { Ref, ref } from 'vue';

export function useString() {
	const pad = (str: string, len: number): string => {
		str = String(str);
		while (str.length < len) str = '0' + str;
		return str;
	};

	const randomString = (length: number): Ref<string> => {
		const result: Ref<string> = ref('');
		const characters: string = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
		const charactersLength: number = characters.length;
		for (let i = 0; i < length; i++) {
			result.value += characters.charAt(Math.floor(Math.random() * charactersLength));
		}
		return result;
	};

	const capitalize = (string: string): string => {
		string = String(string);
		return string.charAt(0).toUpperCase() + string.slice(1);
	};

	const initials = (string: string): string => {
		let names: Array<string> = string ? string.split(' ') : ['Not', 'Logged'];
		let initials: string = names[0].substring(0, 1).toUpperCase();
		if (names.length > 1) {
			initials += names[names.length - 1].substring(0, 1).toUpperCase();
		}
		return initials;
	};
	const snakeToCamel = (str: string) =>
		str
			.toLowerCase()
			.replace(/([-_][a-z])/g, (group) => group.toUpperCase().replace('-', '').replace('_', ''));

	return { pad, randomString, capitalize, initials, snakeToCamel };
}
