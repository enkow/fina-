export interface Row {
	readonly label: string;
	readonly items: Item[];
}

export interface Item {
	readonly label: string;
	readonly value: string;
}
