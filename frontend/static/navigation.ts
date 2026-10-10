/** Native navigation: Apache/LiteSpeed serves index.html for each clean URL. */
export class PageNotFoundError extends Error {}

export function notFound(): never {
  throw new PageNotFoundError("Không tìm thấy trang");
}

export function useRouter() {
  return {
    push: (href: string) => { window.location.assign(href); },
    replace: (href: string) => { window.location.replace(href); },
    back: () => { window.history.back(); },
    refresh: () => { window.location.reload(); },
  };
}

export function useSearchParams() {
  return new URLSearchParams(window.location.search);
}
