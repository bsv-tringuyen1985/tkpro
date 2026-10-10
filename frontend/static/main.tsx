import { Component, useEffect, useState, type ReactNode } from "react";
import { createRoot } from "react-dom/client";
import type { Metadata } from "next";
import "@/app/globals.css";
import Header from "@/components/Header";
import HeaderClient from "@/components/HeaderClient";
import Footer from "@/components/Footer";
import Loading from "@/app/loading";
import NotFound from "@/app/not-found";
import ErrorPage from "@/app/error";
import { ToastProvider } from "@/components/Toast";
import { DEFAULT_SETTINGS, getSettings } from "@/services/settingService";
import { PageNotFoundError } from "./navigation";
import { applyMetadata } from "./metadata";
import { matchRoute, readSearchParams, type RouteName } from "./routes";

type PageProps = {
  params: Promise<{ category: string; slug: string }>;
  searchParams: Promise<Record<string, string | string[]>>;
};
type PageModule = {
  default: (props: PageProps) => ReactNode | Promise<ReactNode>;
  metadata?: Metadata;
  generateMetadata?: (props: PageProps) => Promise<Metadata>;
};

// Reuse the existing views and API services. Only Next's runtime primitives are
// replaced for this build; async page functions are awaited before React renders.
const pages: Record<Exclude<RouteName, "missing">, () => Promise<PageModule>> = {
  home: () => import("@/app/page"),
  products: () => import("@/app/san-pham/page"),
  category: () => import("@/app/san-pham/[category]/page"),
  product: () => import("@/app/san-pham/[category]/[slug]/page"),
  posts: () => import("@/app/tin-tuc/page"),
  post: () => import("@/app/tin-tuc/[slug]/page"),
  about: () => import("@/app/gioi-thieu/page"),
  services: () => import("@/app/dich-vu/page"),
  contact: () => import("@/app/lien-he/page"),
  search: () => import("@/app/tim-kiem/page"),
};

async function loadPage() {
  const route = matchRoute(window.location.pathname);
  if (route.name === "missing") throw new PageNotFoundError();
  const page = await pages[route.name]();
  const props: PageProps = {
    params: Promise.resolve(route.params),
    searchParams: Promise.resolve(readSearchParams(window.location.search)),
  };
  const [content, metadata, settings] = await Promise.all([
    page.default(props),
    page.generateMetadata?.(props) ?? Promise.resolve(page.metadata || {}),
    getSettings(),
  ]);
  if (route.name === "search") metadata.robots = "noindex,follow";
  applyMetadata(metadata, settings.site_name);
  return content;
}

function AsyncContent({ load, fallback = null }: { load: () => Promise<ReactNode>; fallback?: ReactNode }) {
  const [content, setContent] = useState<ReactNode>(fallback);
  useEffect(() => {
    let active = true;
    load().then(value => { if (active) setContent(value); }).catch(error => {
      if (!active) return;
      if (error instanceof PageNotFoundError) {
        applyMetadata({ title: "Không tìm thấy trang", robots: "noindex,follow" });
        setContent(<NotFound />);
      } else {
        applyMetadata({ title: "Không thể tải trang", robots: "noindex,follow" });
        setContent(<ErrorPage error={error instanceof Error ? error : new Error(String(error))} reset={() => window.location.reload()} />);
      }
    });
    return () => { active = false; };
  }, [load]);
  return content;
}

class RenderBoundary extends Component<{ children: ReactNode }, { error: Error | null }> {
  state: { error: Error | null } = { error: null };
  static getDerivedStateFromError(error: Error) { return { error }; }
  render() {
    return this.state.error
      ? <ErrorPage error={this.state.error} reset={() => window.location.reload()} />
      : this.props.children;
  }
}

function ApiNotice() {
  const [message, setMessage] = useState("");
  useEffect(() => {
    const show = (event: Event) => setMessage((event as CustomEvent<string>).detail);
    window.addEventListener("tkpro:api-error", show);
    return () => window.removeEventListener("tkpro:api-error", show);
  }, []);
  if (!message) return null;
  return <div role="alert" className="border-b border-red-200 bg-red-50 px-4 py-3 text-center text-sm text-red-800">
    {message} <button type="button" className="ml-2 font-bold underline" onClick={() => window.location.reload()}>Thử lại</button>
  </div>;
}

const initialHeader = <HeaderClient siteName={DEFAULT_SETTINGS.site_name} hotline={DEFAULT_SETTINGS.hotline}
  announcement={DEFAULT_SETTINGS.announcement} items={[]} />;

createRoot(document.getElementById("root")!).render(
  <RenderBoundary>
    <ToastProvider>
      <ApiNotice />
      <AsyncContent load={Header} fallback={initialHeader} />
      <main><AsyncContent load={loadPage} fallback={<Loading />} /></main>
      <AsyncContent load={Footer} />
    </ToastProvider>
  </RenderBoundary>,
);
