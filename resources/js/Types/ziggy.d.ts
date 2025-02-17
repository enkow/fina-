declare global {
	function route(name?: string, params?: any, absolute?: boolean, config?: any): any;
}
export {};

declare module 'ziggy' {
	export const Ziggy: any;
}
