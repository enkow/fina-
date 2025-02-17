export function useQueryString() {
	const queryArray = (url: string): Record<string, string> => {
		if (url.indexOf('http') !== -1) {
			let el: HTMLAnchorElement = document.createElement('a');
			el.href = url;
			url = el.search;
		}
		let urlSearchParams: URLSearchParams = new URLSearchParams(url);
		return Object.fromEntries(urlSearchParams.entries());
	};

	const queryValue = (url: string, key: string): string => {
		let array: Object = queryArray(url);
		// @ts-ignore
		return array[key];
	};

	const queryUrl = (params: Record<string, string>): string => {
		return window.location.pathname + '?' + new URLSearchParams(params).toString();
	};

	return { queryUrl, queryArray, queryValue };
}
