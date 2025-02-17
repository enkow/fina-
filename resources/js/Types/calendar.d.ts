import { ComputedRef } from 'vue';

export interface Package {
	readonly name: ComputedRef<string>;
	readonly description: ComputedRef<string>;
	readonly discount: ComputedRef<string>;
	readonly image: string;
}

export interface Game {
	readonly name: ComputedRef<string>;
	readonly description: ComputedRef<string>;
	readonly image: string;
}
